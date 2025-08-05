<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Handle form submission to add a new reading
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form data
    $systolic = isset($_POST['systolic']) ? (int) $_POST['systolic'] : 0;
    $diastolic = isset($_POST['diastolic']) ? (int) $_POST['diastolic'] : 0;
    $pulse = isset($_POST['pulse']) ? (int) $_POST['pulse'] : 0;
    $medication = isset($_POST['medication']) ? htmlspecialchars(trim($_POST['medication'])) : '';
    $notes = isset($_POST['notes']) ? htmlspecialchars(trim($_POST['notes'])) : '';
    $record_date = isset($_POST['record_date']) ? $_POST['record_date'] : date('Y-m-d');

    // Basic validation
    if ($systolic > 0 && $diastolic > 0 && $pulse > 0) {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO readings (user_id, systolic, diastolic, pulse, medication, notes, record_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisss", $user_id, $systolic, $diastolic, $pulse, $medication, $notes, $record_date);

        if ($stmt->execute()) {
            $message = "New record created successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Error: Please fill in all required fields (Systolic, Diastolic, and Pulse).";
    }
}

// Fetch user's readings to display
$readings = [];
$stmt = $conn->prepare("SELECT * FROM readings WHERE user_id = ? ORDER BY record_date DESC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $readings[] = $row;
}
$stmt->close();

// Close the database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Pressure Tracker</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<aside class="sidebar">
    <h3>MyTaller BP</h3>
    <ul>
        <li><a href="index.php">Add Reading</a></li>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="#" onclick="logout()">Logout</a></li>
    </ul>
    </aside>

<main class="main-content">
    <div class="container">
        <h2>Add New Blood Pressure Reading</h2>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <div class="form-group">
                <label for="record_date">Date of Record:</label>
                <input type="date" id="record_date" name="record_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="systolic">Systolic:</label>
                <input type="number" id="systolic" name="systolic" placeholder="e.g., 120" required>
            </div>
            <div class="form-group">
                <label for="diastolic">Diastolic:</label>
                <input type="number" id="diastolic" name="diastolic" placeholder="e.g., 80" required>
            </div>
            <div class="form-group">
                <label for="pulse">Pulse:</label>
                <input type="number" id="pulse" name="pulse" placeholder="e.g., 72" required>
            </div>
            <div class="form-group">
                <label for="medication">Medication:</label>
                <input type="text" id="medication" name="medication" placeholder="e.g., Lisinopril">
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Any additional notes..."></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Save Reading</button>
            </div>
        </form>

        <hr>

        <h2>Your Readings</h2>
        <?php if (count($readings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Systolic</th>
                        <th>Diastolic</th>
                        <th>Pulse</th>
                        <th>Medication</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readings as $reading): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reading['record_date']); ?></td>
                        <td><?php echo htmlspecialchars($reading['systolic']); ?></td>
                        <td><?php echo htmlspecialchars($reading['diastolic']); ?></td>
                        <td><?php echo htmlspecialchars($reading['pulse']); ?></td>
                        <td><?php echo htmlspecialchars($reading['medication']); ?></td>
                        <td><?php echo htmlspecialchars($reading['notes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No readings found. Add your first reading above!</p>
        <?php endif; ?>

    </div>
</main>

<script src="js/script.js"></script>

</body>
</html>