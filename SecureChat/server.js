const WebSocket = require('ws');
const http = require('http');
const express = require('express');
const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

const users = {
    // Hardcoded users 
    'user1': { password: 'pass1', lastMessageTime: Date.now(), messageCount: 0 },
    'user2': { password: 'pass2', lastMessageTime: Date.now(), messageCount: 0 }
};

const MAX_MESSAGES_PER_MINUTE = 5;  // Max messages per minute per user
const RATE_LIMIT_TIME = 60000;  // 1 minute in milliseconds

app.use(express.static('public'));  // Serve static files from the 'public' directory

wss.on('connection', (ws, req) => {
    // Extract username and password from the WebSocket URL
    const url = new URL(req.url, `ws://${req.headers.host}`);
    const username = url.searchParams.get('username');
    const password = url.searchParams.get('password');

    // Check if the user exists and the password matches
    if (!username || !users[username] || users[username].password !== password) {
        ws.close(4001, 'Authentication failed');
        return;
    }

    console.log(`${username} connected`);

    // Send a welcome message
    ws.send(JSON.stringify({ message: 'Welcome to SecureChat!' }));

    ws.on('message', (message) => {
        const currentTime = Date.now();
        const user = users[username];

        // Parse the incoming message (it's a JSON string)
        const data = JSON.parse(message);

        // Rate limiting logic
        if (currentTime - user.lastMessageTime > RATE_LIMIT_TIME) {
            user.messageCount = 0;  // Reset message count every minute
        }

        if (user.messageCount >= MAX_MESSAGES_PER_MINUTE) {
            ws.send(JSON.stringify({ message: 'Rate limit exceeded. Please try again later.' }));
        } else {
            user.messageCount++;
            user.lastMessageTime = currentTime;

            console.log(`${username} sent a message: ${data.message}`);

            // Broadcast message to all other clients
            wss.clients.forEach((client) => {
                if (client !== ws && client.readyState === WebSocket.OPEN) {
                    client.send(JSON.stringify({ user: username, message: data.message }));
                }
            });
        }
    });

    ws.on('close', () => {
        console.log(`${username} disconnected`);
    });
});

server.listen(3000, () => {
    console.log('Server running on http://localhost:3000');
});
