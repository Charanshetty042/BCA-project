<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details (optional, for personalization)
$sql = "SELECT name, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name, $email);
$stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="dashboarduser.css"> <!-- Link to the new CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .cancelled-card {
            background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
            border: 2px solid #fecaca;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px 0 rgba(220, 38, 38, 0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .cancelled-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(220, 38, 38, 0.18);
            border-color: #fca5a5;
        }
        .cancelled-card .delete-history-btn {
            background: #fecaca;
            color: #b91c1c;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            margin-top: 1rem;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px 0 rgba(220, 38, 38, 0.08);
        }
        .cancelled-card .delete-history-btn:hover {
            background: #fca5a5;
            color: #991b1b;
        }
        .cancelled-card .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .cancelled-card .card-title {
            font-size: 1.15rem;
            font-weight: bold;
            color: #dc2626;
            letter-spacing: 0.5px;
        }
        .cancelled-card .card-date {
            font-size: 0.95rem;
            color: #991b1b;
            font-weight: 500;
        }
        .cancelled-card .card-detail {
            color: #374151;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        .cancelled-card .card-label {
            color: #b91c1c;
            font-weight: 500;
        }
        .cancelled-card .card-icon {
            color: #dc2626;
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        .cancelled-empty {
            text-align: center;
            color: #b91c1c;
            font-size: 1.2rem;
            margin-top: 2rem;
            font-weight: 500;
            background: #fff1f2;
            border-radius: 1rem;
            padding: 2rem 1rem;
            box-shadow: 0 2px 8px 0 rgba(220, 38, 38, 0.08);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <header class="text-white py-3" style="background-color:#3D8D7A;">
        <div class="container d-flex justify-content-between align-items-center" style="display:flex;justify-content:space-between;align-items:center;max-width:1200px;margin:0 auto;">
            <div class="logo d-flex align-items-center" style="display:flex;align-items:center;">
                <img src="./images/logo.png" alt="Logo" style="height: 50px; margin-right: 10px;">
                <h1 class="h5 mb-0" style="margin:0;">Malenadu Transport</h1>
            </div>
            <nav>
                <ul class="nav" style="display:flex;gap:15px;list-style:none;margin:0;padding:0;">
                    <li class="nav-item"><a href="index.php" class="nav-link text-white" style="color:white;text-decoration:none;">Home</a></li>
                    <li class="nav-item"><a href="book_vehicle.php" class="nav-link text-white" style="color:white;text-decoration:none;">Book Vehicle</a></li>
                    <li class="nav-item"><a href="view_bookings.php" class="nav-link text-white" style="color:white;text-decoration:none;">View Bookings</a></li>
                    <li class="nav-item"><a href="profile.php" class="nav-link text-white" style="color:white;text-decoration:none;">Profile</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link text-white" style="color:white;text-decoration:none;">Logout</a></li>
                    <li class="nav-item d-flex align-items-center">
                        <a href="#" class="nav-link text-white p-0" id="sidebarMenuTrigger" style="color:white;text-decoration:none;cursor:pointer;display:flex;align-items:center;">
                            <span style="display:inline-block;width:22px;height:18px;vertical-align:middle;">
                                <span style="display:block;width:20px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                                <span style="display:block;width:20px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                                <span style="display:block;width:20px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <h2 style="text-align:center;margin-top:32px;">Book Vehicle</h2>
    <p style="text-align:center;">To book vehicle click on 'Book Vehicle'</p>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-10 ms-sm-auto px-md-4" id="dashboard-main-content" style="margin-left:0;">
                <div class="content">
                   
                    </div>
                </div>
            </main>
        </div>
    </div>
    <footer style="background-color: #A3D1C6; color: #333; font-size: 0.9rem; position: fixed; left: 0; bottom: 0; width: 100%; z-index: 100;" class="py-2">
        <div class="container text-center">
            <div class="social-icons" style="margin-bottom:6px;">
                <a href="#" class="text-dark" style="margin:0 4px;"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-dark" style="margin:0 4px;"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-dark" style="margin:0 4px;"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-dark" style="margin:0 4px;"><i class="fab fa-linkedin"></i></a>
            </div>
            <p style="margin-bottom:0;">&copy; <?php echo date('Y'); ?> Malenadu Transport. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Sidebar overlay and sidebar -->
    <div id="customSidebarOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:1049;"></div>
    <aside id="customSidebar" style="display:none;position:fixed;top:0;right:0;width:260px;height:100vh;background:#3D8D7A;z-index:1050;box-shadow:-2px 0 10px rgba(0,0,0,0.08);padding:32px 18px 18px 18px;">
        <button id="closeSidebarBtn" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        <h5 class="mb-4">Menu</h5>
        <ul class="list-unstyled">
            <li class="mb-3"><a href="index.php#contact" class="text-white" style="text-decoration:none;"><i class="bi bi-telephone"></i> Contact Us</a></li>
            <li class="mb-3"><a href="history.php" class="text-white" style="text-decoration:none;"><i class="bi bi-clock-history"></i> History</a></li>
            <li class="mb-3"><a href="trackorder.php" class="text-white" style="text-decoration:none;"><i class="bi bi-card-checklist"></i> Order Status</a></li>
        </ul>
    </aside>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('historyToast');
            document.getElementById('toastMsg').textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2000);
        }

        function showLoading(show) {
            document.getElementById('historyLoading').style.display = show ? 'flex' : 'none';
        }

        function fetchCancelledBookings() {
            showLoading(true);
            fetch('view_bookings.php?ajax=1&status=cancelled')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cancelledBookingsContainer').innerHTML = html;
                    attachDeleteHistoryEvents();
                    // Count cards
                    const count = document.querySelectorAll('#cancelledBookingsContainer .cancelled-card').length;
                    document.getElementById('cancelledCount').textContent = count > 0 ? `(${count})` : '';
                    // Show/hide delete all button based on count
                    document.getElementById('deleteAllHistoryBtnContainer').style.display = count > 0 ? 'flex' : 'none';
                    showLoading(false);
                });
            // Check for accepted bookings as well
            fetch('view_bookings.php?ajax=1&status=accepted')
                .then(response => response.text())
                .then(html => {
                    const acceptedCount = (html.match(/class=["']accepted-card["']/g) || []).length;
                    // If there are accepted bookings, also show the button
                    if (acceptedCount > 0) {
                        document.getElementById('deleteAllHistoryBtnContainer').style.display = 'flex';
                    }
                });
        }

        function attachDeleteHistoryEvents() {
            document.querySelectorAll('.delete-history-btn').forEach(btn => {
                btn.onclick = function() {
                    if (confirm('Delete this cancelled booking from history?')) {
                        showLoading(true);
                        const bookingId = btn.getAttribute('data-booking-id');
                        fetch('view_bookings.php?ajax=1&delete_history=1&booking_id=' + bookingId, { method: 'POST' })
                            .then(() => {
                                showToast('Booking deleted!');
                                // Remove the card from the DOM
                                btn.closest('.cancelled-card').remove();
                                // Optionally update the count
                                const count = document.querySelectorAll('#cancelledBookingsContainer .cancelled-card').length;
                                document.getElementById('cancelledCount').textContent = count > 0 ? `(${count})` : '';
                                showLoading(false);
                            });
                    }
                };
            });
            const deleteAllBtn = document.getElementById('deleteAllHistoryBtn');
            if (deleteAllBtn) {
                deleteAllBtn.onclick = function() {
                    if (confirm('Delete all cancelled bookings from history?')) {
                        showLoading(true);
                        fetch('view_bookings.php?ajax=1&delete_history=1&all=1', { method: 'POST' })
                            .then(() => { showToast('All history deleted!'); fetchCancelledBookings(); });
                    }
                };
            }
        }

        function showHistorySection() {
            // Show history section logic
            document.getElementById('historySection').style.display = 'block';
            // Optionally hide other sections
        }
        function showBookingStatusSection() {
            // Show booking status section logic
            alert('Show booking status section here.');
        }

        // Sidebar open/close logic
        const sidebarTrigger = document.getElementById('sidebarMenuTrigger');
        const sidebar = document.getElementById('customSidebar');
        const overlay = document.getElementById('customSidebarOverlay');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');

        sidebarTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.style.display = 'block';
            overlay.style.display = 'block';
        });
        closeSidebarBtn.addEventListener('click', function() {
            sidebar.style.display = 'none';
            overlay.style.display = 'none';
        });
        overlay.addEventListener('click', function() {
            sidebar.style.display = 'none';
            overlay.style.display = 'none';
        });
        function showOrderStatusSection() {
            alert('Show order status section here.');
        }

        function showHistorySectionFromSidebar() {
            const section = document.getElementById('historySection');
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                fetchCancelledBookings();
            } else {
                section.style.display = 'none';
            }
            // Optionally close sidebar after click
            document.getElementById('customSidebar').style.display = 'none';
            document.getElementById('customSidebarOverlay').style.display = 'none';
        }

        document.getElementById('showCancelledBookingsBtn').addEventListener('click', function() {
            const section = document.getElementById('cancelledBookingsContainer');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
            // Optionally, you can also toggle visibility or fetch data here
        });
    </script>
</body>
</html>
