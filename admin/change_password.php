<?php
session_start();
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
    die("Connection failed: " . $conn->connect_error);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $admin_id = $_SESSION['admin_id'];

    if (!$current || !$new || !$confirm) {
        $error = "All fields are required.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 8) {
        $error = "New password must be at least 8 characters.";
    } else {
        $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $hash)) {
            $error = "Your current password is incorrect.";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hash, $admin_id);
            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Error updating password.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password | Afrikala Arts Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <h2 class="mb-4 text-center">Change Password</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" id="current_password" required />
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required minlength="8" />
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required minlength="8" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Change Password</button>
                </form>
                <div class="text-center mt-3">
                    <a href="dashboard.php">&larr; Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>