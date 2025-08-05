<?php
// Include the database connection file
include 'db_connect.php';

$message = '';

// Check if a verification token is present in the URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $verification_token = htmlspecialchars($_GET['token']);

    // Prepare a statement to find the user with the given token
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $verification_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User found, now update their status to verified
        $stmt_update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $stmt_update->bind_param("s", $verification_token);

        if ($stmt_update->execute()) {
            $message = "Your email has been successfully verified! You can now log in.";
        } else {
            $message = "Error: Could not update user status. " . $stmt_update->error;
        }

        $stmt_update->close();
    } else {
        $message = "Invalid or expired verification token.";
    }

    $stmt->close();
} else {
    $message = "No verification token provided.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h2>Email Verification</h2>
    <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
    <a href="login.php">Go to Login Page</a>
</div>

</body>
</html>