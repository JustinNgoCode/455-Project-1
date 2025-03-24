## SecureChat

SecureChat is a real-time chat application built with WebSocket for instant communication, Express for server-side logic, and Electron for a desktop client. It features end-to-end encryption, user registration, private chats, file sharing, and a user-friendly interface. The application now supports communication over a local network (LAN) and is prepared for future Internet deployment with secure protocols (`wss://` and `https://`).

### Features:

- **User Authentication** - Register and log in with a username and password, secured with bcrypt and JWT.
- **Rate Limiting** - Users can send up to 10 messages per minute to prevent spam.
- **Live Private Chat** - Messages are sent in real time to specific users.
- **End-to-End Encryption** - Messages are encrypted using AES-GCM (256-bit) and RSA-OAEP (4096-bit); files are encrypted with AES-CBC.
- **File Sharing** - Securely send files to other users with encryption.
- **File Download Support** - Download decrypted files received from other users.
- **User-Friendly UI** - Styled interface with emoji support, text formatting, and a sidebar for user selection.
- **Local Echo of Messages** - Sender’s messages are shown locally in the chat window.
- **Reconnect Logic** - Automatically attempts to reconnect on connection loss (up to 5 attempts with a 3-second interval).
- **Text Formatting Support** - Supports bold (`**text**`), italic (`*text*`), and links (`[text](url)`).
- **Brute Force Protection for Login** - Limits failed login attempts to 5, with a 15-minute lockout period.
- **Cross-Platform Electron App** - Works on Windows, macOS, and Linux (tested on Windows).
- **Encrypted Chat Logging System** - Logs encrypted chat sessions to the `logs/` directory for debugging.
- **Network Support** - Now supports LAN communication, allowing users on different devices in the same network to chat; prepared for future Internet communication.

### Project Structure:

#### Server (server.js):

Built with Node.js, Express, and the `ws` WebSocket library. It handles user authentication, message relaying, file transfers, and rate limiting.

##### Main Features:
- Dynamic user registration and login with hashed passwords (using bcrypt).
- WebSocket-based private messaging and file sharing.
- Relays encrypted messages without decryption for true end-to-end security.
- Limits messages to 10 per minute per user.
- Logs chat sessions to files in the `logs/` directory for debugging.
- Configurable host and port via environment variables (`HOST` and `PORT`), defaulting to `0.0.0.0:3000` for LAN/Internet accessibility.

#### Client (index.html):

An HTML/JavaScript interface running in an Electron app, connecting to the WebSocket server.

##### Main Features:
- Registration and login forms with a configurable server URL input field.
- Sidebar for selecting users to chat with.
- Real-time private chat with chat history.
- Emoji picker and Markdown-like text formatting (bold, italic, links).
- Secure file sharing with encryption.
- Logout functionality.
- "Test Connection" button to verify the server URL.
- Help text to guide users on setting up LAN communication.

#### Electron App (main.js):

A simple Electron setup to run the client as a desktop application.

##### Main Features:
- Launches multiple windows for testing multiple users.
- Loads the `index.html` client interface with web security disabled for local development.

## How to Set Up:

#### Requirements:
1. Install Node.js (version 14.x or higher).
2. Install the following dependencies:
   - `express`
   - `ws`
   - `bcryptjs`
   - `jsonwebtoken`
   - `helmet`
   - `dotenv`
   - `electron`

### Running the Server:
1. Save the server code as `server.js`.
2. Install dependencies:
   ```bash
   npm install express ws bcryptjs jsonwebtoken helmet dotenv
