<?php
// payment_success.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
// Optionally, you can check if payment is done for this booking_id
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7fafc; }
        .center-card { max-width: 420px; margin: 100px auto; border-radius: 1.5rem; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15); background: #fff; padding: 2.5rem 2rem; text-align: center; }
        .success-anim { display: block; margin: 1.5rem auto 0 auto; }
        @keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 80% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(1); } }
        .success-anim.active { animation: popIn 0.7s cubic-bezier(.68,-0.55,.27,1.55); }
        @keyframes dash { to { stroke-dashoffset: 0; } }
        @keyframes tick { to { stroke-dashoffset: 0; } }
    </style>
</head>
<body>
    <div class="center-card">
        <div class="success-anim active" id="successAnim">
            <svg width="90" height="90" viewBox="0 0 90 90">
                <circle cx="45" cy="45" r="40" fill="#e6f9ec"/>
                <circle cx="45" cy="45" r="36" fill="none" stroke="#34a853" stroke-width="5" style="stroke-dasharray:226;stroke-dashoffset:226;animation:dash 0.7s forwards;"/>
                <polyline points="30,48 42,60 62,36" fill="none" stroke="#34a853" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:50;stroke-dashoffset:50;animation:tick 0.4s 0.7s forwards;"/>
            </svg>
            <div style="color:#34a853;font-weight:700;font-size:1.2rem;margin-top:0.7rem;">Payment Successful!</div>
        </div>
        <div class="mt-4" style="color:#888;">Redirecting to your bookings...</div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'view_bookings.php';
        }, 4000);
    </script>
</body>
</html>
