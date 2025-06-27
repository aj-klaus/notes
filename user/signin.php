<?php
session_start();
include('includes/dbconnection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
    $redirect = ($_SESSION['role'] === 'admin') ? '/onss/admin/index.php' : '/onss/user/dashboard.php';
    header("Location: " . $redirect);
    exit();
}

if (isset($_POST['login'])) {
    $emailormobnum = $_POST['emailormobnum'];
    $plain_password = $_POST['password'];

    $sql = "SELECT ID, FullName, Email, MobileNumber, Password, Role, Status, failed_attempts, locked_until
            FROM tbluser 
            WHERE (Email=:emailormobnum OR MobileNumber=:emailormobnum)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':emailormobnum', $emailormobnum, PDO::PARAM_STR);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $isLocked = !empty($user['locked_until']) && strtotime($user['locked_until']) > time();
        if ($isLocked) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            $_SESSION['msg'] = "🔒 Account locked due to multiple failed attempts. Try again in $remaining minute(s).";
        } elseif (password_verify($plain_password, $user['Password'])) {
            if ($user['Status'] === 'active') {
                // Reset failed attempts and unlock
                $reset = $dbh->prepare("UPDATE tbluser SET failed_attempts = 0, locked_until = NULL WHERE ID = :id");
                $reset->bindParam(':id', $user['ID']);
                $reset->execute();

                $_SESSION['uid'] = $user['ID'];
                $_SESSION['login'] = $user['Email'] ?? $user['MobileNumber'];
                $_SESSION['fullname'] = $user['FullName'];
                $_SESSION['role'] = $user['Role'];

                $redirect = ($_SESSION['role'] === 'admin') ? '/onss/admin/index.php' : '/onss/user/dashboard.php';
                header("Location: " . $redirect);
                exit();
            } else {
                $_SESSION['msg'] = "⚠️ Your account is inactive. Please contact support.";
            }
        } else {
            $attempts = $user['failed_attempts'] + 1;
            if ($attempts >= 3) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $lockQuery = $dbh->prepare("UPDATE tbluser SET failed_attempts = 0, locked_until = :lockUntil WHERE ID = :id");
                $lockQuery->execute([':lockUntil' => $lockUntil, ':id' => $user['ID']]);
                $_SESSION['msg'] = "🚫 Too many failed attempts. Account locked for 15 minutes.";
            } else {
                $failQuery = $dbh->prepare("UPDATE tbluser SET failed_attempts = :attempts WHERE ID = :id");
                $failQuery->execute([':attempts' => $attempts, ':id' => $user['ID']]);
                $_SESSION['msg'] = "❌ Invalid email/mobile or password. Attempt $attempts of 5.";
            }
        }
    } else {
        $_SESSION['msg'] = "❌ Invalid email/mobile or password.";
    }

    echo "<script>window.location.href='signin.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OLMS || Sign In</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Heebo', sans-serif;
        }
        .login-container {
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        .form-floating > label > i {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }
        .btn-primary {
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .toast-container {
            z-index: 9999;
        }
        #spinner {
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div id="spinner" class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">
        <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
            <div class="login-container p-4 p-sm-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-primary"><i class="fas fa-sign-in-alt me-2"></i>OLMS Login</h3>
                       <a href="../index.php" class="btn btn-outline-primary btn-sm">Home</a>        </div>

                <?php if (isset($_SESSION['msg'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div class="toast show bg-dark text-white" role="alert">
                        <div class="toast-body"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" class="form-control" name="emailormobnum" placeholder="Email or Mobile Number" required>
                        <label><i class="fas fa-user me-2"></i>Email or Mobile</label>
                        <div class="invalid-feedback">Please enter your email or mobile number.</div>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <label><i class="fas fa-lock me-2"></i>Password</label>
                        <div class="invalid-feedback">Password is required.</div>
                    </div>

                    <div class="mb-3 text-end">
                        <a href="forgot-password.php" class="text-primary small"><i class="fas fa-question-circle me-1"></i>Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary py-3 w-100 mb-3" name="login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="signup.php" class="text-muted small">Don't have an account? <strong>Sign Up</strong></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        window.addEventListener('load', function () {
            const spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.remove('show');
            }
        });

        // Bootstrap validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>