<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit();
}
$driver_id = $_SESSION['driver_id'];

// Fetch all ratings received by this driver
$query = "SELECT r.rating_id, r.rating, r.review, r.created_at, u.name AS customer_name, u.phone, b.booking_id FROM ratings r JOIN bookings b ON r.booking_id = b.booking_id JOIN users u ON r.customer_id = u.user_id WHERE r.driver_id = ? ORDER BY r.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Received Ratings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .star { color: #ffc107; font-size: 1.2rem; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">Ratings Received from Customers</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Customer Phone</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-star star <?php echo $i <= $row['rating'] ? 'fas' : 'far'; ?>"></i>
                        <?php endfor; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['review']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No ratings found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="text-center mt-4">
        <a href="driver_dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>
</body>
</html>
