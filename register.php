<?php
// Start a session to use session variables later
session_start();

// Include the database connection file
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim user inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $password_verify = $_POST['password_verify'];

    // Basic form validation
    if (empty($username) || empty($email) || empty($password) || empty($password_verify)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $password_verify) {
        $message = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username or email already exists. Please choose a different one.";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Generate a unique verification token
            $verification_token = bin2hex(random_bytes(32));

            // Prepare and execute the insert statement
            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $verification_token);

            if ($stmt_insert->execute()) {
                // For a live server, you would send an email here.
                // For now, we will display the verification link for testing purposes.
                $verification_link = "http://mytaller.info/verify.php?token=" . urlencode($verification_token);
                $message = "Registration successful! Please check your email for the verification link. For testing, the link is: <a href='$verification_link'>$verification_link</a>";

            } else {
                $message = "Error: " . $stmt_insert->error;
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h2>User Registration</h2>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword('password')">Show</span>
            </div>
        </div>
        <div class="form-group">
            <label for="password_verify">Verify Password:</label>
            <div class="password-container">
                <input type="password" id="password_verify" name="password_verify" required>
                <span class="toggle-password" onclick="togglePassword('password_verify')">Show</span>
            </div>
        </div>
        <div class="form-group">
            <button type="submit">Register</button>
        </div>
    </form>
</div>

<script src="js/script.js"></script>

</body>
</html>