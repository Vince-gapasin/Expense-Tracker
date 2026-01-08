<?php
ob_start(); // <--- 1. ADD THIS AT THE VERY TOP
session_start();
include 'db.php';

$error = ""; // Initialize variable

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
            $_SESSION['role'] = $row['role']; 
            $_SESSION['username'] = $row['username'];
            
            // Clean buffer and Redirect
            ob_end_clean(); // <--- 2. CLEAN BUFFER
            header("Location: index.php");
            exit(); // <--- 3. STOP SCRIPT
        } else {
            $error = "Incorrect Password";
        }
    } else {
        $error = "User not found";
    }
    
    // Clear results to avoid sync issues if logic continues
    $stmt->close();
    while($conn->more_results()){ $conn->next_result(); }
}
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body style="flex-direction:column;">
    <div class="card" style="width: 300px; text-align: center;">
        <h2>PennyWise Login</h2>
        
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
        <p><small>Use <b>admin</b> / <b>12345</b></small></p>
    </div>
</body>
</html>