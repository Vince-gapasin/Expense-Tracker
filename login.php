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
            $_SESSION['role'] = trim($row['role']);
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
    while ($conn->more_results()) {
        $conn->next_result();
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login - PennyWise</title>
    <link rel="icon" type="image/png" href="wiselogo.png">
    <link rel="stylesheet" href="style.css">
</head>

<body style="
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#eaf2ff,#cfdcff);
    font-family:Verdana, Geneva, Tahoma, sans-serif;
">

    <div style="
    width:520px;
    background:#f7faff;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(143,168,255,0.35);
    display:flex;
    overflow:hidden;
">

        <!-- LEFT SIDE -->
        <div style="
        width:60%;
        padding:28px;
        display:flex;
        flex-direction:column;
        align-items:center;
        text-align:center;
    ">

            <?php if (isset($success_msg)): ?>
                <p style="
                color:#1f3c88;
                background:#eaf2ff;
                padding:8px;
                border-radius:6px;
                margin-bottom:12px;
                font-size:13px;
                width:100%;
            ">
                    <?php echo $success_msg; ?>
                </p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p style="
                color:#842029;
                background:#f8d7da;
                padding:8px;
                border-radius:6px;
                margin-bottom:12px;
                font-size:13px;
                width:100%;
            ">
                    <?php echo $error; ?>
                </p>
            <?php endif; ?>

            <!-- CENTERED TITLE -->
            <h3 style="margin:0; color:#2b3a67;">Welcome to</h3>
            <h2 style="
            margin:6px 0 18px;
            font-size:32px;
            color:#3559c7;
            font-weight:800;
        ">₱ennyWise</h2>

            <!-- FORM -->
            <form method="POST" style="width:100%;">
                <input type="text" name="username" placeholder="Username" required
                    style="
                    width:90%;
                    padding:12px;
                    margin-bottom:14px;
                    border-radius:8px;
                    border:1px solid #d0dbff;
                    background:#eef3ff;
                    font-size:14px;
                    outline:none;
                ">

                <input type="password" name="password" placeholder="Password" required
                    style="
                    width:90%;
                    padding:12px;
                    margin-bottom:18px;
                    border-radius:8px;
                    border:1px solid #d0dbff;
                    background:#eef3ff;
                    font-size:14px;
                    outline:none;
                ">

                <style>
                    .login-btn {
                        width: 100%;
                        padding: 12px;
                        background: #8fa8ff;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 13px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: background 0.3s ease;
                    }

                    .login-btn:hover {
                        background: #6f89ff;
                        /* darker shade on hover */
                    }
                </style>

                <button type="submit" name="login" class="login-btn">
                    Login
                </button>

            </form>

            <!-- CENTERED NEW HERE -->
            <p style="
            margin-top:16px;
            font-size:13px;
            color:#4a5d9d;
            text-align:center;
        ">
                New here?<br>
                <a href="register.php" style="
                color:#6f89ff;
                font-weight:700;
                text-decoration:none;
            ">
                    Create an Account
                </a>
            </p>
        </div>

        <!-- RIGHT SIDE IMAGE -->
        <div style="
        width:40%;
        background:linear-gradient(135deg,#eaf2ff,#dbe4ff);
        display:flex;
        justify-content:center;
        align-items:center;
    ">
            <img src="wiselogo.png" alt="PennyWise Logo"
                style="max-width:180px; height:auto;">
        </div>




    </div>

</body>

</html>