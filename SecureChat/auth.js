// Import bcryptjs for password hashing and jsonwebtoken for generating JWTs
const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");

// In-memory object to store users (for this example; in real-world apps, use a database)
const users = {};

// Function to register a new user
const register = (username, password) => {
    // Check if the user already exists
    if (users[username]) return "User already exists";

    // Hash the password using bcrypt (with a salt factor of 10 for security)
    users[username] = bcrypt.hashSync(password, 10);

    // Notify the user that registration was successful
    return "User registered";
};

// Function to handle user login
const login = (username, password) => {
    // Check if the user exists
    if (!users[username]) return "User not found";

    // Verify the password by comparing the input with the stored hashed password
    if (!bcrypt.compareSync(password, users[username])) return "Wrong password";

    // Generate a JSON Web Token for the user, signed with a secret key
    // The token includes the username and expires in 1 hour
    return jwt.sign({ username }, "secretkey", { expiresIn: "1h" });
};

// Export the register and login functions so they can be used in other files
module.exports = { register, login };
