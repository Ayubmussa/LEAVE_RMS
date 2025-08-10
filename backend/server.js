const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const mysql = require('mysql2');

// Initialize express app
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Session configuration
app.use(session({
  secret: 'leave-and-rms-secret-key',
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false } // Set to true if using HTTPS
}));

// Serve static files from the public directory
app.use(express.static(path.join(__dirname, '../public')));

// Add MySQL connection
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'leave_rms_db'
});

// Login endpoint (uses database)
app.post('/api/login', (req, res) => {
  const { usernameOrEmail, password } = req.body;
  db.query('SELECT * FROM users WHERE username = ? OR email = ?', [usernameOrEmail, usernameOrEmail], (err, results) => {
    if (err) return res.status(500).json({ success: false, message: 'Database error' });
    if (results.length === 0) {
      return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
    const user = results[0];
    // In production, use password hashing/verification
    if (user.password === password) {
      req.session.user = { id: user.id, username: user.username };
      res.json({ success: true, message: 'Login successful', user: { id: user.id, username: user.username, email: user.email } });
    } else {
      res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
  });
});

// Check authentication status
app.get('/api/auth-status', (req, res) => {
  if (req.session.user) {
    res.json({ authenticated: true, user: req.session.user });
  } else {
    res.json({ authenticated: false });
  }
});

// Logout endpoint
app.post('/api/logout', (req, res) => {
  req.session.destroy();
  res.json({ success: true, message: 'Logout successful' });
});

// Default route to serve the main HTML file
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, '../public/index.html'));
});

// Start the server
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});