<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, name, password, user_type FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password, $user_type);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['user_type'] = $user_type;

            // Set driver_id in session if user is a driver
            if ($user_type === 'driver') {
                $driverStmt = $conn->prepare("SELECT id FROM drivers WHERE name = ?");
                $driverStmt->bind_param("s", $name);
                $driverStmt->execute();
                $driverStmt->bind_result($driver_id);
                if ($driverStmt->fetch()) {
                    $_SESSION['driver_id'] = $driver_id;
                }
                $driverStmt->close();
            }

            echo "<div class='message success'>Login successful! Redirecting...</div>";
            header("refresh:2;url=dashboard.php");
        } else {
            $error_message = "Incorrect password!";
        }
    } else {
        $error_message = "User not found!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1683720287176-f4d066378b31?q=80&w=1887&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">

<div class="w-full max-w-md mx-auto">
    <form method="post" action="login.php" class="bg-white/70 backdrop-blur-lg shadow-2xl rounded-2xl px-10 py-10">
        <div class="flex flex-col items-center mb-8">
            <img src="./images/logo.png" alt="Company Logo" class="h-20 mb-3 drop-shadow-lg rounded-full border-4 border-blue-200 bg-white p-2">
            <h2 class="text-3xl font-extrabold text-blue-800 tracking-tight mb-1">Welcome Back</h2>
            <p class="text-blue-600 text-sm">Sign in to your account</p>
        </div>
        <div class="mb-5">
            <input type="email" name="email" placeholder="Email" required
                class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" />
        </div>
        <div class="mb-6">
            <input type="password" name="password" placeholder="Password" required
                class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" />
        </div>
        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition mb-2">Login</button>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mt-4 text-center animate-pulse">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col items-center mt-8 space-y-2">
            <a href="forgot_password.php" class="text-blue-700 hover:underline text-sm">Forgot Password?</a>
            <a href="register.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg font-semibold shadow transition">Register Here</a>
        </div>
    </form>
    <div class="mt-8 text-center text-blue-900/80 text-xs">
        &copy; <?php echo date('Y'); ?> Your Company Name. All rights reserved.
    </div>
</div>

</body>
</html>
