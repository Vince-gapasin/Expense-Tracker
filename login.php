<?php
ob_start();
session_start();
include 'db.php';

$error = ""; 

// Handle "Registration Successful" message
if (isset($_GET['msg']) && $_GET['msg'] == 'registered') {
    $success_msg = "Account created! Please login.";
}

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Call SP to get user
    $stmt = $conn->prepare("CALL sp_get_user(?)");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify Password Hash
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = trim($row['role']); // Trim to ensure no whitespace issues
            $_SESSION['username'] = $row['username'];
            
            ob_end_clean();
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect Password";
        }
    } else {
        $error = "User not found";
    }
    
    $stmt->close();
    while($conn->more_results()){ $conn->next_result(); }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Login - PennyWise</title>
</head>
<body style="flex-direction:column;">
    <div class="card" style="width: 300px; text-align: center;">
        <h2>PennyWise Login</h2>
        
        <?php if(isset($success_msg)): ?>
            <p style='color:#155724; background:#d4edda; padding:8px; border-radius:4px;'>
                <?php echo $success_msg; ?>
            </p>
        <?php endif; ?>

        <?php if($error): ?>
            <p style='color:red; background:#ffe6e6; padding:5px; border-radius:4px;'>
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <p style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
            New here? <br>
            <a href="register.php" style="color: #6f42c1; text-decoration: none; font-weight: bold;">Create an Account</a>
        </p>

        <p><small style="color:#aaa;">Use <b>admin</b> / <b>password</b></small></p>
    </div>
</body>
</html>