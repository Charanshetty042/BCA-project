<?php
// Dummy payment handler for bookings
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0) {
    // Get booking price
    $stmt = $conn->prepare("SELECT price FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($amount);
    if ($stmt->fetch()) {
        $stmt->close();
        // Insert or update payment as completed
        $check = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ?");
        $check->bind_param("i", $booking_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            // Update existing payment
            $update = $conn->prepare("UPDATE payments SET status = 'completed' WHERE booking_id = ?");
            $update->bind_param("i", $booking_id);
            $update->execute();
            $update->close();
        } else {
            // Insert new payment
            $insert = $conn->prepare("INSERT INTO payments (booking_id, customer_id, amount, payment_method, status) VALUES (?, ?, ?, 'dummy', 'completed')");
            $insert->bind_param("iid", $booking_id, $user_id, $amount);
            $insert->execute();
            $insert->close();
        }
        $check->close();
        // Optionally update booking status to 'paid' or keep as 'confirmed'
        // $conn->query("UPDATE bookings SET status = 'paid' WHERE booking_id = $booking_id");
        header('Location: payment_success.php?booking_id=' . $booking_id);
        exit();
    } else {
        $stmt->close();
        echo 'Invalid booking.';
        exit();
    }
} else {
    echo 'Invalid request.';
    exit();
}
