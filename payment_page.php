<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if ($booking_id <= 0) {
    echo 'Invalid booking.';
    exit();
}
// Fetch booking details
$stmt = $conn->prepare("SELECT price, pickup_location, drop_location, date FROM bookings WHERE booking_id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$stmt->bind_result($amount, $pickup, $drop, $date);
if (!$stmt->fetch()) {
    echo 'Booking not found.';
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Dummy Razorpay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7fafc; }
        .payment-card { max-width: 420px; margin: 60px auto; border-radius: 1.5rem; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15); background: #fff; padding: 2.5rem 2rem; }
        .razorpay-logo { font-size: 2rem; font-weight: bold; color: #1976d2; letter-spacing: 2px; margin-bottom: 1.5rem; }
        .amount { font-size: 2.2rem; color: #34a853; font-weight: 700; margin-bottom: 1.2rem; }
        .pay-btn { background: #1976d2; color: #fff; font-weight: 600; font-size: 1.2rem; border-radius: 0.5rem; padding: 0.75rem 2rem; margin-top: 1.5rem; }
        .pay-btn:hover { background: #125ea2; }
        .order-summary { text-align: left; margin-bottom: 1.5rem; background: #f1f5f9; border-radius: 0.75rem; padding: 1rem 1.2rem; }
        .order-summary b { color: #1976d2; }
        .payment-methods { margin: 1.5rem 0 0.5rem 0; text-align: left; }
        .payment-methods label { margin-right: 1.5rem; font-weight: 500; }
        .success-anim { display: none; margin: 1.5rem auto 0 auto; }
        .success-anim.active { display: block; animation: popIn 0.7s cubic-bezier(.68,-0.55,.27,1.55); }
        @keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 80% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(1); } }
    </style>
</head>
<body>
    <div class="payment-card text-center">
        <div class="razorpay-logo">Razorpay</div>
        <div class="order-summary mb-3">
            <div><b>Booking ID:</b> <?php echo htmlspecialchars($booking_id); ?></div>
            <div><b>Pickup:</b> <?php echo htmlspecialchars($pickup); ?></div>
            <div><b>Drop:</b> <?php echo htmlspecialchars($drop); ?></div>
            <div><b>Date:</b> <?php echo htmlspecialchars($date); ?></div>
            <div class="amount">₹<?php echo number_format($amount, 2); ?></div>
        </div>
        <form id="paymentForm" action="dummy_payment.php" method="get">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <div class="payment-methods mb-3">
                <label><input type="radio" name="payment_method" value="card" checked> Card</label>
                <label><input type="radio" name="payment_method" value="upi"> UPI</label>
                <label><input type="radio" name="payment_method" value="wallet"> Wallet</label>
            </div>
            <div id="cardFields" class="mb-3">
                <input type="text" name="card_number" class="form-control mb-2" placeholder="Card Number" maxlength="19" required>
                <div class="row g-2">
                    <div class="col"><input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" required></div>
                    <div class="col"><input type="text" name="card_cvv" class="form-control" placeholder="CVV" maxlength="4" required></div>
                </div>
                <input type="text" name="card_name" class="form-control mt-2" placeholder="Name on Card" required>
            </div>
            <div id="upiFields" class="mb-3" style="display:none;">
                <input type="text" name="upi_id" class="form-control" placeholder="Enter UPI ID (e.g. user@bank)" required>
            </div>
            <div id="walletFields" class="mb-3" style="display:none;">
                <input type="text" name="wallet_number" class="form-control mb-2" placeholder="Wallet Number" required>
                <input type="text" name="wallet_provider" class="form-control" placeholder="Wallet Provider (e.g. Paytm, PhonePe)" required>
            </div>
            <button type="button" class="pay-btn" id="payBtn">Pay & Complete</button>
        </form>
        <div class="success-anim" id="successAnim">
            <svg width="90" height="90" viewBox="0 0 90 90">
                <circle cx="45" cy="45" r="40" fill="#e6f9ec"/>
                <circle cx="45" cy="45" r="36" fill="none" stroke="#34a853" stroke-width="5" style="stroke-dasharray:226;stroke-dashoffset:226;animation:dash 0.7s forwards;"/>
                <polyline points="30,48 42,60 62,36" fill="none" stroke="#34a853" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:50;stroke-dashoffset:50;animation:tick 0.4s 0.7s forwards;"/>
            </svg>
            <div style="color:#34a853;font-weight:700;font-size:1.2rem;margin-top:0.7rem;">Payment Successful!</div>
        </div>
        <div class="mt-4">
            <a href="view_bookings.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
    <script>
        document.getElementById('payBtn').onclick = function(e) {
            e.preventDefault();
            var method = document.querySelector('input[name="payment_method"]:checked').value;
            var valid = true;
            if (method === 'card') {
                cardFields.querySelectorAll('input').forEach(function(i) { if (!i.value) valid = false; });
            } else if (method === 'upi') {
                upiFields.querySelectorAll('input').forEach(function(i) { if (!i.value) valid = false; });
            } else if (method === 'wallet') {
                walletFields.querySelectorAll('input').forEach(function(i) { if (!i.value) valid = false; });
            }
            if (!valid) {
                alert('Please fill all required payment details.');
                return;
            }
            // Submit the form to dummy_payment.php to process payment
            document.getElementById('paymentForm').submit();
        };
        // Payment method dynamic fields
        const cardFields = document.getElementById('cardFields');
        const upiFields = document.getElementById('upiFields');
        const walletFields = document.getElementById('walletFields');
        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'card') {
                    cardFields.style.display = '';
                    upiFields.style.display = 'none';
                    walletFields.style.display = 'none';
                    // Set required
                    cardFields.querySelectorAll('input').forEach(i => i.required = true);
                    upiFields.querySelectorAll('input').forEach(i => i.required = false);
                    walletFields.querySelectorAll('input').forEach(i => i.required = false);
                } else if (this.value === 'upi') {
                    cardFields.style.display = 'none';
                    upiFields.style.display = '';
                    walletFields.style.display = 'none';
                    cardFields.querySelectorAll('input').forEach(i => i.required = false);
                    upiFields.querySelectorAll('input').forEach(i => i.required = true);
                    walletFields.querySelectorAll('input').forEach(i => i.required = false);
                } else if (this.value === 'wallet') {
                    cardFields.style.display = 'none';
                    upiFields.style.display = 'none';
                    walletFields.style.display = '';
                    cardFields.querySelectorAll('input').forEach(i => i.required = false);
                    upiFields.querySelectorAll('input').forEach(i => i.required = false);
                    walletFields.querySelectorAll('input').forEach(i => i.required = true);
                }
            });
        });
    </script>
    <style>
        @keyframes dash { to { stroke-dashoffset: 0; } }
        @keyframes tick { to { stroke-dashoffset: 0; } }
    </style>
</body>
</html>
