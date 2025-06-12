<?php
// Handle add registration AJAX request from admin dashboard

header('Content-Type: application/json');
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

// Collect and sanitize input
$name = trim($_POST['name'] ?? '');
$age = trim($_POST['age'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$event = trim($_POST['event'] ?? '');
$registered_at = $_POST['registered_at'] ?? date('Y-m-d');

// Only require name, event, registered_at
if (!$name || !$event || !$registered_at) {
    echo json_encode(['error' => 'Name, event, and date are required.']);
    exit;
}
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}

// Save to DB
$stmt = $conn->prepare("INSERT INTO registrations (name, age, email, phone, event, registered_at) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissss", $name, $age, $email, $phone, $event, $registered_at);
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'registration' => [
            'name' => htmlspecialchars($name),
            'age' => $age,
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'event' => htmlspecialchars($event),
            'registered_at' => htmlspecialchars($registered_at)
        ]
    ]);
} else {
    echo json_encode(['error' => 'Database error.']);
}
$stmt->close();
$conn->close();