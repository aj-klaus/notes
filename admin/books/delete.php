<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

if (isset($_GET['id'])) {
    $book_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($book_id) {
        try {
            // Delete the book
            $sql = "DELETE FROM tblbooks WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Redirect to list with success message
                header('Location: index.php?status=success_delete');
                exit();
            } else {
                echo "<p style='color:red;'>Error deleting book.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Database error: Could not delete book. " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid book ID provided.</p>";
    }
} else {
    echo "<p style='color:red;'>No book ID provided for deletion.</p>";
}

// Fallback redirect if something went wrong or no ID was given
if (!headers_sent()) {
    header('Location: index.php');
    exit();
}
?>