<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

$error = '';
$success = '';
$show_form = false;
$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $conn->prepare("SELECT id, reset_expires FROM admins WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($admin = $res->fetch_assoc()) {
        if (strtotime($admin['reset_expires']) > time()) {
            $show_form = true;
            $admin_id = $admin['id'];
        } else {
            $error = "Reset link has expired.";
        }
    } else {
        $error = "Invalid reset link.";
    }
    $stmt->close();
} else {
    $error = "Invalid or missing token.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$new || !$confirm) {
        $error = "All fields are required.";
        $show_form = true;
    } elseif ($new !== $confirm) {
        $error = "Passwords do not match.";
        $show_form = true;
    } elseif (strlen($new) < 8) {
        $error = "Password must be at least 8 characters.";
        $show_form = true;
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE admins SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
        $stmt2->bind_param("si", $new_hash, $admin_id);
        if ($stmt2->execute()) {
            $success = "Password has been reset. You can now <a href='login.php'>login</a>.";
            $show_form = false;
        } else {
            $error = "Could not update password.";
            $show_form = true;
        }
        $stmt2->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | Afrikala Arts Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <h2 class="mb-4 text-center">Reset Password</h2>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($show_form): ?>
      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" class="form-control" name="new_password" id="new_password" required minlength="8" />
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" name="confirm_password" id="confirm_password" required minlength="8" />
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
      </form>
      <?php endif; ?>
      <div class="text-center mt-3">
        <a href="login.php">&larr; Back to Login</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>