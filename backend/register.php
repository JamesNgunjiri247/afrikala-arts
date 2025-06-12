<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = trim($_POST['name'] ?? '');
$age = isset($_POST['age']) ? intval($_POST['age']) : null;
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$event = trim($_POST['event'] ?? '');

if (!$name || !$event) 
{
    echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    exit;
}

$stmt = $conn->prepare("INSERT INTO registrations (name, age, email, phone, event) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sisss", $name, $age, $email, $phone, $event);

if ($stmt->execute()) {
    header("Location: thankyou.php");
    exit();
} else {
    echo "<div class='alert alert-danger mt-4 text-center fw-bold shadow-sm'>
        Something went wrong. Please try again.
      </div>";
}
$stmt->close();
$conn->close();
?>