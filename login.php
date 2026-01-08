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
    <link rel="stylesheet" href="style.css">
</head>
<body style="flex-direction:column;">
    <div class="card" style="width: 300px; text-align: center;">
        
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

        <div class="container"> 
            <div class="content">
                <div class="text1">
                    <h3>Welcome to<h3>
                    <h2>₱ennyWise </h2>

                    <p>Tagline Here</p>
                
                </div>
            </div>
            
            <div class="logreg-box">
                <div class="form-box" id="login">
                     <form method="POST">
                        <h3>LOGIN</h3>
                            <input type="text" name="username" placeholder="Username" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <button type="submit" name="login">Login</button>
                     </form>

                <p style="margin-top: 20px; border-top: 2px solid #eee; padding-top: 15px;">
                    New here? <br>
                    <a href="register.php" style="color: #6f42c1;  font-weight: bold;">Create an Account</a>
                </p>
                </div>
            </div>
        </div>


     

    </div>

</body>
</html>