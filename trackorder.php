<?php
session_start();
include 'db_connect.php';

// Only show orders for the logged-in user
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$query = "SELECT booking_id, vehicle_type, pickup_location, drop_location, date, status FROM bookings WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Status flow for progress bar
$status_flow = ['pending','confirmed','on the way','reached','pickedup','delivered','completed','cancelled'];
$status_labels = [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'on the way' => 'On the Way',
    'reached' => 'Reached',
    'pickedup' => 'Picked Up',
    'delivered' => 'Delivered',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    .order-card { background: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08); padding: 2rem; margin-bottom: 2rem; }
    .amazon-progress { display: flex; align-items: center; justify-content: space-between; margin: 2rem 0 1rem 0; }
    .amazon-step { flex: 1; text-align: center; position: relative; }
    .amazon-step:not(:last-child)::after { content: ''; position: absolute; top: 18px; right: -50%; width: 100%; height: 4px; background: #e0e7ef; z-index: 0; }
    .amazon-step.active:not(:last-child)::after { background: #34a853; }
    .amazon-circle { width: 36px; height: 36px; border-radius: 50%; background: #e0e7ef; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem auto; font-weight: bold; color: #888; position: relative; z-index: 1; }
    .amazon-step.active .amazon-circle { background: #34a853; color: #fff; }
    .amazon-step.cancelled .amazon-circle { background: #ea4335; color: #fff; }
    .amazon-label { font-size: 0.95rem; color: #333; }
    .amazon-step.active .amazon-label { color: #34a853; font-weight: 600; }
    .amazon-step.cancelled .amazon-label { color: #ea4335; font-weight: 600; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">Track Your Orders</h2>
    <div class="mb-4">
        <a href="dashboarduser.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-primary">Order #<?php echo htmlspecialchars($row['booking_id']); ?></span>
                    <span class="text-muted small"><?php echo htmlspecialchars($row['date']); ?></span>
                </div>
                <div class="mb-2"><b>Vehicle:</b> <?php echo htmlspecialchars($row['vehicle_type']); ?></div>
                <div class="mb-2"><b>From:</b> <?php echo htmlspecialchars($row['pickup_location']); ?> <i class="fas fa-arrow-right"></i> <b>To:</b> <?php echo htmlspecialchars($row['drop_location']); ?></div>
                <div class="amazon-progress">
                    <?php
                    $current_status = $row['status'];
                    $cancelled = ($current_status === 'cancelled');
                    $reached_index = array_search($current_status, $status_flow);
                    foreach ($status_flow as $i => $status) {
                        $active = ($i <= $reached_index && !$cancelled);
                        $is_cancel = ($status === 'cancelled' && $cancelled);
                        echo '<div class="amazon-step'.($active ? ' active' : '').($is_cancel ? ' cancelled' : '').'">';
                        echo '<div class="amazon-circle">'.($i+1).'</div>';
                        echo '<div class="amazon-label">'.htmlspecialchars($status_labels[$status]).'</div>';
                        echo '</div>';
                        if ($is_cancel) break;
                    }
                    ?>
                </div>
                <div class="mt-2">
                    <span class="badge <?php
                        if ($current_status === 'completed') echo 'bg-success';
                        elseif ($current_status === 'cancelled') echo 'bg-danger';
                        elseif ($current_status === 'pending') echo 'bg-warning text-dark';
                        else echo 'bg-info text-dark';
                    ?>">Current Status: <?php echo htmlspecialchars(ucwords($current_status)); ?></span>
                </div>
                <?php if ($current_status === 'completed'): ?>
                <div class="mt-3">
                    <form action="rate_order.php" method="POST" class="d-flex flex-column flex-md-row align-items-center gap-2 rating-form" data-booking-id="<?php echo htmlspecialchars($row['booking_id']); ?>">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_id']); ?>">
                        <label class="mb-0"><b>Rate:</b></label>
                        <div class="star-rating d-flex align-items-center" style="font-size:1.5rem; color:#ffc107; cursor:pointer;">
                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                <input type="radio" name="rating" id="star-<?php echo $row['booking_id']; ?>-<?php echo $star; ?>" value="<?php echo $star; ?>" style="display:none;" required>
                                <label for="star-<?php echo $row['booking_id']; ?>-<?php echo $star; ?>" style="margin:0 2px;">
                                    <i class="fa fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <input type="text" name="feedback" class="form-control form-control-sm w-auto" placeholder="Feedback (optional)">
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                    </form>
                    <div class="rating-success text-success fw-bold mt-2" style="display:none;">Thank you for your feedback!</div>
                </div>
                <script>
                document.querySelectorAll('.star-rating').forEach(function(starRating) {
                    const stars = starRating.querySelectorAll('input[type="radio"]');
                    stars.forEach(function(star, idx) {
                        star.addEventListener('change', function() {
                            stars.forEach(function(s, i) {
                                s.nextElementSibling.querySelector('i').classList.toggle('fa-solid', i <= idx);
                                s.nextElementSibling.querySelector('i').classList.toggle('fa-regular', i > idx);
                            });
                        });
                    });
                });
                document.querySelectorAll('.rating-form').forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        var formData = new FormData(form);
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'rate_order.php', true);
                        xhr.onload = function() {
                            if (xhr.status === 200 && xhr.responseText.trim() === 'success') {
                                form.style.display = 'none';
                                form.parentElement.querySelector('.rating-success').style.display = 'block';
                            } else {
                                alert('Failed to submit rating. Please try again.');
                            }
                        };
                        xhr.send(formData);
                    });
                });
                </script>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No orders found.</div>
    <?php endif; ?>
</div>
<!-- FontAwesome for arrow icon -->
<script src="https://kit.fontawesome.com/4b2b2b6a0a.js" crossorigin="anonymous"></script>
</body>
</html>