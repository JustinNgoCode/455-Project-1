// Initialize Firebase
const firebaseConfig = {
    // Replace with your Firebase project config
    apiKey: "YOUR_API_KEY",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_STORAGE_BUCKET",
    appId: "YOUR_APP_ID"
};
firebase.initializeApp(firebaseConfig);
const storage = firebase.storage();

// WebSocket setup
const socket = io("https://your-glitch-project.glitch.me", { autoConnect: false });

// DOM elements
const authSection = document.getElementById("auth");
const chatSection = document.getElementById("chat");
const authTitle = document.getElementById("auth-title");
const authBtn = document.getElementById("auth-btn");
const authSwitch = document.getElementById("auth-switch");
const authError = document.getElementById("auth-error");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const currentUser = document.getElementById("current-user");
const status = document.getElementById("status");
const messages = document.getElementById("messages");
const messageInput = document.getElementById("message");
const sendBtn = document.getElementById("send-btn");
const fileInput = document.getElementById("file-input");
const emojiBtn = document.getElementById("emoji-btn");
const emojiPicker = document.getElementById("emoji-picker");

// State
let isLogin = true;
let currentUsername = "";
const emojis = ["😊", "😂", "👍", "❤️", "🚀"];

// Encryption key (simplified, derive from password in production)
const encoder = new TextEncoder();
const decoder = new TextDecoder();

// Toggle login/register
authSwitch.addEventListener("click", () => {
    isLogin = !isLogin;
    authTitle.textContent = isLogin ? "Login" : "Register";
    authBtn.textContent = isLogin ? "Login" : "Register";
    authSwitch.textContent = isLogin ? "Register instead" : "Login instead";
    authError.classList.add("hidden");
});

// Auth handler
authBtn.addEventListener("click", async () => {
    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();
    if (!username || !password) {
        authError.textContent = "Please enter username and password";
        authError.classList.remove("hidden");
        return;
    }

    // Basic rate limiting (client-side, 3 attempts per minute)
    const now = Date.now();
    const attempts = JSON.parse(localStorage.getItem("authAttempts") || "[]");
    const recent = attempts.filter(t => now - t < 60_000);
    if (recent.length >= 3) {
        authError.textContent = "Too many attempts, try again later";
        authError.classList.remove("hidden");
        return;
    }
    recent.push(now);
    localStorage.setItem("authAttempts", JSON.stringify(recent.slice(-3)));

    try {
        const response = await fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: isLogin ? "login" : "register",
                username,
                password
            })
        });
        const result = await response.json();
        if (result.success) {
            currentUsername = username;
            currentUser.textContent = username;
            authSection.classList.add("hidden");
            chatSection.classList.remove("hidden");
            socket.auth = { username };
            socket.connect();
        } else {
            authError.textContent = result.message;
            authError.classList.remove("hidden");
        }
    } catch (e) {
        authError.textContent = "Error connecting to server";
        authError.classList.remove("hidden");
    }
});

// WebSocket events
socket.on("connect", () => {
    status.textContent = "Online";
    socket.emit("presence", { status: "online" });
});

socket.on("disconnect", () => {
    status.textContent = "Offline";
    setTimeout(() => socket.connect(), 5000); // Auto-reconnect
});

socket.on("message", async ({ sender, content, isFile }) => {
    const div = document.createElement("div");
    div.className = sender === currentUsername ? "sent" : "received";
    if (isFile) {
        const decrypted = await decrypt(content);
        div.innerHTML = `<a href="${decrypted}" target="_blank">File from ${sender}</a>`;
    } else {
        const decrypted = await decrypt(content);
        div.textContent = `${sender}: ${decrypted}`;
    }
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
});

socket.on("presence", ({ username, status: userStatus }) => {
    if (username !== currentUsername) {
        const div = document.createElement("div");
        div.className = "text-gray-500 text-sm";
        div.textContent = `${username} is ${userStatus}`;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }
});

// Send message
sendBtn.addEventListener("click", async () => {
    const content = messageInput.value.trim();
    if (content) {
        const encrypted = await encrypt(content);
        socket.emit("message", { content, isFile: false });
        messageInput.value = "";
    }
});

// File upload
fileInput.addEventListener("change", async () => {
    const file = fileInput.files[0];
    if (!file) return;

    // Basic client-side validation
    if (file.size > 5 * 1024 * 1024) { // 5MB limit
        alert("File too large (max 5MB)");
        return;
    }
    if (!["image/png", "image/jpeg", "application/pdf"].includes(file.type)) {
        alert("Only PNG, JPEG, or PDF allowed");
        return;
    }

    try {
        const ref = storage.ref(`files/${Date.now()}_${file.name}`);
        await ref.put(file);
        const url = await ref.getDownloadURL();
        const encrypted = await encrypt(url);
        socket.emit("message", { content: encrypted, isFile: true });
        fileInput.value = "";
    } catch (e) {
        alert("Error uploading file");
    }
});

// Emoji picker
emojiBtn.addEventListener("click", () => {
    emojiPicker.classList.toggle("hidden");
    if (!emojiPicker.innerHTML) {
        emojis.forEach(emoji => {
            const span = document.createElement("span");
            span.className = "emoji";
            span.textContent = emoji;
            span.addEventListener("click", () => {
                messageInput.value += emoji;
                emojiPicker.classList.add("hidden");
            });
            emojiPicker.appendChild(span);
        });
    }
});

// Typing indicator
messageInput.addEventListener("input", () => {
    socket.emit("presence", { status: "typing..." });
});

// Encryption (simplified, use PBKDF2-derived key in production)
async function encrypt(text) {
    const key = await crypto.subtle.importKey(
        "raw",
        encoder.encode("static-key-123"), // Replace with user-derived key
        { name: "AES-GCM" },
        false,
        ["encrypt"]
    );
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const encrypted = await crypto.subtle.encrypt(
        { name: "AES-GCM", iv },
        key,
        encoder.encode(text)
    );
    return btoa(String.fromCharCode(...iv, ...new Uint8Array(encrypted)));
}

async function decrypt(data) {
    const bytes = Uint8Array.from(atob(data), c => c.charCodeAt(0));
    const iv = bytes.slice(0, 12);
    const ciphertext = bytes.slice(12);
    const key = await crypto.subtle.importKey(
        "raw",
        encoder.encode("static-key-123"),
        { name: "AES-GCM" },
        false,
        ["decrypt"]
    );
    const decrypted = await crypto.subtle.decrypt(
        { name: "AES-GCM", iv },
        key,
        ciphertext
    );
    return decoder.decode(decrypted);
}