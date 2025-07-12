<?php include 'header.php'; ?>
<?php
session_start();
//include 'driver_dashboard.php';
include 'db_connect.php';

// Get driver id from session
$driver_id = null;
$stmt = $conn->prepare("SELECT id FROM drivers WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $driver_id = $row['id'];
}
$stmt->close();

if (!$driver_id) {
    echo '<div class="container mt-5"><div class="alert alert-danger">No driver profile found. Please complete your profile.</div></div>';
    exit();
}

// Handle accept/reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'] === 'accept' ? 'confirmed' : 'rejected';
    // Only allow update if booking is assigned to this driver and is pending
    $update = $conn->prepare("UPDATE bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id SET b.status = ? WHERE b.booking_id = ? AND v.driver_id = ? AND b.status = 'pending'");
    $update->bind_param("sii", $action, $booking_id, $driver_id);
    $update->execute();
    if ($update->affected_rows > 0) {
        $msg = ($action === 'confirmed') ? 'Booking accepted.' : 'Booking rejected.';
        $update->close();
        if ($action === 'confirmed') {
            echo '<div id="order-accepted-animation" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.95);display:flex;align-items:center;justify-content:center;flex-direction:column;">';
            echo '<div style="background:#43a047;border-radius:50%;width:120px;height:120px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 24px rgba(67,160,71,0.2);animation:bounceIn 0.7s;">';
            echo '<svg width="70" height="70" viewBox="0 0 70 70"><circle cx="35" cy="35" r="33" stroke="#fff" stroke-width="4" fill="none"/><polyline points="20,38 32,50 50,25" style="fill:none;stroke:#fff;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;"/></svg>';
            echo '</div>';
            echo '<h2 style="color:#43a047;margin-top:24px;font-weight:700;font-size:2rem;font-family:sans-serif;letter-spacing:1px;">Order Accepted</h2>';
            echo '<p style="color:#333;font-size:1.1rem;">Redirecting...</p>';
            echo '</div>';
            echo '<style>@keyframes bounceIn{0%{transform:scale(0.5);}60%{transform:scale(1.1);}80%{transform:scale(0.95);}100%{transform:scale(1);}}</style>';
            echo '<script>setTimeout(function(){window.location.href = "driver_order.php?msg=Order+Accepted";}, 5000);</script>';
            exit();
        } else {
            header('Location: driver_order.php?msg=' . urlencode($msg));
            exit();
        }
    } else {
        $update->close();
        header('Location: driver_order.php?msg=' . urlencode('Action could not be performed. It may have already been updated.'));
        exit();
    }
}

// Fetch bookings assigned to this driver (pending only)
$query = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone, v.name AS vehicle_name FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id JOIN users u ON b.user_id = u.user_id WHERE v.driver_id = ? AND b.status = 'pending' ORDER BY b.date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var action = '';
                var acceptBtn = form.querySelector('button[name="action"][value="accept"]');
                var rejectBtn = form.querySelector('button[name="action"][value="reject"]');
                if (document.activeElement === acceptBtn) {
                    action = 'accept';
                } else if (document.activeElement === rejectBtn) {
                    action = 'reject';
                }
                if (action === 'accept') {
                    if (!confirm('Are you sure you want to accept this booking?')) {
                        e.preventDefault();
                    }
                } else if (action === 'reject') {
                    if (!confirm('Are you sure you want to reject this booking?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    });
    </script>
</head>
<body>
<div class="container mt-5">
    <a href="driver_dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>
    <h2 class="mb-4">Pending Bookings Assigned to You</h2>
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['customer_phone']); ?></td>
                    <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($booking['drop_location']); ?></td>
                    <td><?php echo htmlspecialchars($booking['date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                    <td>₹<?php echo htmlspecialchars($booking['price']); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:8px; justify-content:center; align-items:center;">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No pending bookings assigned to you.</div>
    <?php endif; ?>
</div>
</body>
</html>
