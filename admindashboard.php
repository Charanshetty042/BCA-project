<?php
// admindashboard.php
include 'header.php';
include 'db_connect.php';
session_start();
// You may want to add admin authentication here

// Booking statistics
$booking_stats = [
    'total' => 0,
    'completed' => 0,
    'pending' => 0,
    'cancelled' => 0
];
$res = $conn->query("SELECT COUNT(*) as total, SUM(status='completed') as completed, SUM(status='pending') as pending, SUM(status='cancelled') as cancelled FROM bookings");
if ($row = $res->fetch_assoc()) {
    $booking_stats = $row;
}
// Driver statistics
$driver_stats = [
    'total' => 0
];
$res = $conn->query("SELECT COUNT(*) as total FROM drivers");
if ($row = $res->fetch_assoc()) {
    $driver_stats = $row;
}
// User statistics
$customer_stats = [
    'total' => 0
];
$res = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type='customer'");
if ($row = $res->fetch_assoc()) {
    $customer_stats = $row;
}

// Contact messages
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .dashboard-card { border-radius: 1rem; box-shadow: 0 4px 24px 0 rgba(38,166,154,0.08); transition: box-shadow 0.2s; }
        .dashboard-card:hover { box-shadow: 0 8px 32px 0 rgba(38,166,154,0.18); }
        .user-card:hover { box-shadow: 0 8px 32px 0 rgba(38,166,154,0.18); background: #f1f5f9; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center bg-light">
                <h5 class="mb-2">Total Bookings</h5>
                <div class="display-6 fw-bold text-primary"><?php echo $booking_stats['total']; ?></div>
                <div class="small text-success">Completed: <?php echo $booking_stats['completed']; ?></div>
                <div class="small text-warning">Pending: <?php echo $booking_stats['pending']; ?></div>
                <div class="small text-danger">Cancelled: <?php echo $booking_stats['cancelled']; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center bg-light">
                <h5 class="mb-2">Total Drivers</h5>
                <div class="display-6 fw-bold text-primary"><?php echo $driver_stats['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center bg-light">
                <h5 class="mb-2">Total Customers</h5>
                <div class="display-6 fw-bold text-primary"><?php echo $customer_stats['total']; ?></div>
            </div>
        </div>
    </div>
    <!-- Recent Activity -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card dashboard-card p-4">
                <h5 class="mb-3">Recent Bookings</h5>
                <ul class="list-group">
                <?php
                $recent = $conn->query("SELECT booking_id, user_id, status, date FROM bookings ORDER BY date DESC LIMIT 5");
                if ($recent->num_rows > 0) {
                    while ($b = $recent->fetch_assoc()) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">#'.htmlspecialchars($b['booking_id']).' <span class="badge bg-info">'.htmlspecialchars(ucfirst($b['status'])).'</span> <span class="text-muted small">'.htmlspecialchars($b['date']).'</span></li>';
                    }
                } else {
                    echo '<li class="list-group-item">No recent bookings.</li>';
                }
                ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card p-4">
                <h5 class="mb-3">Recent Drivers</h5>
                <ul class="list-group">
                <?php
                $recent = $conn->query("SELECT name, vehicle_type FROM drivers ORDER BY id DESC LIMIT 5");
                if ($recent->num_rows > 0) {
                    while ($d = $recent->fetch_assoc()) {
                        echo '<li class="list-group-item">'.htmlspecialchars($d['name']).' <span class="badge bg-secondary">'.htmlspecialchars($d['vehicle_type']).'</span></li>';
                    }
                } else {
                    echo '<li class="list-group-item">No recent drivers.</li>';
                }
                ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- Admin Profile Section -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card dashboard-card p-4">
                <h5 class="mb-3">Admin Profile</h5>
                <p><b>Username:</b> <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'admin'; ?></p>
                <a href="profile.php" class="btn btn-outline-secondary">Edit Profile</a>
                <a href="update_password.php" class="btn btn-outline-warning ms-2">Change Password</a>
            </div>
        </div>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center">
                <h4>Manage Drivers</h4>
                <a href="managdriver.php" class="btn btn-primary mt-3">View Drivers</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center">
                <h4>Manage Customers</h4>
                <a href="managcustomer.php" class="btn btn-primary mt-3">View Customers</a>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['show_users'])): ?>
    <div class="row mt-5">
        <h3 class="text-center mb-4">All Users</h3>
        <?php
        $users = $conn->query("SELECT * FROM users");
        if ($users->num_rows > 0) {
            while ($u = $users->fetch_assoc()) {
                $uid = isset($u['id']) ? (int)$u['id'] : 0;
                $uname = isset($u['username']) ? $u['username'] : 'Unknown';
                $uemail = isset($u['email']) ? $u['email'] : '';
                echo '<div class="col-md-3 mb-4">';
                echo '<div class="card user-card h-100 p-3 text-center" style="cursor:pointer;" onclick="showUserModal('.$uid.')">';
                echo '<h5>'.htmlspecialchars($uname).'</h5>';
                echo '<div class="text-muted">'.htmlspecialchars($uemail).'</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12 text-center">No users found.</div>';
        }
        ?>
    </div>
    <!-- User Info Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel">User Info</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="userModalBody">
            <!-- User info will be loaded here -->
          </div>
        </div>
      </div>
    </div>
    <script>
    function showUserModal(userId) {
        fetch('admindashboard.php?get_user_info=1&id=' + userId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('userModalBody').innerHTML = html;
            var myModal = new bootstrap.Modal(document.getElementById('userModal'));
            myModal.show();
        });
    }
    function removeUser(userId) {
        if(confirm('Are you sure you want to remove this user?')) {
            fetch('admindashboard.php?delete_user=1&id=' + userId, {method: 'POST'})
            .then(res => res.text())
            .then(msg => { alert(msg); location.reload(); });
        }
    }
    </script>
    <?php endif; ?>
    <div class="container my-5">
        <h2 class="mb-4">Contact Us Messages</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($messages && $messages->num_rows > 0): ?>
                    <?php while ($row = $messages->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No messages found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Admin Reports Section -->
    <div class="container my-5">
        <h2 class="mb-4">Admin Reports</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Report ID</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Generated At</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $reports = $conn->query("SELECT * FROM admin_reports ORDER BY generated_at DESC");
                if ($reports && $reports->num_rows > 0):
                    while ($report = $reports->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $report['report_id']; ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $report['report_type'])); ?></td>
                        <td style="max-width:350px; white-space:pre-wrap; word-break:break-word;"><?php echo nl2br(htmlspecialchars($report['details'])); ?></td>
                        <td><?php echo $report['generated_at']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No reports found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>
</body>
</html>
<?php
// AJAX: Show user info and total orders
if (isset($_GET['get_user_info']) && isset($_GET['id'])) {
    $uid = intval($_GET['id']);
    $user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    $orders = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id=$uid")->fetch_assoc();
    echo '<b>Username:</b> '.htmlspecialchars($user['username']).'<br>';
    echo '<b>Email:</b> '.htmlspecialchars($user['email']).'<br>';
    echo '<b>Phone:</b> '.htmlspecialchars($user['phone'] ?? '').'<br>';
    echo '<b>Total Orders:</b> '.htmlspecialchars($orders['total']).'<br>';
    echo '<button class="btn btn-danger mt-3" onclick="removeUser('.$uid.')">Remove User</button>';
    exit;
}
// AJAX: Remove user
if (isset($_GET['delete_user']) && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = intval($_GET['id']);
    $conn->query("DELETE FROM users WHERE id=$uid");
    $conn->query("DELETE FROM bookings WHERE user_id=$uid"); // Optionally remove user's bookings
    echo 'User removed successfully.';
    exit;
}
?>
