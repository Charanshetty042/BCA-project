<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Goods Vehicle Booking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'poppins', sans-serif;
        }
        
        body {
            background-color:#B3D8A8;
            color: #333;
            line-height: 1.6;
        }
        
        .hero {
            background-image: url('./images/truck1.jpg'); /* Replace with your image path */
            max-width: 100%;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: black;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color:black;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin-bottom: 30px;
            color:black;
        }
        
        header {
            background-color:#3D8D7A; /* Change this to your desired color */
            color: white;
            padding: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="text-white py-3" data-aos="fade-down">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo d-flex align-items-center">
                <img src="./images/logo.png" alt="Logo" style="height: 50px; margin-right: 10px;">
                <h1 class="h5 mb-0">Malenadu Transport</h1>
            </div>
            <nav>
                <ul class="nav">
                    <li class="nav-item"><a href="#home" class="nav-link text-white">Home</a></li>
                    <li class="nav-item"><a href="#features" class="nav-link text-white">Features</a></li>
                    <li class="nav-item"><a href="#about" class="nav-link text-white">About Us</a></li>
                    <li class="nav-item"><a href="#contact" class="nav-link text-white">Contact Us</a></li>
                    <li class="nav-item"><a href="register.php" class="btn btn-primary">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home" data-aos="fade-up">
       <!-- <h1>Efficient Local Goods Transportation</h1>
        <p>Book reliable vehicles for your local goods transportation needs with our easy-to-use platform.</p>-->
        <a href="login.php" class="btn btn-primary" data-aos="zoom-in">Get Started</a>
    </section>

    <div style="background-color:#FFF6DA;padding: 20px; text-align: center;">
        <h2>Welcome Malenadu Transportation</h2>
        <p>Experience the best transportation services with us!</p>
    </div>

    <!-- Features Section -->
    <section class="py-5" id="features" data-aos="fade-up">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Our Services</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Vehicle Booking</h3>
                            <p class="card-text">Book various types of goods vehicles for your transportation needs with just a few clicks.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Real-time Tracking</h3>
                            <p class="card-text">Track your goods in real-time from pickup to delivery with our advanced tracking system.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-left">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Secure Payments</h3>
                            <p class="card-text">Safe and secure payment options with transparent pricing and no hidden charges.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section style="background-color: #FBFFE4; color: #333;" class="py-5" id="about" data-aos="fade-up">
        <div class="container text-center">
            <h2>About Us</h2>
            <p class="mt-3">LocalGoodsTransit is a leading local goods vehicle booking platform dedicated to connecting businesses and individuals with reliable transportation services. Our mission is to simplify the process of goods transportation while ensuring efficiency, safety, and affordability.</p>
            <p>Founded in 2023, we have grown to serve hundreds of customers across the region, building trust through our commitment to excellent service and customer satisfaction.</p>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="py-5" id="contact" data-aos="fade-up">
        <div class="container">
            <h2 class="text-center mb-5">Contact Us</h2>
            <div class="row">
                <div class="col-md-6" data-aos="fade-right">
                    <h3>Get In Touch</h3>
                    <p><i class="fas fa-map-marker-alt"></i>sirsi old bus stand behind petrol bunk</p>
                    <p><i class="fas fa-phone"></i>8073196478</p>
                    <p><i class="fas fa-envelope"></i>MalenaduTransport@gmail.com</p>
                    <p><i class="fas fa-clock"></i> Monday - Friday: 9:00 AM - 6:00 PM</p>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <h3>Send Us a Message</h3>
                    <form action="save_contact.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background-color: #A3D1C6; color: #333;" class="py-4" data-aos="fade-up">
        <div class="container text-center">
            <div class="footer-links">
                <a href="#home" class="text-dark">Home</a>
                <a href="#features" class="text-dark">Services</a>
                <a href="#about" class="text-dark">About Us</a>
                <a href="#contact" class="text-dark">Contact</a>
                <a href="privacy.html" class="text-dark">Privacy Policy</a>
                <a href="terms.html" class="text-dark">Terms of Service</a>
            </div>
            <div class="social-icons">
                <a href="#" class="text-dark"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-dark"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-dark"><i class="fab fa-linkedin"></i></a>
            </div>
            <p>&copy; 2025 LocalGoodsTransit. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000, // Animation duration in milliseconds
            once: false, // Animation will trigger every time you scroll
        });
    </script>
</body>
</html>
