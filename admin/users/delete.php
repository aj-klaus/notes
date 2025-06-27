<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

if (isset($_GET['id'])) {
    $user_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($user_id) {
        try {
            // Check if the user has any active loans
            $stmt_check_loans = $dbh->prepare("SELECT COUNT(*) FROM tbllans WHERE user_id = :user_id AND status = 'on_loan'");
            $stmt_check_loans->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_check_loans->execute();
            
            if ($stmt_check_loans->fetchColumn() > 0) {
                // User has active loans, prevent deletion
                echo "<p style='color:red;'>Cannot delete user: This user has books currently on loan. All books must be returned first.</p>";
            } else {
                // No active loans, proceed with deletion
                $sql = "DELETE FROM tbluser WHERE ID = :id";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    header('Location: index.php?status=success_delete');
                    exit();
                } else {
                    echo "<p style='color:red;'>Error deleting user.</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Database error: Could not delete user. " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid user ID provided.</p>";
    }
} else {
    echo "<p style='color:red;'>No user ID provided for deletion.</p>";
}

// Fallback redirect if something went wrong or no ID was given
if (!headers_sent()) {
    header('Location: index.php');
    exit();
}
?>