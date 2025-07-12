<?php
// filepath: c:\wamp64\www\project\book_vehicle.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vehicle</title>
    <!-- Add this in the <head> section of your HTML -->
    <link rel="stylesheet" href="book_vehicle.css">
    <!-- Bootstrap CSS for proper header layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column;">
<?php include 'header.php'; ?>
<div style="flex: 1 0 auto;">
<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'goods_vehicle_booking');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Taluk list (same as driver_profile.php)
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

$selected_taluk = isset($_GET['taluk']) ? $_GET['taluk'] : '';

// Pagination setup
$limit = 6; // Number of cards per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination (do this first)
$where = '';
if (!empty($selected_taluk)) {
    $where = "WHERE d.taluk = ? AND v.availability = 'available'";
    $count_sql = "SELECT COUNT(*) AS total FROM drivers d JOIN vehicles v ON d.id = v.driver_id $where";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('s', $selected_taluk);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_row = $count_result->fetch_assoc();
    $total_records = $total_row['total'];
    $count_stmt->close();
    $sql = "SELECT d.name, d.vehicle_type, d.vehicle_photo, d.vehicle_name, d.per_km_price FROM drivers d JOIN vehicles v ON d.id = v.driver_id $where LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $selected_taluk);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $where = "WHERE v.availability = 'available'";
    $total_sql = "SELECT COUNT(*) AS total FROM drivers d JOIN vehicles v ON d.id = v.driver_id $where";
    $total_result = $conn->query($total_sql);
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['total'];
    $sql = "SELECT d.name, d.vehicle_type, d.vehicle_photo, d.vehicle_name, d.per_km_price FROM drivers d JOIN vehicles v ON d.id = v.driver_id $where LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}
$total_pages = ceil($total_records / $limit);
?>

<div class="container mt-4" style="max-width: 700px; margin-left: auto; margin-right: auto; margin-top: 10px !important;">
    <form method="get" class="mb-4 d-flex justify-content-center align-items-center" style="gap: 10px; margin-bottom: 10px !important;">
        <div style="position: relative; width: 320px;">
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; pointer-events: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.106a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                </svg>
            </span>
            <select id="taluk" name="taluk" class="form-select" onchange="this.form.submit()" style="padding-left: 40px; border-radius: 24px; min-width: 220px; height: 48px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <option value="">-- Select Taluk --</option>
                <?php foreach ($taluks as $taluk): ?>
                    <option value="<?php echo htmlspecialchars($taluk); ?>" <?php echo ($selected_taluk == $taluk) ? 'selected' : ''; ?>><?php echo htmlspecialchars($taluk); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="container mt-4" style="max-width: 1440px; margin-left: auto; margin-right: auto; padding-left: 16px; padding-right: 16px;">
        <h1 class="text-center mb-4 font-bold text-3xl text-blue-800">Available Vehicles</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 justify-items-center">
            <?php $cardCount = 0; while ($row = $result->fetch_assoc()): ?>
                <?php if ($cardCount >= 6) break; ?>
                <div>
                    <div class="card mb-4 shadow-lg h-100 border-0 bg-gradient-to-br from-blue-50 to-blue-100 rounded-3xl transition-transform duration-200 hover:scale-105">
                        <a href="vehicle_booking_form.php?driver_name=<?php echo urlencode($row['name']); ?>">
                            <img src="<?php echo !empty($row['vehicle_photo']) ? $row['vehicle_photo'] : 'default_vehicle.jpg'; ?>" class="card-img-top rounded-2xl border-2 border-blue-200 shadow" alt="Vehicle Photo" style="height:220px;object-fit:cover;">
                        </a>
                        <div class="card-body text-center">
                            <p class="card-text text-gray-700 mb-1">Vehicle Name: <span class="font-medium text-blue-600"><?php echo htmlspecialchars($row['vehicle_name']); ?></span></p>
                            <p class="card-text text-gray-700 mb-1">Vehicle Type: <span class="font-medium text-blue-600"><?php echo htmlspecialchars($row['vehicle_type']); ?></span></p>
                            <p class="card-text text-gray-700 mb-1">Per KM Price: <span class="font-medium text-green-700">₹<?php echo htmlspecialchars($row['per_km_price']); ?></span></p>
                            <a href="vehicle_booking_form.php?driver_name=<?php echo urlencode($row['name']); ?>" class="btn btn-primary mt-2 w-100">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php $cardCount++; ?>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; align-items: center; margin-top: 32px; margin-bottom: 16px; width: 100%;">
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?taluk=<?php echo urlencode($selected_taluk); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
<?php else: ?>
    <div style="min-height: 200px;"></div>
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;">
        <span style="font-size: 2rem; color: #e53e3e; font-weight: bold; margin-bottom: 8px;">No Vehicles Available</span>
        <span style="font-size: 1.1rem; color: #555;">Sorry, there are no vehicles available<?php echo $selected_taluk ? ' for <b style=\'color:#2563eb;\'>' . htmlspecialchars($selected_taluk) . '</b>.' : '.'; ?></span>
        <span style="font-size: 2.5rem; color: #e53e3e; margin-top: 12px;">&#128663;</span>
        <button onclick="window.history.back();" style="margin-top: 24px; padding: 10px 28px; border-radius: 24px; background: #2563eb; color: #fff; border: none; font-size: 1rem; font-weight: 500; box-shadow: 0 2px 8px rgba(0,0,0,0.07); cursor: pointer; transition: background 0.2s;">&larr; Back</button>
    </div>
<?php endif;

$conn->close();
?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>