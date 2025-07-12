<?php
// vehicle_booking_form.php
// Show booking form for a selected vehicle/driver

// Database connection
$conn = new mysqli('localhost', 'root', '', 'goods_vehicle_booking');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$customer_name = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($customer_name);
    $stmt->fetch();
    $stmt->close();
}

$driver_name = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';
$driver = null;
$vehicle_id = null;
if ($driver_name) {
    $stmt = $conn->prepare("SELECT * FROM drivers WHERE name = ?");
    $stmt->bind_param("s", $driver_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();
    $stmt->close();

    // Find the vehicle for this driver and vehicle type
    if ($driver) {
        $stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE driver_id = ? AND vehicle_type = ? LIMIT 1");
        $stmt->bind_param("is", $driver['id'], $driver['vehicle_type']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $vehicle_id = $row['vehicle_id'];
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vehicle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f0f4f8 0%, #e0e7ef 100%);
        }
        .card {
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
            border-radius: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #1e293b;
        }
        .btn-primary, .btn-success {
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);
            border: none;
        }
        .btn-success {
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
            border: none;
        }
        .modal-content {
            border-radius: 1rem;
        }
        .list-group-item {
            background: #f8fafc;
            border: none;
            border-bottom: 1px solid #e5e7eb;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
        #routeMap, #map, #dropMap {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Book Vehicle</h2>
    <?php if ($driver): ?>
        <div class="card mb-4" style="max-width: 500px;">
            <img src="<?php echo !empty($driver['vehicle_photo']) ? $driver['vehicle_photo'] : 'default_vehicle.jpg'; ?>" class="card-img-top" alt="Vehicle Photo" style="height:250px;object-fit:cover;">
            <div class="card-body">
                <h5 class="card-title">
                    <?php echo htmlspecialchars($driver['vehicle_type']); ?>
                    <?php if (isset($driver['capacity'])): ?>
                        <span class="text-muted" style="font-size:1rem;">(<?php echo htmlspecialchars($driver['capacity']); ?> tons)</span>
                    <?php endif; ?>
                </h5>
                <p class="card-text">Driver: <?php echo htmlspecialchars($driver['name']); ?></p>
                <p class="card-text">Per KM Price: <b>₹<?php echo isset($driver['per_km_price']) ? htmlspecialchars($driver['per_km_price']) : 'N/A'; ?></b></p>
            </div>
        </div>
        <form action="process_booking.php" method="POST">
            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id !== null ? htmlspecialchars($vehicle_id) : ''; ?>">
            <input type="hidden" name="driver_name" value="<?php echo htmlspecialchars($driver['name']); ?>">
            <input type="hidden" name="vehicle_type" value="<?php echo htmlspecialchars($driver['vehicle_type']); ?>">
            <input type="hidden" name="vehicle_photo" value="<?php echo htmlspecialchars($driver['vehicle_photo']); ?>">
            <input type="hidden" name="per_km_price" value="<?php echo isset($driver['per_km_price']) ? htmlspecialchars($driver['per_km_price']) : ''; ?>">
            <input type="hidden" id="distance" name="distance" value="">
            <input type="hidden" id="total_price" name="total_price" value="">
            <div class="mb-3">
                <label for="pickup_location" class="form-label">Pickup Location</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="pickup_location" name="pickup_location" required>
                    <button type="button" class="btn btn-outline-secondary" id="currentLocationBtn">Use Current Location</button>
                    <button type="button" class="btn btn-outline-secondary" id="selectOnMapBtn">Select on Map</button>
                </div>
            </div>
            <!-- Map Modal for Pickup -->
            <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Select Pickup Location on Map</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body" style="height:400px;">
                    <div id="map" style="height:100%; width:100%;"></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="mb-3">
                <label for="drop_location" class="form-label">Drop Location</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="drop_location" name="drop_location" required>
                    <button type="button" class="btn btn-outline-secondary" id="currentDropLocationBtn">Use Current Location</button>
                    <button type="button" class="btn btn-outline-secondary" id="selectDropOnMapBtn">Select on Map</button>
                </div>
            </div>
            <!-- Map Modal for Drop -->
            <div class="modal fade" id="dropMapModal" tabindex="-1" aria-labelledby="dropMapModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="dropMapModalLabel">Select Drop Location on Map</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body" style="height:400px;">
                    <div id="dropMap" style="height:100%; width:100%;"></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Time</label>
                <input type="time" class="form-control" id="time" name="time" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone_number" name="phone_number" pattern="[0-9]{10}" maxlength="10" required placeholder="Enter 10-digit phone number">
            </div>
            <div class="mb-3 text-center">
                <button type="button" class="btn btn-success" id="showRouteBtn">Show Route & Distance</button>
            </div>
            <div id="routeMapContainer" class="mb-3" style="display:none;">
                <div id="routeMap" style="height:350px;width:100%;"></div>
                <div class="mt-2"><b>Distance:</b> <span id="routeDistance">-</span></div>
            </div>
            <button type="submit" class="btn btn-primary" id="bookNowBtn">Book Now</button>
            <!-- Order Preview Modal -->
            <div class="modal fade" id="orderPreviewModal" tabindex="-1" aria-labelledby="orderPreviewModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="orderPreviewModalLabel">Confirm Your Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <ul class="list-group">
                      <li class="list-group-item"><b>Pickup Location:</b> <span id="previewPickup"></span></li>
                      <li class="list-group-item"><b>Drop Location:</b> <span id="previewDrop"></span></li>
                      <li class="list-group-item"><b>Date:</b> <span id="previewDate"></span></li>
                      <li class="list-group-item"><b>Time:</b> <span id="previewTime"></span></li>
                      <li class="list-group-item"><b>Phone Number:</b> <span id="previewPhone"></span></li>
                      <li class="list-group-item"><b>Distance:</b> <span id="previewDistance"></span> km</li>
                      <li class="list-group-item"><b>Vehicle Type:</b> <?php echo htmlspecialchars($driver['vehicle_type']); ?><?php if (isset($driver['capacity'])): ?> <span class="text-muted">(<?php echo htmlspecialchars($driver['capacity']); ?> tons)</span><?php endif; ?></li>
                      <li class="list-group-item"><b>Per KM Price:</b> ₹<span id="previewPerKm"></span></li>
                      <li class="list-group-item"><b>Total Price:</b> ₹<span id="previewTotal"></span></li>
                    </ul>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmOrderBtn">Confirm Order</button>
                  </div>
                </div>
              </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-danger">Vehicle/Driver not found.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Geolocation and reverse geocoding
const pickupInput = document.getElementById('pickup_location');
document.getElementById('currentLocationBtn').onclick = function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            // Reverse geocode using Nominatim
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
            const res = await fetch(url);
            const data = await res.json();
            pickupInput.value = data.display_name || `${lat},${lon}`;
        }, function() {
            alert('Unable to fetch your location.');
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
};

const dropInput = document.getElementById('drop_location');
document.getElementById('currentDropLocationBtn').onclick = function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            // Reverse geocode using Nominatim
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
            const res = await fetch(url);
            const data = await res.json();
            dropInput.value = data.display_name || `${lat},${lon}`;
        }, function() {
            alert('Unable to fetch your location.');
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
};

// Map modal logic for pickup
let map, marker;
document.getElementById('selectOnMapBtn').onclick = function() {
    const modal = new bootstrap.Modal(document.getElementById('mapModal'));
    modal.show();
    setTimeout(() => {
        if (!map) {
            map = L.map('map').setView([15.3173, 75.7139], 7); // Center on Karnataka
            L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)',
                maxZoom: 17
            }).addTo(map);
            map.on('click', async function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                // Reverse geocode
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`;
                const res = await fetch(url);
                const data = await res.json();
                pickupInput.value = data.display_name || `${e.latlng.lat},${e.latlng.lng}`;
            });
        } else {
            map.invalidateSize();
        }
    }, 500);
};

// Map modal logic for drop
let dropMap, dropMarker;
document.getElementById('selectDropOnMapBtn').onclick = function() {
    const modal = new bootstrap.Modal(document.getElementById('dropMapModal'));
    modal.show();
    setTimeout(() => {
        if (!dropMap) {
            dropMap = L.map('dropMap').setView([15.3173, 75.7139], 7); // Center on Karnataka
            L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)',
                maxZoom: 17
            }).addTo(dropMap);
            dropMap.on('click', async function(e) {
                if (dropMarker) dropMap.removeLayer(dropMarker);
                dropMarker = L.marker(e.latlng).addTo(dropMap);
                // Reverse geocode
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`;
                const res = await fetch(url);
                const data = await res.json();
                dropInput.value = data.display_name || `${e.latlng.lat},${e.latlng.lng}`;
            });
        } else {
            dropMap.invalidateSize();
        }
    }, 500);
};

