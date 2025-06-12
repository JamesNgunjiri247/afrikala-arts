<?php
// Handle edit registration AJAX request from admin dashboard

session_start();
header('Content-Type: application/json');

// Database connection settings
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Collect and sanitize input
$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$age = trim($_POST['age'] ?? ''); // optional
$email = trim($_POST['email'] ?? ''); // optional
$phone = trim($_POST['phone'] ?? ''); // optional
$event = trim($_POST['event'] ?? '');
$registered_at = $_POST['registered_at'] ?? date('Y-m-d');

// Only require id, name, event, registered_at
if (!$id || !$name || !$event || !$registered_at) {
    echo json_encode(['error' => 'Name, event, and date are required.']);
    exit;
}
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}

// Update registration in DB
$stmt = $conn->prepare("UPDATE registrations SET name=?, age=?, email=?, phone=?, event=?, registered_at=? WHERE id=?");
$stmt->bind_param("sissssi", $name, $age, $email, $phone, $event, $registered_at, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Database error.']);
}
$stmt->close();
$conn->close();
?>