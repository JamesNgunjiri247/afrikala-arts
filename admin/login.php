<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Favicon (SVG for modern browsers, PNG fallback) -->
    <link rel="icon" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.svg" type="image/svg+xml">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,700&display=swap" rel="stylesheet">
    <style>
      html, body {
        height: 100%;
        min-height: 100vh;
      }
      body {
        font-family: 'Quicksand', Arial, sans-serif;
        min-height: 100vh;
        margin: 0;
        padding: 0;
        background: linear-gradient(110deg, #075b37 60%, #bada7c 100%);
        background-size: 200% 200%;
        animation: gradientMotion 8s ease-in-out infinite alternate;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      @keyframes gradientMotion {
        0% { background-position: 100% 0%; }
        100% { background-position: 0% 100%; }
      }
      .admin-login-box {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 8px 42px 0 rgba(7,91,55,0.13);
        padding: 2.5rem 2rem 2rem 2rem;
        max-width: 380px;
        width: 100%;
        margin: 0 auto;
        margin-top: 2.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      .site-logo {
        margin-bottom: 1.2rem;
        width: 120px;
        max-width: 60vw;
        height: auto;
        display: block;
      }
      .admin-login-box h2 {
        font-weight: bold;
        color: #075b37;
        text-align: center;
        margin-bottom: 1.6rem;
        letter-spacing: .01em;
      }
      .admin-login-box .form-label {
        font-weight: 500;
        color: #075b37;
        margin-bottom: .4rem;
      }
      .admin-login-box .form-control {
        border-radius: 1.5em;
        border: 1.5px solid #bada7c;
        font-size: 1.08rem;
        background: #f6f8f7;
        color: #075b37;
        margin-bottom: 8px;
      }
      .admin-login-box .form-control:focus {
        border-color: #075b37;
        box-shadow: 0 0 0 0.1rem #bada7c60;
      }
      .admin-login-box .btn-primary {
        width: 100%;
        border-radius: 2em;
        background: #075b37;
        border: none;
        font-weight: bold;
        font-size: 1.1rem;
        box-shadow: 0 1px 9px rgba(7,91,55,0.06);
        transition: background 0.18s;
        margin-top: 8px;
      }
      .admin-login-box .btn-primary:hover,
      .admin-login-box .btn-primary:focus {
        background: #0b7d4a;
      }
      .admin-login-box .alert {
        border-radius: 1em;
        font-size: .98rem;
      }
      .admin-login-box .form-check-label {
        font-size: 0.97rem;
      }
      .admin-login-box .back-link {
        color: #075b37;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.18s;
      }
      .admin-login-box .back-link:hover {
        text-decoration: underline;
        color: #0b7d4a;
      }
      @media (max-width: 600px) {
        .admin-login-box {
          padding: 1.2rem .7rem 1.3rem .7rem;
          max-width: 97vw;
        }
        .site-logo {
          width: 80px;
        }
        .admin-login-box h2 {
          font-size: 1.38rem;
        }
      }
    </style>
</head>
<body>
    <div class="admin-login-box">
        <!-- Website Logo only, large, no text -->
        <img src="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.svg" alt="Afrikala Arts Logo" class="site-logo" />
        <h2>Admin Login</h2>
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
            <button type="submit" class="btn btn-primary mb-2">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.html" class="back-link">&larr; Back to site</a>
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