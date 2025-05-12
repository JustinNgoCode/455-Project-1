## FriendChat

FriendChat is a secure chat application built with PHP and MySQL for the backend, and a single-page HTML/JavaScript frontend. It features user authentication, real-time messaging, file sharing, end-to-end encryption, rate limiting, and reCAPTCHA for spam protection. The application is designed for secure communication over the internet using HTTPS.

### Features:

- **User Authentication** - Register and log in with a username and password, stored securely with password hashing.
- **Rate Limiting** - Limits users to 10 requests per minute to prevent abuse.
- **Text Messaging** - Send and receive text messages in real-time, encrypted with AES-256-CBC.
- **End-to-End Encryption** - Messages are encrypted on the client side before being sent and decrypted only by the recipient.
- **File Sharing** - Upload and share files (JPEG, PNG, GIF, PDF) up to 10MB, stored securely on the server.
- **File Download Support** - Download files shared by other users.
- **User-Friendly UI** - Clean interface with emoji support and a scrollable chat window.
- **reCAPTCHA Protection** - Prevents automated spam during registration and login.
- **Chat Logging** - Logs events (e.g., messages sent, files uploaded) in the database for debugging.
- **Cross-Origin Support** - Supports CORS for flexible frontend hosting.

### Project Structure:

#### Server (api.php):

Built with PHP and MySQL, the backend handles user authentication, message storage, file uploads, and rate limiting.

##### Main Features:
- User registration and login with hashed passwords (using PHP's `password_hash`).
- AES-256-CBC encryption for message content.
- File upload handling with validation (size, type, and directory creation).
- Rate limiting (10 requests per minute per IP).
- reCAPTCHA verification for registration and login.
- Logs events to a database table for debugging.
- Configurable database connection via InfinityFree hosting.

#### Client (index.html):

A single-page HTML/JavaScript interface that communicates with the backend via HTTP requests.

##### Main Features:
- Registration and login forms with reCAPTCHA integration.
- Real-time chat interface with message history.
- Emoji picker for adding emojis to messages.
- File upload and download support.
- Client-side rate limiting (10 requests per minute).
- Logout functionality.
- Responsive design with CSS styling.

## How to Set Up:

#### Requirements:
1. A web server with PHP (version 7.4 or higher) and MySQL support (e.g., InfinityFree hosting).
2. A reCAPTCHA v2 key pair from [Google reCAPTCHA](https://www.google.com/recaptcha).
3. A MySQL database with the following tables:
   - `users` (for storing user credentials)
   - `messages` (for storing chat messages and files)
   - `requests` (for rate limiting)
   - `logs` (for event logging)

#### Database Setup:
1. Create a MySQL database (e.g., `friendchat`).
2. Create the required tables using the following SQL schema:
   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL
   );

   CREATE TABLE messages (
       id INT AUTO_INCREMENT PRIMARY KEY,
       sender VARCHAR(255) NOT NULL,
       content TEXT NOT NULL,
       type ENUM('text', 'file') NOT NULL,
       file_path VARCHAR(255) DEFAULT NULL,
       timestamp DATETIME NOT NULL
   );

   CREATE TABLE requests (
       id INT AUTO_INCREMENT PRIMARY KEY,
       ip_address VARCHAR(45) NOT NULL,
       action VARCHAR(50) NOT NULL,
       request_count INT NOT NULL,
       last_request DATETIME NOT NULL,
       window_end DATETIME NOT NULL
   );

   CREATE TABLE logs (
       id INT AUTO_INCREMENT PRIMARY KEY,
       event TEXT NOT NULL,
       timestamp DATETIME NOT NULL
   );
