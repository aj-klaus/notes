<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';
$available_books = [];
$users = [];
$default_loan_period_days = 14; // Default loan period in days

// Fetch available books (those with available_copies > 0)
try {
    $stmt_books = $dbh->query("SELECT id, title, isbn, available_copies FROM tblbooks WHERE available_copies > 0 ORDER BY title ASC");
    $available_books = $stmt_books->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<p style='color:red;'>Database error fetching books: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Fetch users (assuming 'tbluser' and 'ID', 'FullName' columns)
try {
    $stmt_users = $dbh->query("SELECT ID, FullName FROM tbluser ORDER BY FullName ASC"); // Changed SELECT to FullName
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<p style='color:red;'>Database error fetching users: " . htmlspecialchars($e->getMessage()) . "</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = filter_var($_POST['book_id'], FILTER_VALIDATE_INT);
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $loan_date = $_POST['loan_date'];
    $due_date = $_POST['due_date'];

    if (!$book_id || !$user_id || empty($loan_date) || empty($due_date)) {
        $message = "<p style='color:red;'>All fields are required and must be valid selections.</p>";
    } else {
        try {
            // Start a transaction for atomicity
            $dbh->beginTransaction();

            // 1. Check book availability (double-check in case another loan happened simultaneously)
            $stmt_check_book = $dbh->prepare("SELECT available_copies FROM tblbooks WHERE id = :book_id FOR UPDATE"); // FOR UPDATE locks the row
            $stmt_check_book->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_check_book->execute();
            $book_info = $stmt_check_book->fetch(PDO::FETCH_ASSOC);

            if (!$book_info || $book_info['available_copies'] <= 0) {
                $dbh->rollBack();
                $message = "<p style='color:red;'>Selected book is not available for loan or does not exist.</p>";
            } else {
                // 2. Insert into tbllans
                $sql_loan = "INSERT INTO tbllans (book_id, user_id, loan_date, due_date, status) 
                             VALUES (:book_id, :user_id, :loan_date, :due_date, 'on_loan')";
                $stmt_loan = $dbh->prepare($sql_loan);
                $stmt_loan->bindParam(':book_id', $book_id, PDO::PARAM_INT);
                $stmt_loan->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt_loan->bindParam(':loan_date', $loan_date);
                $stmt_loan->bindParam(':due_date', $due_date);

                if ($stmt_loan->execute()) {
                    // 3. Decrement available_copies in tblbooks
                    $sql_update_book = "UPDATE tblbooks SET available_copies = available_copies - 1 WHERE id = :book_id";
                    $stmt_update_book = $dbh->prepare($sql_update_book);
                    $stmt_update_book->bindParam(':book_id', $book_id, PDO::PARAM_INT);

                    if ($stmt_update_book->execute()) {
                        $dbh->commit(); // Commit transaction on success
                        header('Location: index.php?status=success_loan');
                        exit();
                    } else {
                        $dbh->rollBack();
                        $message = "<p style='color:red;'>Error updating book availability.</p>";
                    }
                } else {
                    $dbh->rollBack();
                    $message = "<p style='color:red;'>Error recording loan.</p>";
                }
            }
        } catch (PDOException $e) {
            $dbh->rollBack(); // Rollback on any PDO exception
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Record New Loan</h1>

<?php echo $message; ?>

<form action="create.php" method="post">
    <div style="margin-bottom: 15px;">
        <label for="book_id">Select Book:</label>
        <select id="book_id" name="book_id" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">-- Select a Book --</option>
            <?php if (empty($available_books)): ?>
                <option value="" disabled>No available books to loan.</option>
            <?php else: ?>
                <?php foreach ($available_books as $book): ?>
                    <option value="<?php echo htmlspecialchars($book['id']); ?>">
                        <?php echo htmlspecialchars($book['title']) . ' (ISBN: ' . htmlspecialchars($book['isbn']) . ' - Available: ' . htmlspecialchars($book['available_copies']) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <?php if (empty($available_books)): ?><p style="color:red;">No books are currently available for loan. Please add more copies or wait for returns.</p><?php endif; ?>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="user_id">Select User:</label>
        <select id="user_id" name="user_id" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">-- Select a User --</option>
            <?php if (empty($users)): ?>
                <option value="" disabled>No users found.</option>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['ID']); ?>">
                        <?php echo htmlspecialchars($user['FullName']); ?> </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <?php if (empty($users)): ?><p style="color:red;">No users found. Please add users to the system.</p><?php endif; ?>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="loan_date">Loan Date:</label>
        <input type="date" id="loan_date" name="loan_date" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="due_date">Due Date (Default: <?php echo $default_loan_period_days; ?> days from loan date):</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+' . $default_loan_period_days . ' days')); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div>
        <button type="submit" class="btn btn-success">Record Loan</button>
        <a href="index.php" class="btn">Cancel</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>