// Show route and distance logic using OSRM
async function geocode(address) {
    // If address is already in lat,lon format, return as is
    const latlonMatch = address.match(/^(-?\d+\.\d+),\s*(-?\d+\.\d+)$/);
    if (latlonMatch) {
        return [parseFloat(latlonMatch[2]), parseFloat(latlonMatch[1])]; // [lon, lat]
    }
    // Otherwise, geocode using Nominatim
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
    const res = await fetch(url);
    const data = await res.json();
    if (data && data[0]) {
        return [parseFloat(data[0].lon), parseFloat(data[0].lat)];
    }
    throw new Error('Could not geocode address: ' + address);
}

let routeMapInstance, routeLayer;
document.getElementById('showRouteBtn').onclick = async function() {
    const pickupLocation = pickupInput.value;
    const dropLocation = dropInput.value;
    if (!pickupLocation || !dropLocation) {
        alert('Please provide both pickup and drop locations.');
        return;
    }
    const routeMapContainer = document.getElementById('routeMapContainer');
    const routeDistance = document.getElementById('routeDistance');
    routeMapContainer.style.display = 'block';
    try {
        // Geocode both locations
        const [pickupLon, pickupLat] = await geocode(pickupLocation);
        const [dropLon, dropLat] = await geocode(dropLocation);
        // Initialize map if needed
        if (!routeMapInstance) {
            routeMapInstance = L.map('routeMap').setView([(pickupLat + dropLat) / 2, (pickupLon + dropLon) / 2], 10);
            L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)',
                maxZoom: 17
            }).addTo(routeMapInstance);
        } else {
            routeMapInstance.setView([(pickupLat + dropLat) / 2, (pickupLon + dropLon) / 2], 10);
            routeMapInstance.invalidateSize();
            if (routeLayer) routeMapInstance.removeLayer(routeLayer);
        }
        // Get route from OSRM
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${pickupLon},${pickupLat};${dropLon},${dropLat}?overview=full&geometries=geojson`;
        const res = await fetch(osrmUrl);
        const data = await res.json();
        if (!data.routes || !data.routes[0]) throw new Error('No route found');
        const route = data.routes[0];
        // Draw route
        routeLayer = L.geoJSON(route.geometry).addTo(routeMapInstance);
        // Fit map to route
        routeMapInstance.fitBounds(routeLayer.getBounds());
        // Show distance
        const distanceKm = (route.distance / 1000).toFixed(2);
        routeDistance.textContent = distanceKm + ' km';
        document.getElementById('distance').value = distanceKm;
        // Calculate and set total price
        const perKmPrice = parseFloat(document.querySelector('input[name="per_km_price"]').value) || 0;
        const totalPrice = (distanceKm * perKmPrice).toFixed(2);
        document.getElementById('total_price').value = totalPrice;
        document.getElementById('bookNowBtn').disabled = false;
    } catch (e) {
        routeDistance.textContent = '-';
        alert('Error: ' + e.message);
        document.getElementById('bookNowBtn').disabled = true;
    }
};
</script>
</body>
</html>