<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get driver id from session
$driver_id = null;
$stmt = $conn->prepare("SELECT id FROM drivers WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $driver_id = $row['id'];
}
$stmt->close();
if (!$driver_id) {
    echo '<div class="alert alert-danger text-center mt-5">No driver profile found. Please complete your profile.</div>';
    exit();
}

// Handle delete completed order
if (isset($_GET['delete_completed']) && isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();
    header('Location: trips_history.php?deleted=1');
    exit();
}
// Handle delete cancelled order
if (isset($_GET['delete_cancelled']) && isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND status = 'cancelled'");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();
    header('Location: trips_history.php?cancelled_deleted=1');
    exit();
}

// Fetch all accepted orders for this driver
$query = "SELECT b.booking_id, b.customer_name, b.phone_number, b.vehicle_type, b.pickup_location, b.drop_location, b.date, b.status FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status IN ('confirmed','on the way','reached','pickedup','delivered','completed') ORDER BY b.date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
// Fetch all cancelled orders for this driver
$cancelled_query = "SELECT b.booking_id, b.customer_name, b.phone_number, b.vehicle_type, b.pickup_location, b.drop_location, b.date, b.status FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'cancelled' ORDER BY b.date DESC";
$stmt2 = $conn->prepare($cancelled_query);
$stmt2->bind_param("i", $driver_id);
$stmt2->execute();
$cancelled_result = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted & Cancelled Orders History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="driver_dashboard.php" class="btn btn-secondary mb-4">&larr; Back</a>
    <h2 class="mb-4 text-center">Accepted Orders History</h2>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success text-center">Completed order deleted successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['cancelled_deleted'])): ?>
        <div class="alert alert-success text-center">Cancelled order deleted successfully.</div>
    <?php endif; ?>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Vehicle Type</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['drop_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars(ucwords($row['status'])); ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'completed'): ?>
                            <a href="trips_history.php?delete_completed=1&amp;booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this completed order?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center text-muted">No accepted orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <h2 class="mb-4 text-center">Cancelled Orders History</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Vehicle Type</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($cancelled_result && $cancelled_result->num_rows > 0): ?>
                <?php while ($row = $cancelled_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['drop_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><span class="badge bg-danger text-white">Cancelled</span></td>
                    <td>
                        <a href="trips_history.php?delete_cancelled=1&amp;booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this cancelled order?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center text-muted">No cancelled orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>