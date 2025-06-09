<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search/filter
$search = trim($_GET['search'] ?? '');
$event_filter = trim($_GET['event'] ?? '');

$where = [];
if ($search) {
    $search_sql = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_sql%' OR email LIKE '%$search_sql%')";
}
if ($event_filter) {
    $event_sql = $conn->real_escape_string($event_filter);
    $where[] = "event = '$event_sql'";
}
$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=registrations.csv');

// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Event', 'Registered At']);

$sql = "SELECT id, name, email, phone, event, registered_at FROM registrations $where_sql ORDER BY registered_at DESC";
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