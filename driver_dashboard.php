<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// TEMPORARY: Set driver_id for testing if not already set
if (!isset($_SESSION['driver_id']) && isset($_SESSION['user_id'])) {
    // Replace 38 with the actual driver id for the logged-in driver if needed
    $_SESSION['driver_id'] = 38;
}
// dashboard_header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="welcome.css">
    <style>
        :root {
            --uber-black: #000000;
            --uber-white: #ffffff;
            --uber-gray: #f6f6f6;
            --uber-dark-gray: #333333;
            --uber-light-gray: #e0e0e0;
            --uber-green: #1fbad6;
            --uber-blue: #09091a;
            --header-color: #3D8D7A;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Ubuntu', sans-serif;
        }
        
        body {
            background-color: #B3D8A8 !important;
        }
        
        /* Navigation Bar */
        .navbar {
            background-color: var(--header-color);
            color: var(--uber-white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--uber-white);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            color: var(--uber-green);
            margin-right: 0.5rem;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
        }
        
        .nav-links a {
            color: var(--uber-white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            position: relative;
        }
        
        .nav-links a:hover {
            color: inherit;
        }
        
        .nav-links a.active {
            color: var(--uber-green);
        }
        
        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: transparent;
        }
        
        .nav-links i {
            margin-right: 0.5rem;
        }
        
        .hamburger {
            display: none;
            cursor: pointer;
            color: var(--uber-white);
            font-size: 1.5rem;
        }
        
        /* Driver Info Banner */
        .driver-banner {
            background: #FFF6DA;
            color: var(--uber-dark-gray);
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .driver-banner h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .driver-banner p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .benefit-card {
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
            position: relative;
            overflow: hidden;
            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .benefit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        
        .benefit-card > * {
            position: relative;
            z-index: 2;
        }
        
        
        
        .benefit-card i {
            font-size: 2.5rem;
            color: var(--uber-white);
            margin-bottom: 1rem;
        }
        
        .benefit-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.8rem;
            color: white;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
        }
        
        .benefit-card p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        .highlight {
            color: #FFD700;
            font-weight: bold;
        }
        
        /* Specific card backgrounds */
        .earnings-card {
            background-image: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');
        }
        
        .schedule-card {
            background-image: url('https://images.unsplash.com/photo-1501139083538-0139583c060f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');
        }
        
        .safety-card {
            background-image: url('https://images.unsplash.com/photo-1584433144859-1fc3ab64a957?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');
        }
        
        .trips-card {
            background-image: url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80');
        }
        
        .bonus-card {
            background-image: url('https://images.unsplash.com/photo-1740818576518-0c873d356122?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
        }
        
        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background-color: var(--uber-black);
                flex-direction: column;
                align-items: center;
                padding: 2rem 0;
                transition: left 0.3s;
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .nav-links li {
                margin: 1.5rem 0;
            }
            
            .hamburger {
                display: block;
            }
            
            .driver-banner h2 {
                font-size: 1.5rem;
            }
            
            .driver-banner p {
                font-size: 1rem;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
            }
            
            .benefit-card {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="dashboard.php" class="logo">
            <img src="./images/logo.png" alt="Logo" style="height: 50px; margin-right: 10px;">
            <span>Malenadu Transport</span>
        </a>
        <ul class="nav-links">
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="trips_history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'trips_history.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i> History</a></li>
            <li><a href="driver_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'driver_profile.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="driver_order.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'driver_order.php' ? 'active' : ''; ?>"><i class="fas fa-truck"></i> Orders</a></li>
            <li class="d-flex align-items-center" style="margin-left:2rem;">
                <a href="#" class="nav-link text-white p-0" id="sidebarMenuTrigger" style="color:white;text-decoration:none;cursor:pointer;display:flex;align-items:center;">
                    <span style="display:inline-block;width:22px;height:18px;vertical-align:middle;">
                        <span style="display:block;width:22px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                        <span style="display:block;width:22px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                        <span style="display:block;width:22px;height:3px;background:#fff;border-radius:2px;margin:3px auto;"></span>
                    </span>
                </a>
            </li>
            
        </ul>
        
        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

     <div id="customSidebarOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:1049;"></div>
    <aside id="customSidebar" style="display:none;position:fixed;top:0;right:0;width:260px;height:100vh;background:#3D8D7A;z-index:1050;box-shadow:-2px 0 10px rgba(0,0,0,0.08);padding:32px 18px 18px 18px;">
        <button id="closeSidebarBtn" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        <h5 class="mb-4" style="color:#fff; font-family: 'Times New Roman', Times, serif; font-weight:700; letter-spacing:1px;">Menu</h5>
        <?php
        // Revenue calculation
        include_once 'db_connect.php';
        $driver_id = isset($_SESSION['driver_id']) ? (int)$_SESSION['driver_id'] : 0;
        $today = date('Y-m-d');
        $totalRevenue = 0;
        $todayRevenue = 0;
        if ($driver_id) {
            // Total revenue
            $stmt = $conn->prepare("SELECT SUM(price) as total FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'completed'");
            $stmt->bind_param("i", $driver_id);
            $stmt->execute();
            $stmt->bind_result($totalRevenue);
            $stmt->fetch();
            $stmt->close();
            // Today's revenue
            $stmt = $conn->prepare("SELECT SUM(price) as today FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE v.driver_id = ? AND b.status = 'completed' AND DATE(b.date) = ?");
            $stmt->bind_param("is", $driver_id, $today);
            $stmt->execute();
            $stmt->bind_result($todayRevenue);
            $stmt->fetch();
            $stmt->close();
        }
        ?>
        <ul class="list-unstyled">
            <li class="mb-4">
                <a href="driver_revenue.php"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#e0f7fa;color:#388e3c;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-rupee-sign" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">My Revenue</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="order_status.php"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#fff3cd;color:#3D8D7A;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-info-circle" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">Status</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="my_driver_reviews.php"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#fff3cd;color:#3D8D7A;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-star" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">Rating</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="index.php#contact"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#fff3cd;color:#3D8D7A;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-phone" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">Contact Us</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="driver_payments.php"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#fff3cd;color:#3D8D7A;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-credit-card" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">Payments</span>
                </a>
            </li>
            <li>
                <a href="logout.php"
                   class="text-white"
                   style="text-decoration:none;display:flex;align-items:center;font-weight:700;font-size:1.1rem;padding:16px 0 16px 0;font-family:'Times New Roman', Times, serif;letter-spacing:0.5px;">
                    <span style="background:#ffdddd;color:#c82333;border-radius:50%;padding:12px;display:inline-flex;align-items:center;justify-content:center;margin-right:18px;">
                        <i class="fas fa-sign-out-alt" style="font-size:1.3rem;"></i>
                    </span>
                    <span style="color:#fff;">Sign Out</span>
                </a>
            </li>
        </ul>
    </aside>

    <?php
    // Payment details table removed as per request
    ?>
    <!-- Driver Information Banner -->
    <section class="driver-banner">
        <h2>Welcome to Malenadu Transport Driver Portal</h2>
        <p>Join our network of professional drivers and earn up to <span class="highlight">₹30,000 per month</span> by transporting goods across the Malenadu region. Enjoy flexible hours, competitive pay, and a reliable booking system.</p>
        
        <div class="benefits-grid">
            <div class="benefit-card earnings-card">
                <i class="fas fa-rupee-sign"></i>
                <h3>High Earnings</h3>
                <p>Earn up to ₹30,000/month with our competitive payment structure and bonus opportunities.</p>
            </div>
            <div class="benefit-card schedule-card">
                <i class="fas fa-clock"></i>
                <h3>Flexible Schedule</h3>
                <p>Choose your own working hours and accept bookings that fit your availability.</p>
            </div>
            <div class="benefit-card safety-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Secure</h3>
                <p>Verified customers and transparent payment system for your peace of mind.</p>
            </div>
            <div class="benefit-card trips-card">
                <i class="fas fa-road"></i>
                <h3>Regular Trips</h3>
                <p>Consistent demand for goods transport ensures regular income opportunities.</p>
            </div>
            <div class="benefit-card bonus-card">
                <i class="fas fa-gift"></i>
                <h3>Get Bonus</h3>
                <p>Receive special bonuses for top performance and referrals. Drive more, earn more!</p>
            </div>
        </div>
    </section>

    <script>
        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        const sidebarTrigger = document.getElementById('sidebarMenuTrigger');
        const sidebar = document.getElementById('customSidebar');
        const overlay = document.getElementById('customSidebarOverlay');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
        
        // Sidebar open/close logic
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
        // Close sidebar when any sidebar link is clicked
        document.querySelectorAll('#customSidebar a').forEach(function(link) {
            link.addEventListener('click', function() {
                sidebar.style.display = 'none';
                overlay.style.display = 'none';
            });
        });
        // Highlight current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navItems = document.querySelectorAll('.nav-links a');
            navItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                }
            });
        });
    </script>

    <footer style="background: #A3D1C6; color: #000; text-align: center; padding: 1rem 0; width: 100%; z-index: 99; text-decoration: underline;">
        &copy; <?php echo date('Y'); ?> Malenadu Transport. All rights reserved.
    </footer>
</body>
</html>