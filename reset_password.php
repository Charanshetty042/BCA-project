<!-- filepath: c:\wamp64\www\project\reset_password.php -->
<?php
include 'db_connect.php';
if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die("Invalid token.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="reset.css"> <!-- Link to the external CSS file -->
</head>
<body>
    <div class="container">
        <h1>Reset Your Password</h1>
        <form action="update_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>