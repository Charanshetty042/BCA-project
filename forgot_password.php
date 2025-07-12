<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="forgot_password.css"> <!-- Optional: keep your custom CSS -->
</head>
<body class="bg-light" style="min-height: 100vh; background: linear-gradient(135deg, #36a2ff 0%, #4fc3f7 100%);">
    <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow p-5" style="max-width: 500px; width: 100%; font-size: 1.15rem;">
            <h1 class="text-center mb-4 text-primary" style="font-size: 2.2rem;"><i class="bi bi-key"></i> Forgot Password</h1>
            <form action="send_reset_link.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Enter your email:</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-envelope"></i> Send Reset Link
                </button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php" class="btn btn-link">Back to Login</a>
            </div>
        </div>
    </div>
    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>