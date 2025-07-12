<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendBookingConfirmationMail($to, $customer_name, $pickup_location, $drop_location, $date, $vehicle_type, $price) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'charanshetty0042@gmail.com'; // SMTP username
        $mail->Password   = 'cxyh xqvk lenn cqll'; // SMTP password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('charanff07@gmail.com', 'Malenadu Transport'); // Use your Gmail as From
        $mail->addAddress($to, $customer_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Vehicle Booking is Confirmed';
        $mail->Body    = "<h2>Booking Placed!</h2>"
            . "<p>Hello <b>$customer_name</b>,<br>Your vehicle booking has been placed successfully.</p>"
            . "<ul>"
            . "<li><b>Pickup:</b> $pickup_location</li>"
            . "<li><b>Drop:</b> $drop_location</li>"
            . "<li><b>Date:</b> $date</li>"
            . "<li><b>Vehicle Type:</b> $vehicle_type</li>"
            . "<li><b>Total Price:</b> ₹" . number_format($price, 2) . "</li>"
            . "</ul>"
            . "<p>Thank you for booking with us!</p>";
        $mail->AltBody = "Hello $customer_name,\n\nYour vehicle booking has been placed successfully.\n\nDetails:\nPickup: $pickup_location\nDrop: $drop_location\nDate: $date\nVehicle Type: $vehicle_type\nTotal Price: ₹" . number_format($price, 2) . "\n\nThank you for booking with us!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo '<div style="color:red;">Mailer Error: ' . $mail->ErrorInfo . '</div>';
        return false;
    }
}

function sendCustomMail($to, $name, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'charanshetty0042@gmail.com';
        $mail->Password = 'cxyh xqvk lenn cqll';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('charanff07@gmail.com', 'Malenadu Transport');
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Optionally log error
        return false;
    }
}
