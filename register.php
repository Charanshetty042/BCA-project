<?php
include 'db_connect.php';

$success = "";
$error = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $passwordRaw = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Password validation
    if (
        strlen($passwordRaw) < 8 ||
        !preg_match('/[A-Z]/', $passwordRaw) ||
        !preg_match('/[a-z]/', $passwordRaw) ||
        !preg_match('/[0-9]/', $passwordRaw) ||
        !preg_match('/[\W_]/', $passwordRaw)
    ) {
        $errors[] = "Password must be at least 8 characters, include an uppercase letter, a lowercase letter, a number, and a special character.";
    }

    // Validate fields
    if (empty($name) || empty($email) || empty($phone) || empty($passwordRaw) || empty($user_type)) {
        $errors[] = "❌ All fields are required.";
    }

    if (empty($errors)) {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $password, $user_type);

            if ($stmt->execute()) {
                $success = "✅ Registration successful. <a href='login.php'>Login here</a>";
            } else {
                $errors[] = "❌ Error: " . $stmt->error;
            }

            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "❌ Exception: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-image: url('https://images.unsplash.com/photo-1683720287176-f4d066378b31?q=80&w=1887&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); background-size: cover; background-position: center; background-repeat: no-repeat; min-height: 100vh;">

<div class="w-full max-w-md mx-auto py-12">
    <form action="register.php" method="POST" class="bg-white/70 backdrop-blur-lg shadow-2xl rounded-2xl px-10 py-10">
        <div class="flex flex-col items-center mb-8">
            <img src="./images/logo.png" alt="Company Logo" class="h-20 mb-3 drop-shadow-lg rounded-full border-4 border-blue-200 bg-white p-2">
            <h2 class="text-3xl font-extrabold text-blue-800 tracking-tight mb-1">Create Account</h2>
            <p class="text-blue-600 text-sm">Register to get started</p>
        </div>
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-center animate-pulse">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-center animate-pulse">
                <?php foreach ($errors as $err) echo "<div>$err</div>"; ?>
            </div>
        <?php endif; ?>
        <div class="mb-4">
            <input type="text" class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" name="name" placeholder="Full Name" required>
        </div>
        <div class="mb-4">
            <input type="email" class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" name="email" placeholder="Email (must be unique)" required>
        </div>
        <div class="mb-4">
            <input type="text" class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" name="phone" placeholder="Phone Number" required>
        </div>
        <div id="passwordError" style="display:none;" class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-center animate-pulse"></div>
        <div class="mb-4">
            <input type="password" id="password" class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 placeholder-blue-400 text-blue-900 shadow-sm transition" name="password" placeholder="Password" required>
        </div>
        <div class="mb-6">
            <select name="user_type" class="w-full px-5 py-3 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50 text-blue-900 shadow-sm transition" required>
                <option value="">-- Select Role --</option>
                <option value="customer">Customer</option>
                <option value="driver">Driver</option>
            </select>
        </div>
        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-3 rounded-lg shadow-lg transition mb-2">Register</button>
        <div class="text-center mt-6">
            <a href="login.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">Already registered? Login here</a>
        </div>
    </form>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var password = document.getElementById('password').value;
    var errorDiv = document.getElementById('passwordError');
    var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!regex.test(password)) {
        errorDiv.textContent = 'Password must be at least 8 characters, include an uppercase letter, a lowercase letter, a number, and a special character.';
        errorDiv.style.display = 'block';
        e.preventDefault();
    } else {
        errorDiv.style.display = 'none';
    }
});
</script>

</body>
</html>
