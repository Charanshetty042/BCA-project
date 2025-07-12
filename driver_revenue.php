<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit();
}
$driver_id = (int)$_SESSION['driver_id'];
$today = date('Y-m-d');
$totalRevenue = 0;
$todayRevenue = 0;

// Total revenue
$stmt = $conn->prepare("SELECT SUM(price) as total FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'completed'");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$stmt->bind_result($totalRevenue);
$stmt->fetch();
$stmt->close();
// Today's revenue
$stmt = $conn->prepare("SELECT SUM(price) as today FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'completed' AND DATE(b.date) = ?");
$stmt->bind_param("is", $driver_id, $today);
$stmt->execute();
$stmt->bind_result($todayRevenue);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Revenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="driver_dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>
    <h2 class="mb-4 text-center">My Revenue</h2>
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="card shadow p-4 text-center">
                <h4>Total Revenue</h4>
                <div class="display-5 fw-bold text-success">₹<?php echo number_format($totalRevenue ?? 0, 2); ?></div>
                <hr>
                <h5>Today's Revenue</h5>
                <div class="display-6 fw-bold text-primary">₹<?php echo number_format($todayRevenue ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow p-4">
                <h5 class="mb-3">Completed Orders</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Booking ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Pickup</th>
                                <th>Drop</th>
                                <th>Price (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT b.booking_id, b.date, b.customer_name, b.pickup_location, b.drop_location, b.price FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'completed' ORDER BY b.date DESC");
                        $stmt->bind_param("i", $driver_id);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['booking_id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['date']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['pickup_location']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['drop_location']) . '</td>';
                                echo '<td>₹' . number_format($row['price'], 2) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-muted">No completed orders found.</td></tr>';
                        }
                        $stmt->close();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
