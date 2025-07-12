<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        /* Modern CSS for the page */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4caf50, #81c784);
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            color: #333;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #4caf50;
        }
        p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        a {
            color: #4caf50;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        include 'db_connect.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'];
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Connect to the database
            $conn = new mysqli('localhost', 'root', '', 'goods_vehicle_booking');

            if ($conn->connect_error) {
                die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
            }

            // Validate the token
            $expires = date("U"); // Assign the result of date("U") to a variable
            $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires >= ?");
            $stmt->bind_param("ss", $token, $expires); // Pass the variable instead of the function call
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $email = $row['email'];

                // Update the password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $newPassword, $email);
                $stmt->execute();

                // Delete the token
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();

                echo "<h1>Password Reset Successful</h1>";
                echo "<p class='success'>Your password has been reset successfully.</p>";
                echo "<p><a href='login.php'>Click here to log in</a></p>";
            } else {
                echo "<h1>Password Reset Failed</h1>";
                echo "<p class='error'>Invalid or expired token.</p>";
            }

            $stmt->close();
            $conn->close();
        }
        ?>
    </div>
</body>
</html>