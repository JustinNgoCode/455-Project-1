## SecureChat

SecureChat is a lightweight real-time chat application that utilizes WebSocket for instant communication and Express for serving static files. It includes authentication, rate limiting, and a simple front-end for chatting.

### Features:

- **User Authentication** – Predefined usernames and passwords.
- **Rate Limiting** – Users can send up to 5 messages per minute.
- **Live Chat** – Messages are updated in real time.
- **Minimalist UI** – A simple HTML and JavaScript front-end.


### Project Structure:

#### Server (server.js):

Built with **Node.js**, **Express**, and the ws WebSocket library. It handles authentication, broadcasting messages, and rate limiting.

**Main Features:**
- Predefined users (user1, user2).
- WebSocket-based messaging.
- Static file serving from the public directory.
- Limits messages to 5 per minute per user.

#### Client (index.html):

A basic HTML/JavaScript interface connecting to the WebSocket server.

**Main Features:**
- Simple login form.
- Real-time chat.
- Displays messages from all users.

## How to Set Up:

#### Requirements:
1. Install Node.js
2. Install express ws

### Running the Server:
1. Save the server code as server.js.
2. Start the server: node server.js

#### Running the Client:
1. Save the client code as index.html inside a public folder.
2. Open index.html in a browser.

---

## User Guide:

#### 1. Start the Server:
- Open a terminal
- Got to project directory
- Run node server.js.
- Open index.html in a browser

#### 2. Log into the Chat App:
- Use one of these credentials:
  - Username: user1, password: pass1
  - Username: user2, password: pass2

#### 3. Start Chatting:
- Type a message in the input field and press [Send].
- Messages update in real time for all users.

#### 4. Rate Limiting:
- Users can send 5 messages per minute.
- Exceeding this limit triggers a warning.

## Code Breakdown:

#### server.js (backend)
- Handles WebSocket connections
- Authenticates users
- Implements rate limiting*

#### index.html (front)
- Login form for authentication
- Basic UI to send and display messages

## Testing:

#### Steps:
1. Open two different browser windows.
2. Log in as user1 in one and user2 in the other.
3. Send messages and confirm they appear in both windows.
4. Test the rate limit by sending more than 5 messages in a minute.

---

### Possible Future Enhancements:

- **Database Integration** – Replace hardcoded users with a database.
- **Improved UI** – Add styling, timestamps, and user avatars.
- **End-to-End Encryption** – Secure chat messages.
- **User Registration** – Allow users to sign up instead of using predefined logins.




