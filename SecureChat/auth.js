const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");

const users = {}; // Store users

const register = (username, password) => {
    if (users[username]) return "User already exists";
    users[username] = bcrypt.hashSync(password, 10);
    return "User registered";
};

const login = (username, password) => {
    if (!users[username]) return "User not found";
    if (!bcrypt.compareSync(password, users[username])) return "Wrong password";
    return jwt.sign({ username }, "secretkey", { expiresIn: "1h" });
};

module.exports = { register, login };
