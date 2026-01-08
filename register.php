<?php
include 'db.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // 1. Validation
    if ($pass !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        // 2. Check if username exists (Simple check via Select)
        $check = $conn->query("SELECT id FROM users WHERE username = '$user'");
        if ($check->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            // 3. Hash Password & Register
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("CALL sp_register_user(?, ?)");
            $stmt->bind_param("ss", $user, $hashed_pass);
            
            if ($stmt->execute()) {
                // Redirect to login with success message
                header("Location: login.php?msg=registered");
                exit;
            } else {
                $error = "Registration failed. Try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - PennyWise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="flex-direction:column;">

    <div class="card" style="width: 300px; text-align: center;">
        <h2>Create Account 📝</h2>
        
        <?php if($error): ?>
            <p style="color:red; background:#ffe6e6; padding:5px; border-radius:4px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Choose a Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <button type="submit" name="register" style="background-color: #6f42c1;">Register</button>
        </form>

        <p style="margin-top: 15px;">
            Already have an account? <br>
            <a href="login.php" style="color: #28a745; text-decoration: none; font-weight: bold;">Login here</a>
        </p>
    </div>

</body>
</html>