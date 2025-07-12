<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_location = $_POST['pickup_location'];
    $drop_location = $_POST['drop_location'];
    $date = $_POST['date'] . ' ' . $_POST['time'];
    $vehicle_type = $_POST['vehicle_type'];
    $user_id = $_SESSION['user_id'];
    $vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : 0; // Set this properly if available
    $price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
    $per_km_price = isset($_POST['per_km_price']) ? floatval($_POST['per_km_price']) : 0;
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
    $customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, customer_name, phone_number, vehicle_id, pickup_location, drop_location, date, status, price, vehicle_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
    $stmt->bind_param("ississsds", $user_id, $customer_name, $phone_number, $vehicle_id, $pickup_location, $drop_location, $date, $price, $vehicle_type);

    if ($stmt->execute()) {
        // Send confirmation email to customer using PHPMailer
        require_once 'send_mail.php';
        $to = '';
        if (filter_var($phone_number, FILTER_VALIDATE_EMAIL)) {
            $to = $phone_number;
        } elseif (filter_var($customer_name, FILTER_VALIDATE_EMAIL)) {
            $to = $customer_name;
        } elseif (isset($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
            $to = $_SESSION['email'];
        } else {
            // Try to get email from users table
            $user_email = '';
            $user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ? LIMIT 1");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_stmt->bind_result($user_email);
            $user_stmt->fetch();
            $user_stmt->close();
            if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                $to = $user_email;
            }
        }
        if ($to) {
            sendBookingConfirmationMail($to, $customer_name, $pickup_location, $drop_location, $date, $vehicle_type, $price);
        }
        // Send pending order email to driver
        $driver_email = '';
        $driver_stmt = $conn->prepare("SELECT u.email, d.name FROM drivers d JOIN users u ON d.user_id = u.user_id WHERE d.id = (SELECT driver_id FROM vehicles WHERE vehicle_id = ? LIMIT 1) LIMIT 1");
        $driver_stmt->bind_param("i", $vehicle_id);
        $driver_stmt->execute();
        $driver_stmt->bind_result($driver_email, $driver_name);
        $driver_stmt->fetch();
        $driver_stmt->close();
        if (filter_var($driver_email, FILTER_VALIDATE_EMAIL)) {
            $subject = 'New Pending Order Assigned';
            $body = "<h2>New Pending Order</h2>"
                . "<p>Hello <b>$driver_name</b>,<br>You have a new pending order assigned.</p>"
                . "<ul>"
                . "<li><b>Customer:</b> $customer_name</li>"
                . "<li><b>Pickup:</b> $pickup_location</li>"
                . "<li><b>Drop:</b> $drop_location</li>"
                . "<li><b>Date:</b> $date</li>"
                . "<li><b>Vehicle Type:</b> $vehicle_type</li>"
                . "<li><b>Total Price:</b> ₹" . number_format($price, 2) . "</li>"
                . "</ul>"
                . "<p>Please login to your dashboard to accept or reject this order.</p>";
            sendCustomMail($driver_email, $driver_name, $subject, $body);
        }

        echo "<head>
        <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
        <style>
        body { background: linear-gradient(120deg, #f0f4f8 0%, #e0e7ef 100%); }
        .booking-success-card {
          background: #fff;
          border-radius: 1.5rem;
          box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
          padding: 2.5rem 2rem;
          max-width: 420px;
          margin: 3em auto 0 auto;
        }
        .booking-success-card h2 {
          color: #1976d2;
          font-size: 2rem;
          font-weight: 700;
          margin-bottom: 0.5em;
        }
        .booking-success-card .details {
          margin: 1.5em 0 0.5em 0;
          text-align: left;
        }
        .booking-success-card .details span {
          display: block;
          margin-bottom: 0.5em;
          color: #334155;
          font-size: 1.1em;
        }
        </style>
        </head>";
        echo "<div class='booking-success-card text-center'>
        <div id='orderSuccessAnimation' class='mb-4'></div>
        <h2>Booking Successful!</h2>";
        echo "<div class='details'>";
        echo "<span>Distance: <b>" . number_format($distance, 2) . " km</b></span>";
        echo "<span>Per KM Price: <b>₹" . number_format($per_km_price, 2) . "</b></span>";
        echo "<span>Total Price: <b>₹" . number_format($price, 2) . "</b></span>";
        echo "</div>";
        echo "<a href='dashboarduser.php' class='mt-6 inline-block px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition'>Go to Dashboard</a>";
        echo "<script>
        // Animated bouncing truck for order confirmation
        document.getElementById('orderSuccessAnimation').innerHTML = `
        <div style='display:inline-block;position:relative;width:120px;height:80px;'>
          <svg width='120' height='80' viewBox='0 0 120 80'>
            <g>
              <rect x='10' y='40' width='60' height='25' rx='5' fill='#1976d2'/>
              <rect x='70' y='50' width='35' height='15' rx='3' fill='#ffd600'/>
              <circle cx='30' cy='70' r='8' fill='#333'/>
              <circle cx='90' cy='70' r='8' fill='#333'/>
              <circle cx='30' cy='70' r='4' fill='#fff'/>
              <circle cx='90' cy='70' r='4' fill='#fff'/>
            </g>
          </svg>
          <div id='truckBounce' style='position:absolute;top:0;left:0;width:120px;height:80px;'></div>
        </div>
        <div style='font-size:1.3em; color:#1976d2; margin-top:0.5em;'>Order Confirmed!</div>
        <div style='color:#555;'>Thank you for booking with us.</div>
        <style>@keyframes truck-bounce {0%{transform:translateY(0);}20%{transform:translateY(-18px);}40%{transform:translateY(0);}60%{transform:translateY(-10px);}80%{transform:translateY(0);}100%{transform:translateY(0);}}</style>
        `;
        document.querySelector('#orderSuccessAnimation svg').style.animation = 'truck-bounce 1.2s cubic-bezier(.68,-0.55,.27,1.55)';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>