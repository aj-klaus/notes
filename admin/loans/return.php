<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

if (isset($_GET['id'])) {
    $loan_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($loan_id) {
        try {
            // Start a transaction for atomicity
            $dbh->beginTransaction();

            // 1. Get book_id associated with this loan
            $stmt_get_book = $dbh->prepare("SELECT book_id FROM tbllans WHERE id = :loan_id AND status = 'on_loan' FOR UPDATE");
            $stmt_get_book->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
            $stmt_get_book->execute();
            $loan_info = $stmt_get_book->fetch(PDO::FETCH_ASSOC);

            if (!$loan_info) {
                $dbh->rollBack();
                echo "<p style='color:red;'>Loan not found or already returned.</p>";
            } else {
                $book_id = $loan_info['book_id'];
                $return_date = date('Y-m-d');

                // 2. Update loan status and return_date in tbllans
                $sql_update_loan = "UPDATE tbllans SET status = 'returned', return_date = :return_date WHERE id = :loan_id";
                $stmt_update_loan = $dbh->prepare($sql_update_loan);
                $stmt_update_loan->bindParam(':return_date', $return_date);
                $stmt_update_loan->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);

                if ($stmt_update_loan->execute()) {
                    // 3. Increment available_copies in tblbooks
                    $sql_update_book = "UPDATE tblbooks SET available_copies = available_copies + 1 WHERE id = :book_id";
                    $stmt_update_book = $dbh->prepare($sql_update_book);
                    $stmt_update_book->bindParam(':book_id', $book_id, PDO::PARAM_INT);

                    if ($stmt_update_book->execute()) {
                        $dbh->commit(); // Commit transaction on success
                        header('Location: index.php?status=success_return');
                        exit();
                    } else {
                        $dbh->rollBack();
                        echo "<p style='color:red;'>Error updating book availability after return.</p>";
                    }
                } else {
                    $dbh->rollBack();
                    echo "<p style='color:red;'>Error marking loan as returned.</p>";
                }
            }
        } catch (PDOException $e) {
            $dbh->rollBack(); // Rollback on any PDO exception
            echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid loan ID provided.</p>";
    }
} else {
    echo "<p style='color:red;'>No loan ID provided for return.</p>";
}

// Fallback redirect if something went wrong or no ID was given
if (!headers_sent()) {
    header('Location: index.php');
    exit();
}
?>