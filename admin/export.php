<?php
session_start();
// Optional: Ensure only admins can export
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=registrations.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Age', 'Email', 'Phone', 'Event', 'Registered At']);

$sql = "SELECT id, name, age, email, phone, event, registered_at FROM registrations ORDER BY registered_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}
fclose($output);
$conn->close();
exit;
?>