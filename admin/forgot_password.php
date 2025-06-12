<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    if (!$username) {
        $error = "Enter your username.";
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM admins WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($admin = $res->fetch_assoc()) {
            // Generate token and expiry
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

            $stmt2 = $conn->prepare("UPDATE admins SET reset_token=?, reset_expires=? WHERE id=?");
            $stmt2->bind_param("ssi", $token, $expires, $admin['id']);
            $stmt2->execute();

            // Email setup: Replace this with your admin's real email address!
            $to = "jamesngunjiri247@gmail.com";
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
            $subject = "Afrikala Arts Admin Password Reset";
            $message = "Hello,\n\nClick the link below to reset your password:\n$reset_link\n\nIf you didn't request this, ignore this email.";
            $headers = "From: no-reply@afrikala-arts.com\r\n";
            mail($to, $subject, $message, $headers);

            $success = "A password reset link has been sent to the registered admin email.";
        } else {
            $error = "No admin found with that username.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | Afrikala Arts Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <h2 class="mb-4 text-center">Forgot Password</h2>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="username" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
      </form>
      <div class="text-center mt-3">
        <a href="login.php">&larr; Back to Login</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>