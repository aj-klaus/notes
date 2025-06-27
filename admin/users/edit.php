<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';
$user = null;

// Get user ID from URL or POST
$user_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : (isset($_POST['ID']) ? filter_var($_POST['ID'], FILTER_SANITIZE_NUMBER_INT) : null);

if (!$user_id) {
    $message = "<p style='color:red;'>No user ID provided.</p>";
} else {
    try {
        // Fetch current user data including the Role
        $stmt = $dbh->prepare("SELECT ID, FullName, Email, MobileNumber, Status, Role FROM tbluser WHERE ID = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $message = "<p style='color:red;'>User not found.</p>";
            $user_id = null; // Invalidate ID if user not found
        }
    } catch (PDOException $e) {
        $message = "<p style='color:red;'>Database error fetching user: " . htmlspecialchars($e->getMessage()) . "</p>";
        $user_id = null;
    }
}

if ($user_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for update
    $fullName = trim($_POST['FullName']);
    $email = trim($_POST['Email']);
    $mobileNumber = trim($_POST['MobileNumber']);
    $password = $_POST['Password']; // New password (can be empty)
    $status = $_POST['Status'] ?? 'active';
    $role = $_POST['Role']; // Get the selected role

    // Basic validation
    if (empty($fullName) || empty($email) || empty($mobileNumber)) {
        $message = "<p style='color:red;'>Full Name, Email, and Mobile Number are required.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Invalid email format.</p>";
    } elseif (!empty($password) && strlen($password) < 6) {
        $message = "<p style='color:red;'>New password must be at least 6 characters long if provided.</p>";
    } elseif (!in_array($role, ['user', 'admin'])) { // Validate role input
        $message = "<p style='color:red;'>Invalid role selected.</p>";
    } else {
        try {
            // Check if email or mobile number already exists for *another* user
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tbluser WHERE (Email = :email OR MobileNumber = :mobile) AND ID != :id");
            $stmt_check->bindParam(':email', $email);
            $stmt_check->bindParam(':mobile', $mobileNumber);
            $stmt_check->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $message = "<p style='color:red;'>An account with this Email or Mobile Number already exists for another user.</p>";
            } else {
                $sql = "UPDATE tbluser SET FullName = :fullName, Email = :email, MobileNumber = :mobileNumber, Status = :status, Role = :role";
                if (!empty($password)) {
                    $sql .= ", Password = :password"; // Add password update if provided
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE ID = :id";
                $stmt = $dbh->prepare($sql);
                
                $stmt->bindParam(':fullName', $fullName);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':mobileNumber', $mobileNumber);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':role', $role); // Bind the new role
                if (!empty($password)) {
                    $stmt->bindParam(':password', $hashedPassword);
                }
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // If the logged-in admin changes their own role, their session might need updating
                    // or they might be logged out and forced to re-login based on auth_check.
                    // For simplicity, we'll let auth_check handle it on next page load.
                    header('Location: index.php?status=success_edit');
                    exit();
                } else {
                    $message = "<p style='color:red;'>Error updating user.</p>";
                }
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    // Re-fetch user data if there was an error to show current state on form
    // (Ensure the Role is also fetched here)
    if (!empty($message) && $user_id) {
        try {
            $stmt = $dbh->prepare("SELECT ID, FullName, Email, MobileNumber, Status, Role FROM tbluser WHERE ID = :id");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message .= "<p style='color:red;'>Error re-fetching user data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Edit User</h1>

<?php echo $message; ?>

<?php if ($user): ?>
    <form action="edit.php" method="post">
        <input type="hidden" name="ID" value="<?php echo htmlspecialchars($user['ID']); ?>">
        <div style="margin-bottom: 15px;">
            <label for="FullName">Full Name:</label>
            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($user['FullName']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="Email">Email:</label>
            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($user['Email']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="MobileNumber">Mobile Number:</label>
            <input type="text" id="MobileNumber" name="MobileNumber" value="<?php echo htmlspecialchars($user['MobileNumber']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="Password">New Password (leave blank to keep current):</label>
            <input type="password" id="Password" name="Password" minlength="6" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            <small>Enter a new password only if you want to change it. Minimum 6 characters.</small>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="Status">Status:</label>
            <select id="Status" name="Status" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
                <option value="active" <?php echo ($user['Status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($user['Status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="Role">Role:</label>
            <select id="Role" name="Role" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
                <option value="user" <?php echo ($user['Role'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo ($user['Role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-success">Update User</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>