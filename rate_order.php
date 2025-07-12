<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not logged in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $customer_id = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    // Get driver_id from booking
    $driver_id = 0;
    $stmt = $conn->prepare("SELECT v.driver_id FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE b.booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($driver_id);
    $stmt->fetch();
    $stmt->close();

    if ($booking_id > 0 && $customer_id > 0 && $driver_id > 0 && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO ratings (booking_id, customer_id, driver_id, rating, review) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $booking_id, $customer_id, $driver_id, $rating, $review);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
        $stmt->close();
    } else {
        echo 'error';
    }
    exit();
}
echo 'error';
?>
