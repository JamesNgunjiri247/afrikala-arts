<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "afrikala_arts";
    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$username || !$pass) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($admin_id, $hash);
            $stmt->fetch();
            if (password_verify($pass, $hash)) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $username;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Afrikala Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/style.css" rel="stylesheet" />
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <h2 class="mb-4 text-center">Admin Login</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="username" required autofocus />
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required />
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">Show Password</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <a href="../index.html">&larr; Back to site</a>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('showPassword').addEventListener('change', function() {
        var pwd = document.getElementById('password');
        pwd.type = this.checked ? 'text' : 'password';
    });
    </script>
</body>
</html>