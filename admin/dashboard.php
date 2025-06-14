<?php
session_start();

// Ensure only logged-in admins can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);
// Event options (customize or fetch from DB)
$events = [
    "Nairobi County League Buruburu Girls",
    "Mental Health BasketBall Tournament"
];

// --- FILTER/SEARCH LOGIC ---
$search = trim($_GET['q'] ?? '');
$event_filter = trim($_GET['event'] ?? '');

$where = [];
if ($search !== '') {
    $search_sql = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_sql%' OR email LIKE '%$search_sql%' OR phone LIKE '%$search_sql%')";
}
if ($event_filter !== '' && $event_filter !== 'All Events') {
    $event_sql = $conn->real_escape_string($event_filter);
    $where[] = "event = '$event_sql'";
}
$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// Fetch filtered registrations
$registrations = [];
$sql = "SELECT * FROM registrations $where_sql ORDER BY registered_at DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $registrations[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afrikala Arts Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Favicon -->
    <link rel="icon" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.svg" type="image/svg+xml">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-backdrop.show { opacity: 0.3; }
        .navbar-brand {
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            height: 72px;
        }
        .navbar-brand img {
            height: 56px;
            width: auto;
            display: block;
            transition: height 0.2s;
        }
        @media (max-width: 700px) {
            .navbar-brand img {
                height: 40px;
            }
            .navbar-brand {
                height: 54px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../assets/Afrikala%20Art%20Branding%20Colours%20&%20Fonts-02.svg" alt="Afrikala Arts Logo" />
            </a>
            <div>
                Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?>
                <a href="change_password.php" class="btn btn-outline-light btn-sm ms-2">Change Password</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- Top controls: Add, Search/Filter, Export -->
        <div class="d-flex mb-3">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addRegistrationModal">
                + Add Registration
            </button>
            <form class="d-flex flex-grow-1" method="get" action="">
                <input class="form-control me-2" type="search" name="q" placeholder="Search name, email, or phone" value="<?= htmlspecialchars($search) ?>" />
                <select class="form-select me-2" name="event">
                    <option<?= $event_filter == '' || $event_filter == 'All Events' ? ' selected' : '' ?>>All Events</option>
                    <?php foreach ($events as $event): ?>
                        <option<?= $event_filter == $event ? ' selected' : '' ?>><?= htmlspecialchars($event) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-info" type="submit">Search/Filter</button>
            </form>
            <!-- Export as CSV or PDF -->
            <div class="dropdown ms-2">
                <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Export
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="export.php">Export as CSV</a></li>
                    <li><a class="dropdown-item" href="export_registrations_pdf.php" target="_blank">Export as PDF</a></li>
                </ul>
            </div>
        </div>

        <div id="successAlert" class="alert alert-success d-none"></div>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Event</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="registrationTable">
                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No registrations found.</td>
                    </tr>
                <?php else:
                    foreach ($registrations as $i => $reg): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($reg['name']) ?></td>
                            <td><?= htmlspecialchars($reg['age']) ?></td>
                            <td><?= htmlspecialchars($reg['email']) ?></td>
                            <td><?= htmlspecialchars($reg['phone']) ?></td>
                            <td><?= htmlspecialchars($reg['event']) ?></td>
                            <td><?= htmlspecialchars($reg['registered_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-secondary"
                                    onclick='showEditModal(<?= json_encode($reg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="deleteRegistration(<?= $reg['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Registration Modal: Only Name, Event, and Registered At required -->
    <div class="modal fade" id="addRegistrationModal" tabindex="-1" aria-labelledby="addRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addRegistrationForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRegistrationModalLabel">Add Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input name="name" type="text" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Age</label>
                        <input name="age" type="number" min="1" max="120" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input name="phone" type="text" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Event</label>
                        <select name="event" class="form-select" required>
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= htmlspecialchars($event) ?>"><?= htmlspecialchars($event) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Registered At</label>
                        <input name="registered_at" type="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Registration Modal: Only Name, Event, and Registered At required -->
    <div class="modal fade" id="editRegistrationModal" tabindex="-1" aria-labelledby="editRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editRegistrationForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRegistrationModalLabel">Edit Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input name="name" id="edit_name" type="text" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Age</label>
                        <input name="age" id="edit_age" type="number" min="1" max="120" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input name="email" id="edit_email" type="email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input name="phone" id="edit_phone" type="text" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Event</label>
                        <select name="event" id="edit_event" class="form-select" required>
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= htmlspecialchars($event) ?>"><?= htmlspecialchars($event) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Registered At</label>
                        <input name="registered_at" id="edit_registered_at" type="date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle Add Registration AJAX submission
        document.getElementById('addRegistrationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);
            fetch('add_registration.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('successAlert').classList.remove('d-none');
                        document.getElementById('successAlert').textContent = "Registration added successfully!";
                        // Add the new row to the table dynamically
                        let tbody = document.getElementById('registrationTable');
                        if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';
                        let row = tbody.insertRow(0);
                        row.innerHTML = `
                            <td>New</td>
                            <td>${data.registration.name || ''}</td>
                            <td>${data.registration.age || ''}</td>
                            <td>${data.registration.email || ''}</td>
                            <td>${data.registration.phone || ''}</td>
                            <td>${data.registration.event || ''}</td>
                            <td>${data.registration.registered_at || ''}</td>
                            <td></td>
                        `;
                        form.reset();
                        form.querySelector('[name="registered_at"]').value = '<?= date('Y-m-d') ?>';
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addRegistrationModal'));
                        modal.hide();
                    } else {
                        alert(data.error || "Failed to add registration.");
                    }
                })
                .catch(() => alert("Server error. Please try again."));
        });

        // Delete Registration via AJAX
        function deleteRegistration(id) {
            if (!confirm('Are you sure you want to delete this registration?')) return;
            fetch('delete_registration.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'registration_id=' + encodeURIComponent(id)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const btn = document.querySelector(`button[onclick="deleteRegistration(${id})"]`);
                        if (btn) btn.closest('tr').remove();
                    } else {
                        alert(data.error || 'Failed to delete registration.');
                    }
                });
        }

        // Show Edit Modal and Populate Fields
        function showEditModal(reg) {
            document.getElementById('edit_id').value = reg.id;
            document.getElementById('edit_name').value = reg.name;
            document.getElementById('edit_age').value = reg.age;
            document.getElementById('edit_email').value = reg.email;
            document.getElementById('edit_phone').value = reg.phone;
            document.getElementById('edit_event').value = reg.event;
            document.getElementById('edit_registered_at').value = reg.registered_at;
            var editModal = new bootstrap.Modal(document.getElementById('editRegistrationModal'));
            editModal.show();
        }

        // Handle Edit Form Submission via AJAX
        document.getElementById('editRegistrationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('edit_registration.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || "Failed to update registration.");
                    }
                })
                .catch(() => alert("Server error. Please try again."));
        });
    </script>
</body>
</html>