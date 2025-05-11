const express = require("express");
const app = express();
const server = require("http").createServer(app);
const io = require("socket.io")(server, {
    cors: { origin: "*" } // Allow all origins for simplicity
});

// Heartbeat to keep connections alive
io.on("connection", (socket) => {
    console.log("User connected:", socket.id);

    // Authenticate user
    const username = socket.auth?.username || "Anonymous";
    socket.join("chat");

    // Broadcast presence
    socket.on("presence", ({ status }) => {
        io.to("chat").emit("presence", { username, status });
    });

    // Handle messages
    socket.on("message", ({ content, isFile }) => {
        io.to("chat").emit("message", { sender: username, content, isFile });
    });

    // Handle disconnect
    socket.on("disconnect", () => {
        io.to("chat").emit("presence", { username, status: "offline" });
    });

    // Ping/pong heartbeat
    const pingInterval = setInterval(() => {
        socket.emit("ping");
    }, 30_000);
    socket.on("pong", () => {}); // Client responds to keep alive
    socket.on("disconnect", () => clearInterval(pingInterval));
});

// Serve a basic endpoint for UptimeRobot
app.get("/", (req, res) => res.send("SecureChat WebSocket Server"));

// Start server
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => console.log(`Server running on port ${PORT}`));