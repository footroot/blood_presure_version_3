<?php
// Start a session
session_start();

// Redirect to the main application page if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
    } else {
        // Prepare a statement to find the user
        $stmt = $conn->prepare("SELECT id, password, is_verified FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password and check if the user is verified
            if (password_verify($password, $user['password'])) {
                if ($user['is_verified'] == 1) {
                    // Password is correct and user is verified, create a session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    
                    // Redirect to the main application page
                    header("Location: index.php");
                    exit();
                } else {
                    $message = "Your account has not been verified. Please check your email.";
                }
            } else {
                $message = "Incorrect username or password.";
            }
        } else {
            $message = "Incorrect username or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if ($message): ?>
        <div class="message error">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword('password')">Show</span>
            </div>
        </div>
        <div class="form-group">
            <button type="submit">Login</button>
        </div>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<script src="js/script.js"></script>

</body>
</html>