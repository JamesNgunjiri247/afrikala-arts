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

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM registrations WHERE id = $del_id");
    header("Location: dashboard.php?msg=deleted");
    exit;
}

// Search/filter
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

// Fetch unique events for filter dropdown
$events = [];
$event_res = $conn->query("SELECT DISTINCT event FROM registrations");
while ($ev = $event_res->fetch_assoc()) {
    $events[] = $ev['event'];
}

// Fetch registrations
$sql = "SELECT id, name, email, phone, event, registered_at FROM registrations $where_sql ORDER BY registered_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Afrikala Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/style.css" rel="stylesheet" />
    <style>
      .table-actions { white-space: nowrap; }
      .search-bar { max-width: 350px; }
      .filter-bar { max-width: 220px; }
      @media (max-width: 600px) {
        .search-bar, .filter-bar { max-width: 100%; }
      }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <span class="navbar-brand fw-bold">Afrikala Arts Admin</span>
        <div class="ms-auto">
          <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
          <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
      </div>
    </nav>
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <form class="d-flex flex-wrap gap-2" method="get" action="dashboard.php">
                <input type="text" class="form-control search-bar" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or email">
                <select class="form-select filter-bar" name="event">
                    <option value="">All Events</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= htmlspecialchars($ev) ?>" <?= ($event_filter == $ev) ? "selected" : "" ?>><?= htmlspecialchars($ev) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Search/Filter</button>
                <?php if($search || $event_filter): ?>
                  <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
            <a href="export.php?search=<?= urlencode($search) ?>&event=<?= urlencode($event_filter) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Export as CSV
            </a>
        </div>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
          <div class="alert alert-success">Registration deleted successfully.</div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Event</th>
                        <th>Registered At</th>
                        <th class="table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['event']) ?></td>
                                <td><?= $row['registered_at'] ?></td>
                                <td class="table-actions">
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                      onclick="return confirm('Delete this registration?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No registrations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons CDN for export icon (optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
<?php $conn->close(); ?>