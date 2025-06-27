<?php 
session_start();
include('includes/dbconnection.php');

if(isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $mobno = $_POST['mobno'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = 'active';
    $role = 'user';

    $ret = "SELECT Email, MobileNumber FROM tbluser WHERE Email=:email || MobileNumber=:mobno";
    $query = $dbh->prepare($ret);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':mobno', $mobno, PDO::PARAM_STR);
    $query->execute();

    if($query->rowCount() == 0) {
        $sql = "INSERT INTO tbluser(FullName, MobileNumber, Email, Password, Status, Role) 
                VALUES(:fname, :mobno, :email, :password, :status, :role)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':mobno', $mobno, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':role', $role, PDO::PARAM_STR);

        if($query->execute()) {
            $_SESSION['msg'] = "✅ Successfully registered. You can now sign in.";
            echo "<script>window.location.href='signup.php';</script>";
        } else {
            $_SESSION['msg'] = "❌ Database error during registration.";
            echo "<script>window.location.href='signup.php';</script>";
        }
    } else {
        $_SESSION['msg'] = "⚠️ Email or Mobile Number already exists.";
        echo "<script>window.location.href='signup.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OLMS || Signup</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap & Styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- Optional Inline Custom CSS -->
    <style>
        .form-floating > label > i {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }
        .toast-container {
            z-index: 9999;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">
        <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
            <div class="bg-white rounded shadow p-4 p-sm-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-primary"><i class="fas fa-user-plus me-2"></i>OLMS Signup</h3>
                    <a href="../index.php" class="btn btn-outline-primary btn-sm">Home</a>
                </div>

                <!-- Toast Alert -->
                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="toast-container position-fixed bottom-0 end-0 p-3">
                        <div class="toast show bg-dark text-white" role="alert">
                            <div class="toast-body"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
               <form method="post" class="needs-validation" novalidate>
    <div class="form-floating mb-3 position-relative">
        <input type="text" name="fname" class="form-control" placeholder="Full Name"
               pattern="[A-Za-z\s]+" title="Full name should only contain letters and spaces" required>
        <label><i class="fas fa-user me-2"></i>Full Name</label>
        <div class="invalid-feedback">Full name is required and must contain only letters.</div>
    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="text" name="mobno" class="form-control" maxlength="10" pattern="[0-9]{10}" placeholder="Mobile Number" required>
                        <label><i class="fas fa-phone me-2"></i>Mobile Number</label>
                        <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <label><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <div class="invalid-feedback">Valid email required.</div>
                    </div>


                        <div class="form-floating mb-4 position-relative">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password"
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                                title="Password must be at least 8 characters long, include uppercase, lowercase, number, and special character"
                                required oninput="checkPasswordStrength(this.value); validatePasswordMatch();">
                            <label><i class="fas fa-lock me-2"></i>Password</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-3"
                                    onclick="togglePasswordVisibility('password')">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.
                            </div>
                        </div>

                        <!-- Strength badge -->
                        <div id="password-strength" class="mb-3"></div>

                        <!-- Confirm password -->
                        <div class="form-floating mb-4 position-relative">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required
                                oninput="validatePasswordMatch();">
                            <label><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-3"
                                    onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                            </button>
                            <div id="confirm-feedback" class="invalid-feedback">Passwords must match.</div>
                        </div>


                    <div class="mb-3 text-end">
                        <a href="signin.php" class="text-primary"><i class="fas fa-sign-in-alt me-1"></i>Already registered? Sign in</a>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary w-100 py-3">
                        <i class="fas fa-user-plus me-2"></i>Sign Up
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Client-side validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })();
    </script>

    <script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = fieldId === 'password' ? document.getElementById('togglePasswordIcon') : document.getElementById('toggleConfirmIcon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    const strengthBadge = document.getElementById('password-strength');
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[\W_]/.test(password)) strength++;

    let badgeClass = 'bg-danger';
    let message = 'Weak';

    if (strength === 3 || strength === 4) {
        badgeClass = 'bg-warning text-dark';
        message = 'Moderate';
    } else if (strength === 5) {
        badgeClass = 'bg-success';
        message = 'Strong';
    }

    strengthBadge.innerHTML = password
        ? `<span class="badge ${badgeClass}">${message} Password</span>`
        : '';
}

function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const feedback = document.getElementById('confirm-feedback');

    if (confirm.value && password.value !== confirm.value) {
        confirm.setCustomValidity('Passwords do not match');
        feedback.style.display = 'block';
    } else {
        confirm.setCustomValidity('');
        feedback.style.display = 'none';
    }
}
</script>

</body>
</html>
