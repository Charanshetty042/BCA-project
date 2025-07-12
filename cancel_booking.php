<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f0f4f8 0%, #e0e7ef 100%);
            background-image: url('images/icon-of-truck-semi-truck-vector-illustration.webp');
            background-repeat: no-repeat;
            background-size: 400px;
            background-position: right bottom;
        }
        .cancel-card {
            max-width: 420px;
            margin: 60px auto;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            background: #fff;
            padding: 2.5rem 2rem;
            animation: fadeInUp 0.7s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .cancel-title {
            color: #dc2626;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .cancel-success {
            color: #059669;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .cancel-btn {
            @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg shadow mr-2 transition duration-150;
        }
        .back-btn {
            @apply bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg shadow transition duration-150;
        }
        .main-bg {
            @apply min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100;
        }
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .success-anim {
            animation: popSuccess 0.7s cubic-bezier(0.23, 1, 0.32, 1);
        }
        @keyframes popSuccess {
            0% {
                opacity: 0;
                transform: scale(0.7);
            }
            80% {
                opacity: 1;
                transform: scale(1.1);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    // Fetch booking to confirm ownership
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo '<div class="text-center text-danger mt-5">Booking not found or not authorized.</div>';
        exit();
    }
    $booking = $result->fetch_assoc();
    // If not confirmed yet, show confirmation prompt
    if (!isset($_POST['confirm'])) {
        echo '<div class="main-bg">';
        echo '<div class="cancel-card text-center">';
        echo '<h2 class="cancel-title mb-4">Cancel Booking?</h2>';
        echo '<p class="mb-4">Are you sure you want to cancel booking <b>#'.htmlspecialchars($booking_id).'</b>?</p>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="booking_id" value="'.htmlspecialchars($booking_id).'">';
        echo '<input type="hidden" name="confirm" value="1">';
        echo '<button type="submit" class="cancel-btn">Yes, Cancel</button>';
        echo '<a href="view_bookings.php" class="back-btn">No, Go Back</a>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        exit();
    }
    // Perform cancellation
    $update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
    $update->bind_param("i", $booking_id);
    $update->execute();
    echo '<div class="main-bg">';
    echo '<div class="cancel-card text-center success-anim">';
    echo '<h2 class="cancel-success mb-4">Booking Cancelled</h2>';
    echo '<p>Your booking <b>#'.htmlspecialchars($booking_id).'</b> has been cancelled.</p>';
    echo '<a href="view_bookings.php" class="inline-block mt-4 bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150">Back to Bookings</a>';
    echo '</div>';
    echo '</div>';
    exit();
}
// If accessed directly
header('Location: view_bookings.php');
exit();
?>
</body>
</html>
