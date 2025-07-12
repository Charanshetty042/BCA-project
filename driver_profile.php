<?php
session_start();
// Redirect to login if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'driver_dashboard.php';
include 'db_connect.php';

$driverData = [];
$debugMsg = '';
$vehicleAvailability = null;
$vehicleId = null;
// Handle vehicle list/unlist action
if (isset($_POST['toggle_availability']) && isset($_SESSION['user_id'])) {
    // Find driver's vehicle
    $stmt = $conn->prepare("SELECT v.vehicle_id, v.availability FROM vehicles v JOIN drivers d ON v.driver_id = d.id WHERE d.user_id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $vehicleId = $row['vehicle_id'];
        $vehicleAvailability = $row['availability'];
        $newStatus = ($vehicleAvailability === 'available') ? 'maintenance' : 'available';
        $update = $conn->prepare("UPDATE vehicles SET availability = ? WHERE vehicle_id = ?");
        $update->bind_param("si", $newStatus, $vehicleId);
        $update->execute();
        $update->close();
        $successMessage = ($newStatus === 'available') ? 'Vehicle is now listed for booking.' : 'Vehicle is now unlisted from booking.';
        $vehicleAvailability = $newStatus;
    }
    $stmt->close();
}
// Fetch driver profile for this user
$stmt = $conn->prepare("SELECT * FROM drivers WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$driverData = $result->fetch_assoc();
$stmt->close();
// Also fetch vehicle availability for display
if ($driverData && $vehicleAvailability === null) {
    $stmt = $conn->prepare("SELECT vehicle_id, availability FROM vehicles WHERE driver_id = ? AND registration_no = ? LIMIT 1");
    $stmt->bind_param("is", $driverData['id'], $driverData['vehicle_reg_no']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $vehicleId = $row['vehicle_id'];
        $vehicleAvailability = $row['availability'];
    }
    $stmt->close();
}
//if (!$driverData) {
   // $debugMsg .= '<div style="color:red;">No driver profile found for this user.</div>';
//}
?>

<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="driver_profile.css">
</head>

<?php
// Handle form submission (only validate if not toggle_availability)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['toggle_availability'])) {
    $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $licenseNo = isset($_POST['license_no']) ? trim((string)$_POST['license_no']) : '';
    $vehicleRegNo = isset($_POST['vehicle_reg_no']) ? trim((string)$_POST['vehicle_reg_no']) : '';
    $vehicleName = isset($_POST['vehicle_name']) ? trim((string)$_POST['vehicle_name']) : '';
    $vehicleType = isset($_POST['vehicle_type']) ? trim((string)$_POST['vehicle_type']) : '';
    $licenseVerified = isset($_POST['license_verified']) ? 1 : 0;
    $vehicleVerified = isset($_POST['vehicle_verified']) ? 1 : 0;
    $perKmPrice = isset($_POST['per_km_price']) ? floatval($_POST['per_km_price']) : null;
    $taluk = isset($_POST['taluk']) ? trim((string)$_POST['taluk']) : '';
    $capacity = isset($_POST['capacity']) ? floatval($_POST['capacity']) : null;

    // Server-side validation
    $allowedVehicleTypes = ['3 Wheeler', 'Mini Truck', 'Medium Truck', 'Large Truck'];
    $validationErrors = [];
    if (empty($name)) {
        $validationErrors[] = 'Full Name is required.';
    }
    if (!preg_match('/^[A-Za-z]{2}-?\d{10}$/', $licenseNo)) {
        $validationErrors[] = 'Invalid license number format. Example: DL0123456789 or DL-0123456789';
    }
    if (!preg_match('/^[A-Za-z]{2}\d{1,2}[A-Za-z]{1,2}\d{1,4}$/', $vehicleRegNo)) {
        $validationErrors[] = 'Invalid vehicle registration number format. Example: MH01AB1234';
    }
    if (empty($vehicleName)) {
        $validationErrors[] = 'Vehicle Name is required.';
    }
    if (!in_array($vehicleType, $allowedVehicleTypes)) {
        $validationErrors[] = 'Invalid vehicle type selected.';
    }
    if (!is_numeric($perKmPrice) || $perKmPrice < 0) {
        $validationErrors[] = 'Per KM Price must be a positive number.';
    }
    if (empty($taluk)) {
        $validationErrors[] = 'Location (Taluk) is required.';
    }
    if (!is_numeric($capacity) || $capacity <= 0) {
        $validationErrors[] = 'Capacity (in tons) must be a positive number.';
    }

    // Handle file upload (only if no validation errors so far)
    $vehiclePhoto = $driverData['vehicle_photo'] ?? '';
    if (empty($validationErrors) && !empty($_FILES['vehicle_photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileType = $_FILES['vehicle_photo']['type'];
        $fileName = $_FILES['vehicle_photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $validationErrors[] = 'Only JPG, JPEG, PNG, and WEBP files are allowed for vehicle photo.';
        } else {
            $targetDir = "uploads/";
            $vehiclePhoto = $targetDir . uniqid() . "_" . basename($fileName);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            if (!move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $vehiclePhoto)) {
                $validationErrors[] = 'Unable to upload the file. Please check the directory permissions.';
            }
        }
    }

    // Only proceed if no validation errors
    if (empty($validationErrors)) {
        // Only check for duplicates if the license_no or vehicle_reg_no is being changed
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $checkDuplicate = false;
        if (!empty($driverData)) {
            // Editing: check if license_no or vehicle_reg_no changed
            if ($licenseNo !== $driverData['license_no'] || $vehicleRegNo !== $driverData['vehicle_reg_no']) {
                $checkDuplicate = true;
            }
        } else {
            // New registration
            $checkDuplicate = true;
        }
        $hasConflict = false;
        if ($checkDuplicate) {
            $dupStmt = $conn->prepare("SELECT id FROM drivers WHERE (license_no = ? OR vehicle_reg_no = ?) AND user_id != ?");
            $dupStmt->bind_param("ssi", $licenseNo, $vehicleRegNo, $userId);
            $dupStmt->execute();
            $dupResult = $dupStmt->get_result();
            if ($dupResult->num_rows > 0) {
                $conflicts = [];
                while ($row = $dupResult->fetch_assoc()) {
                    $conflicts[] = $row['id'];
                }
                $otherConflicts = array_filter($conflicts, function($id) use ($userId) { return $id != $userId; });
                if (count($otherConflicts) > 0) {
                    $errorMessage = " License number or vehicle registration number already exists " . implode(", ", $otherConflicts);
                    $hasConflict = true;
                }
            }
            $dupStmt->close();
        }
        if (!$hasConflict) {
            if (empty($driverData)) {
                // Insert new record
                $saveStmt = $conn->prepare("INSERT INTO drivers (user_id, name, license_no, vehicle_reg_no, vehicle_name, vehicle_type, vehicle_photo, per_km_price, taluk, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $saveStmt->bind_param("issssssdsd", $userId, $name, $licenseNo, $vehicleRegNo, $vehicleName, $vehicleType, $vehiclePhoto, $perKmPrice, $taluk, $capacity);
                $saveStmt->execute();
                $saveStmt->close();
                // Fetch the newly inserted driver data
                $refreshStmt = $conn->prepare("SELECT * FROM drivers WHERE user_id = ? LIMIT 1");
                $refreshStmt->bind_param("i", $_SESSION['user_id']);
                $refreshStmt->execute();
                $result = $refreshStmt->get_result();
                $driverData = $result->fetch_assoc();
                $refreshStmt->close();
                $successMessage = "Driver profile created successfully!";
            } else {
                // Update existing record
                $saveStmt = $conn->prepare("UPDATE drivers SET name = ?, license_no = ?, vehicle_reg_no = ?, vehicle_name = ?, vehicle_type = ?, vehicle_photo = ?, per_km_price = ?, taluk = ?, capacity = ? WHERE user_id = ?");
                $saveStmt->bind_param("ssssssdsdi", $name, $licenseNo, $vehicleRegNo, $vehicleName, $vehicleType, $vehiclePhoto, $perKmPrice, $taluk, $capacity, $_SESSION['user_id']);
                $saveStmt->execute();
                $saveStmt->close();
                // Refresh data
                $refreshStmt = $conn->prepare("SELECT * FROM drivers WHERE user_id = ? LIMIT 1");
                $refreshStmt->bind_param("i", $_SESSION['user_id']);
                $refreshStmt->execute();
                $result = $refreshStmt->get_result();
                $driverData = $result->fetch_assoc();
                $refreshStmt->close();
                $successMessage = "Driver profile updated successfully!";
            }
            // --- VEHICLE TABLE LOGIC ---
            if (isset($driverData['id'])) {
                // Check for duplicate registration_no in vehicles (not this driver's current vehicle)
                if (isset($driverData['id']) && !empty($vehicleRegNo)) {
                    // Check for duplicate registration_no in vehicles (not this driver's current vehicle)
                    $dupVehicle = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE registration_no = ? AND driver_id != ? LIMIT 1");
                    $dupVehicle->bind_param("si", $vehicleRegNo, $driverData['id']);
                    $dupVehicle->execute();
                    $dupVehicleResult = $dupVehicle->get_result();
                    if ($dupVehicleResult && $dupVehicleResult->num_rows > 0) {
                        $errorMessage = 'Error: Vehicle registration number already exists for another vehicle.';
                        $dupVehicle->close();
                    } else {
                        $dupVehicle->close();
                        // Check if vehicle exists for this driver and registration number
                        $vehicleCheck = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE driver_id = ? AND registration_no = ? LIMIT 1");
                        $vehicleCheck->bind_param("is", $driverData['id'], $vehicleRegNo);
                        $vehicleCheck->execute();
                        $vehicleResult = $vehicleCheck->get_result();
                        if ($vehicleResult->num_rows > 0) {
                            // Update vehicle
                            $vehicleRow = $vehicleResult->fetch_assoc();
                            $vehicleId = $vehicleRow['vehicle_id'];
                            $updateVehicle = $conn->prepare("UPDATE vehicles SET vehicle_type=?, name=?, registration_no=?, price_per_km=?, photo=?, availability='available', driver_name=? WHERE vehicle_id=?");
                            $updateVehicle->bind_param("sssdssi", $vehicleType, $vehicleName, $vehicleRegNo, $perKmPrice, $vehiclePhoto, $name, $vehicleId);
                            $updateVehicle->execute();
                            $updateVehicle->close();
                        } else {
                            // Insert new vehicle
                            $insertVehicle = $conn->prepare("INSERT INTO vehicles (driver_id, driver_name, vehicle_type, registration_no, name, price_per_km, photo, availability) VALUES (?, ?, ?, ?, ?, ?, ?, 'available')");
                            $insertVehicle->bind_param("isssdss", $driverData['id'], $name, $vehicleType, $vehicleRegNo, $vehicleName, $perKmPrice, $vehiclePhoto);
                            $insertVehicle->execute();
                            $insertVehicle->close();
                        }
                        $vehicleCheck->close();
                    }
                }
                // --- END VEHICLE TABLE LOGIC ---
            }
        }
    } else {
        $errorMessage = implode('<br>', $validationErrors);
    }

    // After update/insert and refresh, update $driverData for the form and details
    if (isset($driverData['id'])) {
        // Re-fetch driver data to ensure latest values are shown
        $stmt2 = $conn->prepare("SELECT * FROM drivers WHERE user_id = ? LIMIT 1");
        $stmt2->bind_param("i", $_SESSION['user_id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $driverData = $result2->fetch_assoc();
        $stmt2->close();
    }
}
?>

<div class="container mt-4">
    <?php echo isset($debugMsg) ? $debugMsg : ''; ?>
    <div class="dashboard-header text-center">
        <h1 class="mb-0">Driver Profile</h1>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($driverData['id'])): ?>
        <div id="profileDetails" class="card p-4 shadow-sm mb-4">
            <h4 class="mb-3">Your Profile Details</h4>
            <ul class="list-group mb-3">
                <li class="list-group-item"><b>Full Name:</b> <?php echo htmlspecialchars($driverData['name']); ?></li>
                <li class="list-group-item"><b>License Number:</b> <?php echo htmlspecialchars($driverData['license_no']); ?></li>
                <li class="list-group-item"><b>Vehicle Registration No:</b> <?php echo htmlspecialchars($driverData['vehicle_reg_no']); ?></li>
                <li class="list-group-item"><b>Vehicle Name:</b> <?php echo htmlspecialchars($driverData['vehicle_name']); ?></li>
                <li class="list-group-item"><b>Vehicle Type:</b> <?php echo htmlspecialchars($driverData['vehicle_type']); ?></li>
                <li class="list-group-item"><b>Per KM Price:</b> ₹<?php echo htmlspecialchars($driverData['per_km_price']); ?></li>
                <li class="list-group-item"><b>Capacity (in tons):</b> <?php echo isset($driverData['capacity']) ? htmlspecialchars($driverData['capacity']) : '<span class="text-danger">Not set</span>'; ?></li>
                <li class="list-group-item"><b>Vehicle Photo:</b><br>
                    <?php if (!empty($driverData['vehicle_photo'])): ?>
                        <img src="<?php echo $driverData['vehicle_photo']; ?>" alt="Vehicle Photo" class="img-thumbnail mt-2" style="max-width:200px;">
                    <?php else: ?>
                        <span>No photo uploaded.</span>
                    <?php endif; ?>
                </li>
                <li class="list-group-item"><b>Location (Taluk):</b> <?php echo isset($driverData['taluk']) ? htmlspecialchars($driverData['taluk']) : '<span class="text-danger">Not set</span>'; ?></li>
            </ul>
            <div class="text-center">
                <button id="editProfileBtn" class="btn btn-primary">Edit</button>
            </div>
            <?php if ($vehicleId): ?>
            <div class="text-center mt-3">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="toggle_availability" value="1">
                    <button type="submit" class="btn btn-<?php echo ($vehicleAvailability === 'available') ? 'warning' : 'success'; ?>">
                        <?php echo ($vehicleAvailability === 'available') ? 'Unlist Vehicle' : 'List Vehicle'; ?>
                    </button>
                    <span class="ms-2 fw-bold">
                        Status: <span class="<?php echo ($vehicleAvailability === 'available') ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ucfirst($vehicleAvailability); ?>
                        </span>
                    </span>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <div id="profileFormCard" class="card p-4 shadow-sm mb-4" style="display:none;">
            <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo $driverData['name'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="license_no" class="form-label required-field">License Number</label>
                        <input type="text" class="form-control" id="license_no" name="license_no" required 
                               value="<?php echo $driverData['license_no'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="vehicle_reg_no" class="form-label required-field">Vehicle Registration No</label>
                        <input type="text" class="form-control" id="vehicle_reg_no" name="vehicle_reg_no" required 
                               value="<?php echo $driverData['vehicle_reg_no'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="vehicle_name" class="form-label required-field">Vehicle Name</label>
                        <input type="text" class="form-control" id="vehicle_name" name="vehicle_name" required 
                               value="<?php echo $driverData['vehicle_name'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="vehicle_type" class="form-label required-field">Vehicle Type</label>
                        <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="3 Wheeler" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == '3 Wheeler') ? 'selected' : ''; ?>>3 Wheeler</option>
                            <option value="Mini Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Mini Truck') ? 'selected' : ''; ?>>Mini Truck</option>
                            <option value="Medium Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Medium Truck') ? 'selected' : ''; ?>>Medium Truck</option>
                            <option value="Large Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Large Truck') ? 'selected' : ''; ?>>Large Truck</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="per_km_price" class="form-label required-field">Per KM Price (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="per_km_price" name="per_km_price" required value="<?php echo isset($driverData['per_km_price']) ? htmlspecialchars($driverData['per_km_price']) : ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="capacity" class="form-label required-field">Capacity (in tons)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="capacity" name="capacity" required value="<?php echo isset($driverData['capacity']) ? htmlspecialchars($driverData['capacity']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="taluk" class="form-label required-field">Location (Taluk)</label>
                        <select id="taluk" name="taluk" class="form-select taluk-small" required>
                            <option value="">Select Taluk</option>
                            <?php
                            $taluks = [
                                'Afzalpur', 'Aland', 'Anekal', 'Arsikere', 'Athani', 'Aurad', 'Badami', 'Bagalkot', 'Bailhongal', 'Bangalore North', 'Bangalore South',
                                'Bangarapet', 'Bantwal', 'Basavakalyan', 'Basavana Bagevadi', 'Belgaum', 'Bellary', 'Bhadravati', 'Bhalki', 'Bidar', 'Bijapur', 'Bilgi',
                                'Challakere', 'Chamarajanagar', 'Channagiri', 'Channapatna', 'Chikballapur', 'Chikkanayakanahalli', 'Chikmagalur', 'Chikodi', 'Chincholi',
                                'Chintamani', 'Chitradurga', 'Coondapoor', 'Dandeli', 'Davanagere', 'Devanahalli', 'Dharwad', 'Dod Ballapur', 'Gadag', 'Gauribidanur',
                                'Gokak', 'Gubbi', 'Gulbarga', 'Haliyal', 'Hangal', 'Harihar', 'Hassan', 'Haveri', 'Hiriyur', 'Holalkere', 'Holenarsipur', 'Homnabad',
                                'Honnavar', 'Hosadurga', 'Hosakote', 'Hospet', 'Hubli', 'Hungund', 'Hunsur', 'Ilkal', 'Indi', 'Jagalur', 'Jamkhandi', 'Jewargi',
                                'Kadaba', 'Kadur', 'Kalghatgi', 'Kanakapura', 'Karkal', 'Karwar', 'Kolar', 'Kollegal', 'Koppa', 'Koppal', 'Koratagere', 'Kottur',
                                'Kudligi', 'Kumta', 'Kundapura', 'Kunigal', 'Kushtagi', 'Lingsugur', 'Maddur', 'Magadi', 'Mahalingpur', 'Malavalli', 'Malur', 'Mandya',
                                'Mangalore', 'Manvi', 'Mudalgi', 'Muddebihal', 'Mudhol', 'Mulbagal', 'Mundargi', 'Mysore', 'Nagamangala', 'Nanjangud', 'Nargund',
                                'Navalgund', 'Nelamangala', 'Pandavapura', 'Pavagada', 'Piriyapatna', 'Puttur', 'Raichur', 'Ramanagara', 'Ramdurg', 'Ranebennur',
                                'Raybag', 'Ron', 'Sagar', 'Sakleshpur', 'Sandur', 'Sankeshwar', 'Sargur', 'Saundatti', 'Savanur', 'Sedam', 'Shahapur', 'Shahabad',
                                'Shiggaon', 'Shimoga', 'Shirahatti', 'Shorapur', 'Shrirangapattana', 'Sidlaghatta', 'Sindagi', 'Sindhanur', 'Sirsi', 'Siruguppa',
                                'Sira', 'Sorab', 'Sringeri', 'Srinivaspur', 'Sulya', 'Tarikere', 'Tiptur', 'Tumkur', 'Turuvekere', 'Udupi', 'Virajpet', 'Wadi', 'Yadgir',
                                'Yelandur', 'Yelburga', 'Yellapur', 'Yenagudde'
                            ];
                            foreach ($taluks as $taluk) {
                                $selected = (isset($driverData['taluk']) && $driverData['taluk'] == $taluk) ? 'selected' : '';
                                echo "<option value=\"$taluk\" $selected>$taluk</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_photo" class="form-label">Vehicle Photo</label>
                        <input type="file" class="form-control" id="vehicle_photo" name="vehicle_photo" accept="image/*">
                        <div class="mt-2">
                            <?php if (!empty($driverData['vehicle_photo'])): ?>
                                <img id="preview" class="img-thumbnail rounded" src="<?php echo $driverData['vehicle_photo']; ?>" alt="Vehicle preview">
                            <?php else: ?>
                                <img id="preview" class="img-thumbnail rounded" src="#" alt="Vehicle preview" style="display: none;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                    <button type="button" id="cancelEditBtn" class="btn btn-secondary ms-2">Cancel</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div id="profileFormCard" class="card p-4 shadow-sm">
            <div class="mb-3 text-center text-danger fw-bold">Please complete your profile details to continue.</div>
            <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo $driverData['name'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="license_no" class="form-label required-field">License Number</label>
                        <input type="text" class="form-control" id="license_no" name="license_no" required 
                               value="<?php echo $driverData['license_no'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="vehicle_reg_no" class="form-label required-field">Vehicle Registration No</label>
                        <input type="text" class="form-control" id="vehicle_reg_no" name="vehicle_reg_no" required 
                               value="<?php echo $driverData['vehicle_reg_no'] ?? ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="vehicle_name" class="form-label required-field">Vehicle Name</label>
                        <input type="text" class="form-control" id="vehicle_name" name="vehicle_name" required 
                               value="<?php echo $driverData['vehicle_name'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="vehicle_type" class="form-label required-field">Vehicle Type</label>
                        <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="3 Wheeler" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == '3 Wheeler') ? 'selected' : ''; ?>>3 Wheeler</option>
                            <option value="Mini Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Mini Truck') ? 'selected' : ''; ?>>Mini Truck</option>
                            <option value="Medium Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Medium Truck') ? 'selected' : ''; ?>>Medium Truck</option>
                            <option value="Large Truck" <?php echo (!empty($driverData['vehicle_type']) && $driverData['vehicle_type'] == 'Large Truck') ? 'selected' : ''; ?>>Large Truck</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="per_km_price" class="form-label required-field">Per KM Price (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="per_km_price" name="per_km_price" required value="<?php echo isset($driverData['per_km_price']) ? htmlspecialchars($driverData['per_km_price']) : ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="capacity" class="form-label required-field">Capacity (in tons)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="capacity" name="capacity" required value="<?php echo isset($driverData['capacity']) ? htmlspecialchars($driverData['capacity']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="taluk" class="form-label required-field">Location (Taluk)</label>
                        <select id="taluk" name="taluk" class="form-select taluk-small" required>
                            <option value="">Select Taluk</option>
                            <?php
                            $taluks = [
                                'Afzalpur', 'Aland', 'Anekal', 'Arsikere', 'Athani', 'Aurad', 'Badami', 'Bagalkot', 'Bailhongal', 'Bangalore North', 'Bangalore South',
                                'Bangarapet', 'Bantwal', 'Basavakalyan', 'Basavana Bagevadi', 'Belgaum', 'Bellary', 'Bhadravati', 'Bhalki', 'Bidar', 'Bijapur', 'Bilgi',
                                'Challakere', 'Chamarajanagar', 'Channagiri', 'Channapatna', 'Chikballapur', 'Chikkanayakanahalli', 'Chikmagalur', 'Chikodi', 'Chincholi',
                                'Chintamani', 'Chitradurga', 'Coondapoor', 'Dandeli', 'Davanagere', 'Devanahalli', 'Dharwad', 'Dod Ballapur', 'Gadag', 'Gauribidanur',
                                'Gokak', 'Gubbi', 'Gulbarga', 'Haliyal', 'Hangal', 'Harihar', 'Hassan', 'Haveri', 'Hiriyur', 'Holalkere', 'Holenarsipur', 'Homnabad',
                                'Honnavar', 'Hosadurga', 'Hosakote', 'Hospet', 'Hubli', 'Hungund', 'Hunsur', 'Ilkal', 'Indi', 'Jagalur', 'Jamkhandi', 'Jewargi',
                                'Kadaba', 'Kadur', 'Kalghatgi', 'Kanakapura', 'Karkal', 'Karwar', 'Kolar', 'Kollegal', 'Koppa', 'Koppal', 'Koratagere', 'Kottur',
                                'Kudligi', 'Kumta', 'Kundapura', 'Kunigal', 'Kushtagi', 'Lingsugur', 'Maddur', 'Magadi', 'Mahalingpur', 'Malavalli', 'Malur', 'Mandya',
                                'Mangalore', 'Manvi', 'Mudalgi', 'Muddebihal', 'Mudhol', 'Mulbagal', 'Mundargi', 'Mysore', 'Nagamangala', 'Nanjangud', 'Nargund',
                                'Navalgund', 'Nelamangala', 'Pandavapura', 'Pavagada', 'Piriyapatna', 'Puttur', 'Raichur', 'Ramanagara', 'Ramdurg', 'Ranebennur',
                                'Raybag', 'Ron', 'Sagar', 'Sakleshpur', 'Sandur', 'Sankeshwar', 'Sargur', 'Saundatti', 'Savanur', 'Sedam', 'Shahapur', 'Shahabad',
                                'Shiggaon', 'Shimoga', 'Shirahatti', 'Shorapur', 'Shrirangapattana', 'Sidlaghatta', 'Sindagi', 'Sindhanur', 'Sirsi', 'Siruguppa',
                                'Sira', 'Sorab', 'Sringeri', 'Srinivaspur', 'Sulya', 'Tarikere', 'Tiptur', 'Tumkur', 'Turuvekere', 'Udupi', 'Virajpet', 'Wadi', 'Yadgir',
                                'Yelandur', 'Yelburga', 'Yellapur', 'Yenagudde'
                            ];
                            foreach ($taluks as $taluk) {
                                $selected = (isset($driverData['taluk']) && $driverData['taluk'] == $taluk) ? 'selected' : '';
                                echo "<option value=\"$taluk\" $selected>$taluk</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_photo" class="form-label">Vehicle Photo</label>
                        <input type="file" class="form-control" id="vehicle_photo" name="vehicle_photo" accept="image/*">
                        <div class="mt-2">
                            <?php if (!empty($driverData['vehicle_photo'])): ?>
                                <img id="preview" class="img-thumbnail rounded" src="<?php echo $driverData['vehicle_photo']; ?>" alt="Vehicle preview">
                            <?php else: ?>
                                <img id="preview" class="img-thumbnail rounded" src="#" alt="Vehicle preview" style="display: none;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    // Image preview functionality
    document.getElementById('vehicle_photo').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('preview');
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        }
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const licenseNo = document.getElementById('license_no').value;
        const vehicleRegNo = document.getElementById('vehicle_reg_no').value;
        
        // Basic license number validation (example: DL-0123456789)
        if (!/^[A-Za-z]{2}-?\d{10}$/.test(licenseNo)) {
            alert('Please enter a valid license number (e.g., DL0123456789 or DL-0123456789)');
            e.preventDefault();
            return;
        }
        
        // Basic vehicle registration validation (example: MH01AB1234)
        if (!/^[A-Za-z]{2}\d{1,2}[A-Za-z]{1,2}\d{1,4}$/.test(vehicleRegNo)) {
            alert('Please enter a valid vehicle registration number (e.g., MH01AB1234)');
            e.preventDefault();
            return;
        }
    });

    // Toggle between view and edit
    <?php if (!isset($driverData['id'])): ?>
        // Hide edit/cancel JS for new users
    <?php else: ?>
        const editBtn = document.getElementById('editProfileBtn');
        const cancelBtn = document.getElementById('cancelEditBtn');
        const detailsCard = document.getElementById('profileDetails');
        const formCard = document.getElementById('profileFormCard');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                detailsCard.style.display = 'none';
                formCard.style.display = 'block';
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                formCard.style.display = 'none';
                detailsCard.style.display = 'block';
            });
        }
    <?php endif; ?>
</script>

