<?php
session_start();
include 'db_connect.php';

// Only show bookings for the logged-in driver (except pending/cancelled)
$driver_id = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id FROM drivers WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $driver_id = $row['id'];
    }
    $stmt->close();
}

$result = null;
if ($driver_id) {
    $stmt = $conn->prepare("SELECT b.booking_id, b.user_id, b.vehicle_type, b.pickup_location, b.drop_location, b.date, b.status, b.customer_name, b.phone_number FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status NOT IN ('pending','cancelled') ORDER BY b.date DESC");
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // No driver profile found
    echo '<div class="alert alert-danger text-center mt-5">No driver profile found. Please complete your profile.</div>';
    exit();
}

// Handle success message
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="driver_dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>
    <h2 class="mb-4 text-center">Order Status Management</h2>
    <?php if ($success): ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>User ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Vehicle Type</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $hasUpdatable = false;
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
                    $status_flow = ['confirmed','on the way','reached','pickedup','delivered','completed','cancelled'];
                    $current_index = array_search($row['status'], $status_flow);
                    $canUpdate = ($current_index !== false && $row['status'] !== 'completed' && $row['status'] !== 'cancelled');
                    if ($canUpdate) $hasUpdatable = true;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['drop_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <?php
                        if ($canUpdate) {
                            $next_statuses = array_slice($status_flow, $current_index + 1);
                            if (!empty($next_statuses)) {
                        ?>
                            <form method="POST" action="update_status.php" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                <select name="status" class="form-select form-select-sm" required>
                                    <?php
                                    foreach ($next_statuses as $status) {
                                        echo "<option value=\"$status\">".ucwords($status)."</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        <?php
                            }
                        } else {
                            echo '<span class="text-muted">No updation</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile;
            } else {
                echo '<tr><td colspan="10" class="text-center text-muted">No orders found.</td></tr>';
            }
            if (!$hasUpdatable && $result && $result->num_rows > 0) {
                echo '<tr><td colspan="10" class="text-center text-warning">No orders available for status update.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>