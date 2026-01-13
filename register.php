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
    <link rel="icon" type="image/png" href="wiselogo.png">
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

        <!-- LEFT SIDE: FORM -->
        <div style="
        width:60%;
        padding:28px;
        display:flex;
        flex-direction:column;
        align-items:center;
        text-align:center;
    ">

            <h3 style="margin:0; color:#2b3a67;">Welcome to</h3>
            <h2 style="
            margin:6px 0 18px;
            font-size:32px;
            color:#3559c7;
            font-weight:800;
        ">₱ennyWise</h2>

            <?php if ($error): ?>
                <p style="
                color:#842029;
                background:#f8d7da;
                padding:8px;
                border-radius:6px;
                margin-bottom:12px;
                font-size:13px;
                width:100%;
            "><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" style="width:100%;">
                <input type="text" name="username" placeholder="Choose a Username" required
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
                    margin-bottom:14px;
                    border-radius:8px;
                    border:1px solid #d0dbff;
                    background:#eef3ff;
                    font-size:14px;
                    outline:none;
                ">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required
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
                    /* Register button hover (green) */
                    .register-btn {
                        width: 100%;
                        padding: 12px;
                        background: #8fa8ff;
                        /* same base color as login */
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 13px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: background 0.3s ease;
                    }

                    .register-btn:hover {
                        background: #28a745;
                        /* green on hover */
                    }
                </style>

                <button type="submit" name="register" class="register-btn">
                    Register
                </button>

            </form>

            <p style="
            margin-top:16px;
            font-size:13px;
            color:#4a5d9d;
            text-align:center;
        ">
                Already have an account?<br>
                <a href="login.php" style="
                color:#0F2854;
                font-weight:700;
                text-decoration:none;
            ">Login here</a>
            </p>

        </div>

        <!-- RIGHT SIDE: IMAGE -->
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