<?php
ini_set('session.cookie_samesite', 'Lax');
session_start();

// If already logged in, redirect immediately based on role
if (!empty($_SESSION['supplier_logged_in'])) {
    header("Location: ../../Supplier/HTML/supplier_dashboard.php");
    exit();
}
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'ADMIN':
            header("Location: AdminZE.php");
            exit();
        case 'FINANCE':
            header("Location: ../../finance/HTML/finance-dashboard.php");
            exit();
        case 'BUYER':
            header("Location: ../../Buyers/HTML/buyer_dashboard.php");
            exit();
        case 'REQUESTOR':
            header("Location: ../../Requesitor/HTML/requestor-dashboard.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Login — Procurement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../CSS/style.css" />
</head>

<body>

    <div class="container">
        <div class="login-wrapper">

            <div class="logo-container">
                <img src="../Assets/logo.png" alt="Company Logo" />
            </div>

            <div class="login-box">
                <h4>Enter your<br />Company Account</h4>

                <!-- Error Message -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php
                        $err = $_GET['error'];
                        if ($err === 'inactive') {
                            echo 'Your account has been deactivated. Contact the administrator.';
                        } elseif ($err === 'too_many_attempts') {
                            $wait = (int)($_GET['wait'] ?? 5);
                            echo "Too many failed attempts. Please wait $wait minute(s).";
                        } else {
                            echo 'Invalid username or password';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="../PHP/auth.php" method="POST">
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" name="username" placeholder="Username or Email" required />
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="password" placeholder="Password" required />
                    </div>

                    <button type="submit" class="btn login-btn">LOGIN</button>
                </form>

                <div class="links">
                    <a href="../Forgot_Password/forgot-password.html" class="links-btn">Forgot Password</a>
                </div>

            </div>
        </div>

        <div class="right-image">
            <img src="../Assets/Logo2.jpg" alt="Team" />
            <div class="overlay"></div>
        </div>
    </div>

</body>

</html>