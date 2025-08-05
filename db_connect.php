<?php
// Database credentials - REPLACE THESE WITH YOUR ACTUAL DETAILS
define('DB_SERVER', 'localhost'); // e.g., 'localhost' or your database server IP
define('DB_USERNAME', 'dani'); // e.g., 'root' or 'your_app_user'
define('DB_PASSWORD', '@Dani13'); // e.g., '' for no password, or your password
define('DB_NAME', 'blood_pressure_3.0'); // The name of your database

/* Attempt to connect to MySQL database */
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log the error to a server error log file (recommended for production)
    error_log("Failed to connect to MySQL: " . $conn->connect_error);

    // For development, you might show a user-friendly message
    // In production, avoid showing raw error messages to users for security reasons.
    die("ERROR: Could not connect to the database. Please try again later.");
}
