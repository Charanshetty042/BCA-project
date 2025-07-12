<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit();
}
include 'db_connect.php';
$driver_id = $_SESSION['driver_id'];
$query = "SELECT b.booking_id, b.customer_name, b.pickup_location, b.drop_location, b.date, b.price, p.status AS payment_status
    FROM bookings b
    LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE v.driver_id = ?
    ORDER BY b.date DESC";
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
    <title>Driver Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Your Payment History</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['booking_id']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                    <td><?= htmlspecialchars($row['drop_location']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td>₹<?= number_format($row['price'], 2) ?></td>
                    <td>
                        <?php if ($row['payment_status'] === 'completed'): ?>
                            <span class="badge bg-success">Completed</span>
                        <?php elseif ($row['payment_status'] === 'pending' || $row['payment_status'] === null): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Other</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-center">
        <a href="driver_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
<?php $stmt->close(); ?>
