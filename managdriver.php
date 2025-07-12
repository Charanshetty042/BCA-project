<?php
// managdriver.php
include 'header.php';
include 'db_connect.php';
session_start();
// You may want to add admin authentication here

// Handle driver removal
$removal_success = false;
$removal_error = false;
$removal_error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_driver_id'])) {
    $remove_id = (int)$_POST['remove_driver_id'];
    if ($remove_id > 0) {
        // Remove from drivers table
        $stmt = $conn->prepare("DELETE FROM drivers WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $remove_id);
            $stmt->execute();
            $stmt->close();
        }
        // Remove from bookings table
        $stmt2 = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
        if ($stmt2) {
            $stmt2->bind_param("i", $remove_id);
            $stmt2->execute();
            $stmt2->close();
        }
        // Remove from users table
        $stmt3 = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt3) {
            $stmt3->bind_param("i", $remove_id);
            if ($stmt3->execute()) {
                $removal_success = true;
                header("Location: managdriver.php?removed=1");
                exit();
            } else {
                $removal_error = true;
                $removal_error_msg = 'MySQL error: ' . $stmt3->error;
            }
            $stmt3->close();
        } else {
            $removal_error = true;
            $removal_error_msg = 'Prepare failed: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers</title>
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
    <h2 class="mb-4 text-center">Manage Drivers</h2>
    <?php if (isset($_GET['removed']) && $_GET['removed'] == 1): ?>
        <div class="alert alert-success text-center">Driver removed successfully.</div>
    <?php elseif ($removal_error): ?>
        <div class="alert alert-danger text-center">Error: Could not remove driver. <?php echo htmlspecialchars($removal_error_msg); ?></div>
    <?php endif; ?>
    <div class="row mt-5">
        <h3 class="text-center mb-4">All Drivers</h3>
        <?php
        // Fetch drivers from users table where user_type = 'driver'
        $drivers = $conn->query("SELECT user_id, name, email, phone FROM users WHERE user_type = 'driver'");
        if ($drivers && $drivers->num_rows > 0) {
            while ($d = $drivers->fetch_assoc()) {
                $did = isset($d['user_id']) ? (int)$d['user_id'] : 0;
                $dname = (!empty($d['name'])) ? $d['name'] : 'Unknown';
                $demail = isset($d['email']) ? $d['email'] : '';
                $dphone = isset($d['phone']) ? $d['phone'] : '';
                // Fetch vehicle info from drivers table
                $vehicle = $conn->prepare("SELECT vehicle_reg_no, vehicle_name, license_no FROM drivers WHERE user_id = ? LIMIT 1");
                $vehicle_reg_no = $vehicle_name = $license_no = '';
                if ($vehicle) {
                    $vehicle->bind_param("i", $did);
                    $vehicle->execute();
                    $vehicle->bind_result($vehicle_reg_no, $vehicle_name, $license_no);
                    if ($vehicle->fetch()) {
                        // values are set
                    }
                    $vehicle->close();
                }
                echo '<div class="col-md-3 mb-4">';
                echo '<div class="card user-card h-100 p-3 text-center" style="cursor:pointer;">';
                echo '<h5>'.htmlspecialchars($dname).'</h5>';
                echo '<div class="text-muted">Email: '.htmlspecialchars($demail).'</div>';
                echo '<div class="text-muted">Phone: '.htmlspecialchars($dphone).'</div>';
                echo '<div class="text-muted">Vehicle Reg No: '.htmlspecialchars($vehicle_reg_no).'</div>';
                echo '<div class="text-muted">Vehicle Name: '.htmlspecialchars($vehicle_name).'</div>';
                echo '<div class="text-muted">License No: '.htmlspecialchars($license_no).'</div>';
                echo '<form method="POST" action="managdriver.php" onsubmit="return confirm(\'Are you sure you want to remove this driver?\');">';
                echo '<input type="hidden" name="remove_driver_id" value="'.htmlspecialchars($did).'">';
                echo '<button type="submit" class="btn btn-danger btn-sm mt-2">Remove</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12 text-center">No drivers found.</div>';
        }
        ?>
    </div>
    <div class="text-center mt-5">
        <a href="admindashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
