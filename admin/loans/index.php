<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

// Pagination setup for current loans
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch all current 'on_loan' books
$current_loans = [];
try {
    $sql_current = "
        SELECT 
            l.id AS loan_id,
            b.title AS book_title,
            b.isbn,
            u.FullName AS loaned_to_user_name,
            l.loan_date,
            l.due_date,
            DATEDIFF(CURRENT_DATE(), l.due_date) AS days_overdue
        FROM tbllans l
        JOIN tblbooks b ON l.book_id = b.id
        JOIN tbluser u ON l.user_id = u.ID
        WHERE l.status = 'on_loan'
        ORDER BY l.due_date ASC";
    $stmt_current = $dbh->query($sql_current);
    $all_current_loans = $stmt_current->fetchAll(PDO::FETCH_ASSOC);
    $totalLoans = count($all_current_loans);
    $totalPages = ceil($totalLoans / $limit);
    $current_loans = array_slice($all_current_loans, $offset, $limit);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error fetching current loans: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Fetch 'returned' books history (unchanged)
$returned_loans = [];
try {
    $sql_returned = "
        SELECT 
            l.id AS loan_id,
            b.title AS book_title,
            b.isbn,
            u.FullName AS loaned_to_user_name,
            l.loan_date,
            l.due_date,
            l.return_date
        FROM tbllans l
        JOIN tblbooks b ON l.book_id = b.id
        JOIN tbluser u ON l.user_id = u.ID
        WHERE l.status = 'returned'
        ORDER BY l.return_date DESC
        LIMIT 50";
    $stmt_returned = $dbh->query($sql_returned);
    $returned_loans = $stmt_returned->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error fetching returned loans: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Loans Management</h1>

<p>
    <a href="create.php" class="btn btn-success">Record New Loan</a>
</p>

<h2>Current Loans (Books On Loan)</h2>

<?php if (empty($current_loans)): ?>
    <p>No books are currently on loan.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Book Title (ISBN)</th>
                <th>Loaned To (Full Name)</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Overdue (Days)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($current_loans as $i => $loan): ?>
                <tr <?php echo ($loan['days_overdue'] > 0) ? 'style="background-color: #ffebe6; color: #dc3545;"' : ''; ?>>
                    <td><?php echo $offset + $i + 1; ?></td> <!-- Row number -->
                    <td><?php echo htmlspecialchars($loan['book_title']) . ' (' . htmlspecialchars($loan['isbn']) . ')'; ?></td>
                    <td><?php echo htmlspecialchars($loan['loaned_to_user_name']); ?></td>
                    <td><?php echo htmlspecialchars($loan['loan_date']); ?></td>
                    <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                    <td><?php echo ($loan['days_overdue'] > 0) ? htmlspecialchars($loan['days_overdue']) : 'N/A'; ?></td>
                    <td>
                        <a href="return.php?id=<?php echo htmlspecialchars($loan['loan_id']); ?>" class="btn btn-primary" onclick="return confirm('Are you sure you want to mark this book as returned?');">Return</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination for current loans -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 20px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" style="<?php echo ($i == $page) ? 'font-weight: bold; text-decoration: underline;' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<h2 style="margin-top: 40px;">Returned Books History (Last 50)</h2>

<?php if (empty($returned_loans)): ?>
    <p>No books have been returned yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Loan ID</th>
                <th>Book Title (ISBN)</th>
                <th>Loaned To (Full Name)</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($returned_loans as $loan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                    <td><?php echo htmlspecialchars($loan['book_title']) . ' (' . htmlspecialchars($loan['isbn']) . ')'; ?></td>
                    <td><?php echo htmlspecialchars($loan['loaned_to_user_name']); ?></td>
                    <td><?php echo htmlspecialchars($loan['loan_date']); ?></td>
                    <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($loan['return_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>
