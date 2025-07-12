<?php
// Start session to access user data
session_start();

// Include database connection
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_query = "UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_password_stmt = $conn->prepare($update_password_query);
            $update_password_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_password_stmt->execute()) {
                $_SESSION['success_message'] = "Password reset successfully!";
                header("Location: profile.php");
                exit();
            } else {
                $error_message = "Failed to reset password. Please try again.";
            }
        } else {
            $error_message = "New password and confirm password do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar with Home Icon -->
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a href="dashboarduser.php" class="navbar-brand">
                <i class="bi bi-house"></i> Home
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p class="text-center">Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p class="text-center">Phone: <?php echo htmlspecialchars($user['phone']); ?></p>

        <!-- Profile Photo Section -->
        <div class="text-center mb-4">
            <img src="<?php echo isset($user['profile_photo']) ? $user['profile_photo'] : 'https://cdn.jsdelivr.net/npm/bootstrap-icons/icons/person-circle.svg'; ?>" class="rounded-circle border" alt="Profile Photo" style="width: 100px; height: 100px; object-fit: cover;">
        </div>

        <h2 class="mt-4">Manage Profile</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <!-- Update Profile Form -->
        <form method="POST" action="profile.php" onsubmit="return confirmUpdate();" class="mb-4">
            <input type="hidden" name="update_profile" value="1">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone:</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Profile
            </button>
        </form>

        <script>
            function confirmUpdate() {
                return confirm("Are you sure you want to update your profile?");
            }
        </script>

        <!-- Reset Password Form -->
        <h2>Reset Password</h2>
        <form method="POST" action="profile.php" class="mb-4">
            <input type="hidden" name="reset_password" value="1">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password:</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-key"></i> Reset Password
            </button>
        </form>
        <div class="mb-4">
            <a href="forgot_password.php" class="btn btn-success">
                <i class="bi bi-key"></i> Forgot Password?
            </a>
        </div>

        <h2>Your Booking History</h2>
        <?php
        // Fetch booking history
        $query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $bookings_result = $stmt->get_result();
        ?>
        <?php if ($bookings_result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle Type</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['vehicle_type']); ?></td>
                            <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['drop_location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['date']); ?></td> <!-- Updated column name -->
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No bookings found.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt-4">
            <i class="bi bi-house"></i> Home
        </a>

        <a href="logout.php" class="btn btn-danger mt-4">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>