<?php
// update_status.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $allowed_statuses = ['pending','confirmed','on the way','reached','pickedup','delivered','completed','cancelled'];
    if ($booking_id > 0 && in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $status, $booking_id);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $success = false;
        }
        $stmt->close();
        // Send email to customer if status is completed
        if ($success && $status === 'completed') {
            if (!function_exists('sendCustomMail')) {
                include_once 'send_mail.php';
            }
            // Get customer email and name for this booking
            $infoStmt = $conn->prepare("SELECT u.email, u.name, b.pickup_location, b.drop_location, b.date, b.vehicle_type, b.price FROM bookings b JOIN users u ON b.user_id = u.user_id WHERE b.booking_id = ? LIMIT 1");
            $infoStmt->bind_param("i", $booking_id);
            $infoStmt->execute();
            $infoResult = $infoStmt->get_result();
            if ($info = $infoResult->fetch_assoc()) {
                $to = $info['email'];
                $customer_name = $info['name'];
                $pickup_location = $info['pickup_location'];
                $drop_location = $info['drop_location'];
                $date = $info['date'];
                $vehicle_type = $info['vehicle_type'];
                $price = $info['price'];
                $subject = 'Your Order is Completed!';
                $body = "<h2>Order Completed!</h2>"
                    . "<p>Hello <b>$customer_name</b>,<br>Your order has been marked as completed by the driver.</p>"
                    . "<ul>"
                    . "<li><b>Pickup:</b> $pickup_location</li>"
                    . "<li><b>Drop:</b> $drop_location</li>"
                    . "<li><b>Date:</b> $date</li>"
                    . "<li><b>Vehicle Type:</b> $vehicle_type</li>"
                    . "<li><b>Total Price:</b> ₹" . number_format($price, 2) . "</li>"
                    . "</ul>"
                    . "<p>Thank you for choosing us! Stay in contact for future bookings.</p>";
                $mailSent = sendCustomMail($to, $customer_name, $subject, $body);
                if (!$mailSent) {
                    error_log('Order completion mail to customer failed for booking_id: ' . $booking_id . ' (email: ' . $to . ')');
                    echo '<div style="color:red;">Failed to send completion email to customer.</div>';
                }
            }
            $infoStmt->close();
        }
        // Send email to driver if status is completed
        if ($success && $status === 'completed') {
            if (!function_exists('sendCustomMail')) {
                include_once 'send_mail.php';
            }
            // Get driver email and name for this booking
            $driverStmt = $conn->prepare("SELECT u.email, u.name, b.pickup_location, b.drop_location, b.date, b.vehicle_type, b.price FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id JOIN drivers d ON v.driver_id = d.id JOIN users u ON d.user_id = u.user_id WHERE b.booking_id = ? LIMIT 1");
            $driverStmt->bind_param("i", $booking_id);
            $driverStmt->execute();
            $driverResult = $driverStmt->get_result();
            if ($driverInfo = $driverResult->fetch_assoc()) {
                $to = $driverInfo['email'];
                $driver_name = $driverInfo['name'];
                $pickup_location = $driverInfo['pickup_location'];
                $drop_location = $driverInfo['drop_location'];
                $date = $driverInfo['date'];
                $vehicle_type = $driverInfo['vehicle_type'];
                $price = $driverInfo['price'];
                $subject = 'Order Completed Notification';
                $body = "<h2>Order Completed!</h2>"
                    . "<p>Hello <b>$driver_name</b>,<br>You have successfully completed an order.</p>"
                    . "<ul>"
                    . "<li><b>Pickup:</b> $pickup_location</li>"
                    . "<li><b>Drop:</b> $drop_location</li>"
                    . "<li><b>Date:</b> $date</li>"
                    . "<li><b>Vehicle Type:</b> $vehicle_type</li>"
                    . "<li><b>Total Price:</b> ₹" . number_format($price, 2) . "</li>"
                    . "</ul>"
                    . "<p>Thank you for your service! Stay connected for more orders.</p>";
                $mailSent = sendCustomMail($to, $driver_name, $subject, $body);
                if (!$mailSent) {
                    error_log('Order completion mail to driver failed for booking_id: ' . $booking_id . ' (email: ' . $to . ')');
                    echo '<div style="color:red;">Failed to send completion email to driver.</div>';
                }
            }
            $driverStmt->close();
        }
    } else {
        $success = false;
    }
    // Show animation and redirect
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Status Update</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<style>
    body { background: #f7fafc; display: flex; align-items: center; justify-content: center; height: 100vh; }
    .gpay-anim { width: 120px; height: 120px; margin: 0 auto 1.5rem auto; display: flex; align-items: center; justify-content: center; }
    .circle { stroke-dasharray: 314; stroke-dashoffset: 314; animation: dash 1s ease forwards; }
    .tick { stroke-dasharray: 50; stroke-dashoffset: 50; animation: tick 0.5s 1s ease forwards; }
    .cross { stroke-dasharray: 60; stroke-dashoffset: 60; animation: cross 0.5s 1s ease forwards; }
    @keyframes dash { to { stroke-dashoffset: 0; } }
    @keyframes tick { to { stroke-dashoffset: 0; } }
    @keyframes cross { to { stroke-dashoffset: 0; } }
    .msg { font-size: 1.3rem; font-weight: 600; text-align: center; margin-bottom: 0.5rem; }
    </style></head><body>';
    echo '<div>';
    if ($success) {
        echo '<div class="gpay-anim">
        <svg width="100" height="100">
            <circle cx="50" cy="50" r="50" fill="#e6f9ec"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#34a853" stroke-width="6" class="circle"/>
            <polyline points="35,55 48,68 68,38" fill="none" stroke="#34a853" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" class="tick"/>
        </svg>
        </div>';
        echo '<div class="msg text-success">Status updated successfully!</div>';
    } else {
        echo '<div class="gpay-anim">
        <svg width="100" height="100">
            <circle cx="50" cy="50" r="50" fill="#fff0f0"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#ea4335" stroke-width="6" class="circle"/>
            <line x1="38" y1="38" x2="62" y2="62" stroke="#ea4335" stroke-width="6" stroke-linecap="round" class="cross"/>
            <line x1="62" y1="38" x2="38" y2="62" stroke="#ea4335" stroke-width="6" stroke-linecap="round" class="cross"/>
        </svg>
        </div>';
        echo '<div class="msg text-danger">Failed to update status.</div>';
    }
    echo '<div class="text-center text-muted">Redirecting...</div>';
    echo '</div>';
    echo '<script>setTimeout(function(){ window.location.href = "order_status.php"; }, 3000);</script>';
    echo '</body></html>';
    exit();
} else {
    echo 'Invalid request.';
}
?>
