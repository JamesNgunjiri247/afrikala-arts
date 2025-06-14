<?php
// Handle delete registration AJAX request from admin dashboard

session_start();
header('Content-Type: application/json');

// Only accept POST and require registration_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['registration_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

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

$id = intval($_POST['registration_id']);
$stmt = $conn->prepare("DELETE FROM registrations WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Could not delete registration.']);
}

$stmt->close();
$conn->close();
?>