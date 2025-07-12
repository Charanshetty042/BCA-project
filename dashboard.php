<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1683720287176-f4d066378b31?q=80&w=1887&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">

<div class="w-full max-w-md mx-auto">
    <div class="bg-white/70 backdrop-blur-lg shadow-2xl rounded-2xl px-10 py-10">
        <div class="flex flex-col items-center mb-8">
            <img src="./images/logo.png" alt="Company Logo" class="h-20 mb-3 drop-shadow-lg rounded-full border-4 border-blue-200 bg-white p-2">
            <h2 class="text-3xl font-extrabold text-blue-800 tracking-tight mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
            <p class="text-blue-600 text-sm mb-2">You are logged in as <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_type']); ?></span></p>
        </div>
        <div class="flex flex-col gap-4 mb-6">
            <?php
            if ($_SESSION['user_type'] == 'admin') {
                echo "<a href='admindashboard.php' class='w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition text-center'>Go to Admin Panel</a>";
            } elseif ($_SESSION['user_type'] == 'driver') {
                echo "<a href='driver_dashboard.php' class='w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition text-center'>Go to Driver Panel</a>";
            } else {
                echo "<a href='dashboarduser.php' class='w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition text-center'>Go to Customer Panel</a>";
            }
            ?>
            <a href="logout.php" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg shadow-lg transition text-center">Logout</a>
        </div>
        <div class="mt-8 text-center text-blue-900/80 text-xs">
            &copy; <?php echo date('Y'); ?> Malenadu Transport. All rights reserved.
        </div>
    </div>
</div>

</body>
</html>
