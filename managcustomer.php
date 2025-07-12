<?php
// managcustomer.php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'header.php';
include 'db_connect.php';
session_start();
// You may want to add admin authentication here

// Handle user removal at the top of the file, after session_start()
$removal_success = false;
$removal_error = false;
$removal_error_msg = '';
$debug_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user_id'])) {
    $remove_id = (int)$_POST['remove_user_id'];
    $debug_msg .= 'Attempting to remove user with ID: ' . $remove_id . '<br>';
    if ($remove_id > 0) {
        // First, delete related bookings (remove all bookings for this user)
        $del_bookings = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
        if ($del_bookings) {
            $del_bookings->bind_param("i", $remove_id);
            $del_bookings->execute();
            $del_bookings->close();
            $debug_msg .= 'Related bookings deleted.<br>';
        } else {
            $debug_msg .= 'Could not prepare bookings delete: ' . $conn->error . '<br>';
        }
        // Delete from history (user_id) (table does not exist, so this is removed)
        // Delete from history (customer_id) (table does not exist, so this is removed)
        // Delete from driver_order (user_id) (table does not exist, so this is removed)
        // Delete from driver_order (customer_id) (table does not exist, so this is removed)
        // Check if user exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $check->bind_param("i", $remove_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $debug_msg .= 'User exists. Proceeding to delete.<br>';
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $remove_id);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        // Check if user exists after delete attempt
                        $check_after = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
                        $check_after->bind_param("i", $remove_id);
                        $check_after->execute();
                        $check_after->store_result();
                        if ($check_after->num_rows > 0) {
                            $debug_msg .= '<b>After delete: User STILL EXISTS in database!</b><br>';
                        } else {
                            $debug_msg .= '<b>After delete: User is GONE from database!</b><br>';
                        }
                        $check_after->close();
                        // Try to commit in case autocommit is off
                        $conn->commit();
                        $debug_msg .= 'Commit attempted.<br>';
                        $removal_success = true;
                        header("Location: managcustomer.php?removed=1");
                        exit();
                    } else {
                        $removal_error = true;
                        $removal_error_msg = 'No rows deleted. User may not exist. MySQL error: ' . $stmt->error . ' | SQLSTATE: ' . $stmt->sqlstate;
                    }
                } else {
                    $removal_error = true;
                    $removal_error_msg = 'MySQL error: ' . $stmt->error . ' | SQLSTATE: ' . $stmt->sqlstate;
                }
                $stmt->close();
            } else {
                $removal_error = true;
                $removal_error_msg = 'Prepare failed: ' . $conn->error;
            }
        } else {
            $removal_error = true;
            $removal_error_msg = 'User with ID ' . $remove_id . ' does not exist.';
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .dashboard-card { border-radius: 1rem; box-shadow: 0 4px 24px 0 rgba(38,166,154,0.08); transition: box-shadow 0.2s; }
        .dashboard-card:hover { box-shadow: 0 8px 32px 0 rgba(38,166,154,0.18); }
        .user-card:hover { box-shadow: 0 8px 32px 0 rgba(38,166,154,0.18); background: #f1f5f9; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-center">Manage Customers</h2>
    <?php if ($debug_msg) { echo '<div class="alert alert-info">' . $debug_msg . '</div>'; } ?>
    <?php if (isset($_GET['removed']) && $_GET['removed'] == 1): ?>
        <div class="alert alert-success text-center">Customer removed successfully.</div>
    <?php elseif ($removal_error): ?>
        <div class="alert alert-danger text-center">Error: Could not remove customer. <?php echo htmlspecialchars($removal_error_msg); ?></div>
    <?php endif; ?>
    <div class="row mt-5">
        <h3 class="text-center mb-4">All Customers</h3>
        <?php
        $users = $conn->query("SELECT * FROM users WHERE user_type = 'customer'");
        if ($users->num_rows > 0) {
            while ($u = $users->fetch_assoc()) {
                // Use correct key for user ID
                $uid = isset($u['user_id']) ? (int)$u['user_id'] : 0;
                $uname = (!empty($u['name'])) ? $u['name'] : 'Unknown';
                $uemail = isset($u['email']) ? $u['email'] : '';
                echo '<div class="col-md-3 mb-4">';
                echo '<div class="card user-card h-100 p-3 text-center" style="cursor:pointer;">';
                echo '<h5>'.htmlspecialchars($uname).'</h5>';
                echo '<div class="text-muted">Gmail: '.htmlspecialchars($uemail).'</div>';
                $uphone = isset($u['phone']) ? $u['phone'] : '';
                echo '<div class="text-muted">Phone: '.htmlspecialchars($uphone).'</div>';
                echo '<form method="POST" action="managcustomer.php" onsubmit="return confirm(\'Are you sure you want to remove this customer?\');">';
                echo '<input type="hidden" name="remove_user_id" value="'.htmlspecialchars($uid).'">';
                echo '<button type="submit" class="btn btn-danger btn-sm mt-2">Remove</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12 text-center">No customers found.</div>';
        }
        ?>
    </div>
    <div class="text-center mt-5">
        <a href="admindashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
