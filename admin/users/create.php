<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['FullName']);
    $email = trim($_POST['Email']);
    $mobileNumber = trim($_POST['MobileNumber']);
    $password = $_POST['Password']; // Raw password
    $status = $_POST['Status'] ?? 'active'; // Default to 'active'

    // Basic validation
    if (empty($fullName) || empty($email) || empty($mobileNumber) || empty($password)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Invalid email format.</p>";
    } elseif (strlen($password) < 6) {
        $message = "<p style='color:red;'>Password must be at least 6 characters long.</p>";
    } else {
        try {
            // Check if email or mobile number already exists
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tbluser WHERE Email = :email OR MobileNumber = :mobile");
            $stmt_check->bindParam(':email', $email);
            $stmt_check->bindParam(':mobile', $mobileNumber);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $message = "<p style='color:red;'>An account with this Email or Mobile Number already exists.</p>";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO tbluser (FullName, Email, MobileNumber, Password, Status, RegistrationDate) 
                        VALUES (:fullName, :email, :mobileNumber, :password, :status, NOW())";
                $stmt = $dbh->prepare($sql);
                
                $stmt->bindParam(':fullName', $fullName);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':mobileNumber', $mobileNumber);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':status', $status);

                if ($stmt->execute()) {
                    header('Location: index.php?status=success_add');
                    exit();
                } else {
                    $message = "<p style='color:red;'>Error adding user.</p>";
                }
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Add New User</h1>

<?php echo $message; ?>

<form action="create.php" method="post">
    <div style="margin-bottom: 15px;">
        <label for="FullName">Full Name:</label>
        <input type="text" id="FullName" name="FullName" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label for="Email">Email:</label>
        <input type="email" id="Email" name="Email" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label for="MobileNumber">Mobile Number:</label>
        <input type="text" id="MobileNumber" name="MobileNumber" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label for="Password">Password:</label>
        <input type="password" id="Password" name="Password" required minlength="6" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        <small>Minimum 6 characters</small>
    </div>
    <div style="margin-bottom: 15px;">
        <label for="Status">Status:</label>
        <select id="Status" name="Status" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div>
        <button type="submit" class="btn btn-success">Add User</button>
        <a href="index.php" class="btn">Cancel</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>