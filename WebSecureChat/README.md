# SecureChat Phase 3 Setup Instructions

This guide explains how to deploy SecureChat, a cloud-hosted chat app with real-time messaging, end-to-end encryption, and secure file sharing, using InfinityFree, Glitch, Firebase Storage, Cloudflare, and UptimeRobot.

## Prerequisites
- A free InfinityFree account (https://www.infinityfree.com).
- A free Glitch account (https://glitch.com).
- A free Firebase account (https://firebase.google.com).
- A free Cloudflare account (https://www.cloudflare.com).
- A free UptimeRobot account (https://uptimerobot.com).
- Basic understanding of uploading files and copying code.

## Step 1: Set Up Firebase Storage
1. Go to Firebase Console, create a new project (e.g., "SecureChat").
2. Enable Storage: Navigate to "Storage" > "Get Started".
3. Copy the Firebase config (apiKey, projectId, storageBucket, appId) from "Project Settings" > "Your apps" > "SDK setup".
4. Update `app.js` with your Firebase config:
   ```javascript
   const firebaseConfig = {
       apiKey: "YOUR_API_KEY",
       projectId: "YOUR_PROJECT_ID",
       storageBucket: "YOUR_STORAGE_BUCKET",
       appId: "YOUR_APP_ID"
   };
   ```
5. Set Storage rules in Firebase Console (under "Storage" > "Rules"):
   ```plaintext
   rules_version = '2';
   service firebase.storage {
       match /b/{bucket}/o {
           match /files/{allPaths=**} {
               allow read, write: if request.auth != null;
           }
       }
   }
   ```

## Step 2: Deploy WebSocket Server on Glitch
1. Go to Glitch, create a new project, and select "Node.js".
2. Copy `server.js` and `package.json` into the project (replace existing files).
3. Click "Show" to get the project URL (e.g., `https://your-glitch-project.glitch.me`).
4. Update `app.js` with the Glitch URL:
   ```javascript
   const socket = io("https://your-glitch-project.glitch.me", { autoConnect: false });
   ```
5. Glitch auto-deploys; ensure the project stays active (free tier sleeps after inactivity).

## Step 3: Set Up InfinityFree
1. Sign up at InfinityFree and create a free hosting account.
2. In cPanel, create a MySQL database:
   - Note the database name, username, password, and host (usually `localhost`).
   - Update `api.php` with these credentials:
     ```php
     $host = "localhost";
     $dbname = "your_db_name";
     $user = "your_db_user";
     $pass = "your_db_password";
     ```
3. In cPanel > "MySQL Databases", import `database.sql` to set up tables.
4. Upload `index.html`, `styles.css`, `app.js`, and `api.php` to the `htdocs` folder via cPanel > "File Manager".
5. Enable SSL in cPanel > "Security" > "SSL/TLS" to use HTTPS.

## Step 4: Configure Cloudflare
1. Sign up at Cloudflare, add your InfinityFree domain (e.g., `yourdomain.infinityfreeapp.com`).
2. Update nameservers in InfinityFree to Cloudflare’s nameservers (in cPanel > "Domains").
3. Enable HTTPS proxying in Cloudflare > "SSL/TLS" > Set to "Flexible" (or "Full" if InfinityFree supports).
4. Ensure the site loads at `https://yourdomain.infinityfreeapp.com`.

## Step 5: Set Up UptimeRobot
1. Sign up at UptimeRobot.
2. Create two monitors (HTTP type, 5-minute interval):
   - InfinityFree: `https://yourdomain.infinityfreeapp.com`
   - Glitch: `https://your-glitch-project.glitch.me`
3. Add your email for downtime alerts.

## Step 6: Test SecureChat
1. Open `https://yourdomain.infinityfreeapp.com` in a browser.
2. Register a user (username, password).
3. Log in, send messages, upload files (PNG, JPEG, PDF, max 5MB), and use emojis.
4. Open another browser/tab, log in as a different user, and test real-time chat and presence (online/typing indicators).
5. Check Firebase Storage for uploaded files (encrypted URLs sent via chat).
6. If the site or chat stops, check UptimeRobot alerts and ensure Glitch is awake.

## Notes
- **Encryption**: Messages and file URLs are encrypted client-side using Web Crypto API. The server only stores encrypted data.
- **Rate Limiting**: Login/register attempts are limited to 3 per minute (client-side in `app.js`, server-side in `api.php`).
- **File Validation**: Files are checked for type (PNG, JPEG, PDF) and size (5MB max) before upload.
- **Presence**: Online/offline/typing status is shown via WebSocket events.
- **Reconnect**: The client auto-reconnects if the WebSocket drops.
- **Glitch Sleep**: Free Glitch projects sleep after 5 minutes of inactivity; ping the Glitch URL periodically or upgrade for persistence.
- **Firebase Limits**: Free tier allows 1GB storage, 10GB/month transfer; monitor usage in Firebase Console.

## Troubleshooting
- **Site not loading**: Check InfinityFree cPanel (files in `htdocs`), Cloudflare DNS, and SSL settings.
- **Chat not working**: Ensure Glitch project is running and the WebSocket URL in `app.js` is correct.
- **Database errors**: Verify MySQL credentials in `api.php` and that `database.sql` was imported.
- **File upload fails**: Confirm Firebase config in `app.js` and Storage rules in Firebase Console.
- **Rate limit errors**: Wait 1 minute or clear browser storage (`localStorage.clear()` in console).

For help, check InfinityFree/Glitch/Firebase documentation or ask your instructor.