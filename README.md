## SecureChat

SecureChat is a real-time chat application built with WebSocket for instant communication, Express for server-side logic, and Electron for a desktop client. It features end-to-end encryption, user registration, private chats, file sharing, and a user-friendly interface.

### Features:

- User Authentication - Register and log in with a username and password.
- Rate Limiting - Users can send up to 10 messages per minute.
- Live Private Chat - Messages are sent in real time to specific users.
- End-to-End Encryption - Messages and files are encrypted using AES-256 and RSA-4096.
- File Sharing - Securely send files to other users.
- File Download Support (Download decrypted files)
- User-Friendly UI - Styled interface with emoji support and text formatting.
- Local Echo of Messages (Sender’s messages shown locally)
- Reconnect Logic - Automatically attempts to reconnect on connection loss (up to 5 attempts).
- Text Formatting Support (Bold, italic, links)
- Brute Force Protection for Login (Limits failed login attempts)
- Cross-Platform Electron App (Works on multiple OSes)
- Chat history and session login


### Project Structure:

#### Server (server.js):

Built with Node.js, Express, and the `ws` WebSocket library. It handles user authentication, message relaying, file transfers, and rate limiting.

##### Main Features:
- Dynamic user registration and login with hashed passwords.
- WebSocket-based private messaging and file sharing.
- Relays encrypted messages without decryption for true end-to-end security.
- Limits messages to 5 per minute per user.
- Logs chat sessions to files for debugging.

#### Client (index.html):

An HTML/JavaScript interface running in an Electron app, connecting to the WebSocket server.

##### Main Features:
- Registration and login forms.
- Sidebar for selecting users to chat with.
- Real-time private chat with chat history.
- Emoji picker and Markdown-like text formatting (bold, italic, links).
- Secure file sharing with encryption.
- Logout functionality.

#### Electron App (main.js):

A simple Electron setup to run the client as a desktop application.

##### Main Features:
- Launches multiple windows for testing multiple users.
- Loads the `index.html` client interface.

## How to Set Up:

#### Requirements:
1. Install Node.js
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
2. Install dependencies: `npm install express ws bcryptjs jsonwebtoken helmet dotenv`
3. Start the server: `node server.js`

#### Running the Client:
1. Save the client code as `index.html`.
2. Save the Electron app code as `main.js`.
3. Install Electron: `npm install electron`
4. Start the Electron app: `npm start`

---

## User Guide:

#### 1. Start the Server:
- Open a terminal
- Go to project directory
- Run `node server.js`.
- The server will run on `http://localhost:3000`

#### 2. Start the Electron App:
- In the project directory, run `npm start`.
- This will open three windows for testing multiple users.

#### 3. Register and Log into the Chat App:
- In one window, register a new user (e.g., username: `user1`, password: `pass1`).
- In another window, register a different user (e.g., username: `user2`, password: `pass2`).
- Log in with the registered credentials in each window.

#### 4. Start Chatting:
- Select a user from the sidebar to start a private chat.
- Type a message in the input field and press [Enter] or click [Send].
- Use `**text**` for bold, `*text*` for italic, or `[text](url)` for links.
- Click the emoji button to add emojis to your message.
- Messages are encrypted and sent securely to the selected user.

#### 5. Share Files:
- Click [Choose File] to select a file, then click [Send File].
- Files are encrypted and sent to the selected user.

#### 6. Rate Limiting:
- Users can send 5 messages per minute.
- Exceeding this limit triggers a warning.

#### 7. Logout:
- Click [Logout] to disconnect and return to the login screen.

## Code Breakdown:

#### server.js (backend)
- Handles WebSocket connections for private chats.
- Authenticates users with JWT tokens and hashed passwords.
- Implements rate limiting and brute-force protection.
- Relays encrypted messages and files.
- Logs chat sessions to the `logs` directory.

#### index.html (front)
- Registration and login forms for user authentication.
- Styled UI with a sidebar for user selection.
- Supports private chats, emoji picker, text formatting, and file sharing.
- Implements end-to-end encryption for messages and files.

#### main.js (Electron app)
- Sets up an Electron app to run the client.
- Opens multiple windows for testing.

## Testing:

#### Steps:
1. Start the server with `node server.js`.
2. Start the Electron app with `npm start` to open three windows.
3. Register and log in as `user1` in one window and `user2` in another.
4. Select `user2` in `user1`’s window and send a message.
5. Confirm the message appears in `user2`’s window, and vice versa.
6. Test file sharing by sending a file from `user1` to `user2`.
7. Test the rate limit by sending more than 5 messages in a minute.
8. Test the reconnect logic by stopping and restarting the server.

---

### Possible Future Enhancements:

- Database Integration - Replace `users.json` with a proper database (e.g., MongoDB).
- Improved UI - Add timestamps, user avatars, and message read receipts.
- File Download - Allow users to download received files.
- Group Chats - Enable chatting with multiple users at once.
- Notifications - Add desktop notifications for new messages.

---

