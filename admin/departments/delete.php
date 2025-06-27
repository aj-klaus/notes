<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

if (isset($_GET['id'])) {
    $department_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($department_id) {
        try {
            // Delete the department
            $sql = "DELETE FROM tbldepartments WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $department_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Redirect to list with success message
                header('Location: index.php?status=success_delete');
                exit();
            } else {
                // Error handling (though PDO usually throws exceptions)
                echo "<p style='color:red;'>Error deleting department.</p>";
            }
        } catch (PDOException $e) {
            // If there's a foreign key constraint preventing deletion (e.g., if ON DELETE CASCADE/RESTRICT was used instead of SET NULL)
            echo "<p style='color:red;'>Database error: Could not delete department. " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid department ID provided.</p>";
    }
} else {
    echo "<p style='color:red;'>No department ID provided for deletion.</p>";
}

// Fallback redirect if something went wrong or no ID was given
if (!headers_sent()) {
    header('Location: index.php');
    exit();
}
?>