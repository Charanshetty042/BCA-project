<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete all history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_history'])) {
    $delete_query = "DELETE FROM bookings WHERE user_id = ? AND (status = 'cancelled' OR status = 'accepted' OR status = 'completed')";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $_SESSION['success_message'] = 'All history deleted!';
    header('Location: history.php');
    exit();
}

// Handle delete single completed order
if (isset($_GET['delete_completed']) && isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_id = ? AND (status = 'accepted' OR status = 'completed')");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = 'Completed order deleted!';
    header('Location: history.php');
    exit();
}

// Fetch cancelled, accepted, and completed bookings
$query = "SELECT * FROM bookings WHERE user_id = ? AND (status = 'cancelled' OR status = 'accepted' OR status = 'completed') ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .history-card {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 2px solid #fecaca;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px 0 rgba(220, 38, 38, 0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .history-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(220, 38, 38, 0.18);
            border-color: #fca5a5;
        }
        .delete-history-btn {
            background: #fecaca;
            color: #b91c1c;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            margin-top: 1rem;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px 0 rgba(220, 38, 38, 0.08);
        }
        .delete-history-btn:hover {
            background: #fca5a5;
            color: #991b1b;
        }
        .history-empty {
            text-align: center;
            color: #b91c1c;
            font-size: 1.2rem;
            margin-top: 2rem;
            font-weight: 500;
            background: #fff1f2;
            border-radius: 1rem;
            padding: 2rem 1rem;
            box-shadow: 0 2px 8px 0 rgba(220, 38, 38, 0.08);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="text-center mb-4">History</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success text-center"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <form method="POST" class="text-center mb-4">
            <?php if ($result->num_rows > 0): ?>
                <button type="submit" name="delete_all_history" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Delete All History</button>
            <?php endif; ?>
        </form>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="history-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-danger">#<?php echo htmlspecialchars($row['booking_id']); ?></span>
                        <span class="text-muted small"><?php echo htmlspecialchars($row['date']); ?></span>
                    </div>
                    <div class="mb-1"><strong>Vehicle:</strong> <?php echo htmlspecialchars($row['vehicle_type']); ?></div>
                    <div class="mb-1"><strong>From:</strong> <?php echo htmlspecialchars($row['pickup_location']); ?> <i class="fas fa-arrow-right"></i> <strong>To:</strong> <?php echo htmlspecialchars($row['drop_location']); ?></div>
                    <div class="mb-1"><strong>Status:</strong> <span class="text-<?php echo $row['status'] === 'cancelled' ? 'danger' : ($row['status'] === 'completed' ? 'primary' : 'success'); ?> fw-bold"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></div>
                    <?php if ($row['status'] === 'accepted' || $row['status'] === 'completed'): ?>
                        <a href="history.php?delete_completed=1&amp;booking_id=<?php echo $row['booking_id']; ?>" class="delete-history-btn" onclick="return confirm('Are you sure you want to delete this completed order?');"><i class="fas fa-trash-alt"></i> Delete</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="history-empty">No History found</div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="dashboarduser.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
