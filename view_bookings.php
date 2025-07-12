<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// AJAX handler for deleting cancelled bookings
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['delete_history']) && $_GET['delete_history'] == 1) {
    session_start();
    include 'db_connect.php';
    if (!isset($_SESSION['user_id'])) exit('Not logged in');
    $user_id = $_SESSION['user_id'];
    if (isset($_GET['all']) && $_GET['all'] == 1) {
        // Delete all cancelled bookings for this user
        $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND status = 'cancelled'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        exit('All cancelled bookings deleted');
    } elseif (isset($_GET['booking_id'])) {
        $booking_id = intval($_GET['booking_id']);
        $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_id = ? AND status = 'cancelled'");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        exit('Cancelled booking deleted');
    }
    exit();
}

// AJAX handler for cancelled bookings
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['status']) && $_GET['status'] == 'cancelled') {
    include 'db_connect.php';
    if (!isset($_SESSION['user_id'])) {
        exit('Not logged in');
    }
    $user_id = $_SESSION['user_id'];
    $query = "SELECT b.*, MIN(d.name) AS driver_name, MIN(d.vehicle_reg_no) AS driver_reg_no FROM bookings b
        LEFT JOIN drivers d ON b.vehicle_type = d.vehicle_type
        WHERE b.user_id = ? AND b.status = 'cancelled' GROUP BY b.booking_id ORDER BY b.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        while ($booking = $result->fetch_assoc()) {
            echo '<div class="bg-white rounded-xl shadow p-4 mb-4 border border-red-200">';
            echo '<div class="mb-2 text-red-700 font-bold">Booking #'.htmlspecialchars($booking['booking_id']).'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Vehicle Type:</b> '.htmlspecialchars($booking['vehicle_type']).'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Pickup:</b> '.htmlspecialchars($booking['pickup_location']).'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Drop:</b> '.htmlspecialchars($booking['drop_location']).'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Date:</b> '.htmlspecialchars($booking['date']).'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Driver Name:</b> '.htmlspecialchars($booking['driver_name'] ?? 'N/A').'</div>';
            echo '<div class="mb-1 text-gray-700"><b>Registration No:</b> '.htmlspecialchars($booking['driver_reg_no'] ?? 'N/A').'</div>';
            echo '<button class="delete-history-btn bg-red-200 hover:bg-red-300 text-red-800 font-semibold py-1 px-4 rounded shadow transition duration-150 text-sm" data-booking-id="'.htmlspecialchars($booking['booking_id']).'"><i class="fas fa-trash-alt"></i> Delete</button>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="text-center text-gray-500">No cancelled bookings found.</div>';
    }
    exit();
}

// Fetch bookings with driver name and registration number using vehicles table
$query = "SELECT b.*, v.registration_no, v.name AS vehicle_name, d.name AS driver_name, d.vehicle_reg_no AS driver_reg_no
    FROM bookings b
    LEFT JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    LEFT JOIN drivers d ON v.driver_id = d.id
    WHERE b.user_id = ? AND b.status != 'cancelled'
    ORDER BY b.date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f0f4f8 0%, #e0e7ef 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen">
    <div class="container mx-auto py-8">
        <h1 class="mb-8 text-center text-3xl font-bold text-blue-900">Your Bookings</h1>
        <?php if ($bookings_result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col justify-between border border-blue-100">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-lg font-semibold text-blue-700">Booking #<?php echo htmlspecialchars($booking['booking_id']); ?></span>
                                <span class="text-xs px-2 py-1 rounded <?php
                                    $status = $booking['status'];
                                    if ($status === 'confirmed') echo 'bg-green-100 text-green-700';
                                    elseif ($status === 'pending') echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($status === 'cancelled' || $status === 'rejected') echo 'bg-red-100 text-red-700';
                                    else echo 'bg-gray-200 text-gray-700';
                                ?>">
                                    <?php
                                    if ($status === 'confirmed') echo 'Accepted';
                                    elseif ($status === 'pending') echo 'Pending';
                                    elseif ($status === 'cancelled') echo 'Cancelled';
                                    elseif ($status === 'rejected') echo 'Rejected';
                                    else echo htmlspecialchars(ucfirst($status));
                                    ?>
                                </span>
                            </div>
                            <div class="mb-2 text-gray-700"><b>Vehicle Type:</b> <?php echo htmlspecialchars($booking['vehicle_type']); ?></div>
                            <div class="mb-2 text-gray-700"><b>Pickup:</b> <?php echo htmlspecialchars($booking['pickup_location']); ?></div>
                            <div class="mb-2 text-gray-700"><b>Drop:</b> <?php echo htmlspecialchars($booking['drop_location']); ?></div>
                            <div class="mb-2 text-gray-700"><b>Date:</b> <?php echo htmlspecialchars($booking['date']); ?></div>
                            <div class="mb-2 text-gray-700"><b>Driver Name:</b> <?php echo htmlspecialchars($booking['driver_name'] ?? 'N/A'); ?></div>
                            <div class="mb-2 text-gray-700"><b>Registration No:</b> <?php echo htmlspecialchars($booking['driver_reg_no'] ?? 'N/A'); ?></div>
                            <?php
                            // Check payment status for this booking
                            $paid = false;
                            $pay_stmt = $conn->prepare("SELECT status FROM payments WHERE booking_id = ? AND customer_id = ?");
                            $pay_stmt->bind_param("ii", $booking['booking_id'], $user_id);
                            $pay_stmt->execute();
                            $pay_stmt->bind_result($payment_status);
                            if ($pay_stmt->fetch() && $payment_status === 'completed') {
                                $paid = true;
                            }
                            $pay_stmt->close();
                            $can_pay = !$paid && !in_array($booking['status'], ['completed', 'cancelled', 'rejected']);
                            ?>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed' || $booking['status'] === 'on the way' || $booking['status'] === 'reached' || $booking['status'] === 'pickedup' || $booking['status'] === 'delivered'): ?>
                                <form action="cancel_booking.php" method="POST" class="cancel-booking-form" data-booking-id="<?php echo $booking['booking_id']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">Cancel</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($can_pay): ?>
                                <a href="payment_page.php?booking_id=<?php echo $booking['booking_id']; ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">Pay Now</a>
                            <?php elseif ($paid): ?>
                                <span class="inline-block bg-green-100 text-green-700 font-semibold py-2 px-4 rounded-lg shadow">Paid</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">No bookings found.</p>
        <?php endif; ?>
        <div class="text-center mt-8">
            <a href="dashboarduser.php" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">Back to Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
