<?php
require_once __DIR__ . '/includes/admin_header.php';
// admin_header.php already handles session_start() and auth_check.php
// $dbh (PDO object) should be available from auth_check.php

// Enable error reporting for debugging, temporarily if needed
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// --- Fetch Dashboard Data ---
// 1. Count Total Users (from tbluser)
$total_users = 0;
try {
    $stmt = $dbh->query("SELECT COUNT(*) AS total FROM tbluser");
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_users = $row['total'];
    }
} catch (PDOException $e) {
    // Log the error if logging is set up
    // app_log("Error fetching total users: " . $e->getMessage(), 'error', 'CRITICAL');
    // For now, just set to 0 and perhaps display a warning in debug mode
    $total_users = 0;
    // echo "<p class='text-danger'>Error fetching total users: " . $e->getMessage() . "</p>"; // Uncomment for debugging
}

// 2. Count Total Books (from tblbooks)
$total_books = 0;
try {
    $stmt = $dbh->query("SELECT COUNT(*) AS total FROM tblbooks");
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_books = $row['total'];
    }
} catch (PDOException $e) {
    // app_log("Error fetching total books: " . $e->getMessage(), 'error', 'CRITICAL');
    $total_books = 0;
    // echo "<p class='text-danger'>Error fetching total books: " . $e->getMessage() . "</p>"; // Uncomment for debugging
}

// 3. Count Active Loans (from tbllans)
$active_loans = 0;
try {
    $stmt = $dbh->query("SELECT COUNT(*) AS total FROM tbllans WHERE status = 'on_loan'");
    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $active_loans = $row['total'];
    }
} catch (PDOException $e) {
    // app_log("Error fetching active loans: " . $e->getMessage(), 'error', 'CRITICAL');
    $active_loans = 0;
    // echo "<p class='text-danger'>Error fetching active loans: " . $e->getMessage() . "</p>"; // Uncomment for debugging
}

// 4. Fetch Overdue Books (from tbllans)
$overdue_loans = [];
$sql_overdue = "
    SELECT 
        l.id AS loan_id,
        b.title AS book_title,
        u.fullname AS loaned_to_user,
        l.due_date,
        DATEDIFF(CURRENT_DATE(), l.due_date) AS days_overdue
    FROM tbllans l
    JOIN tblbooks b ON l.book_id = b.id
    JOIN tbluser u ON l.user_id = u.ID
    WHERE l.status = 'on_loan' AND l.due_date < CURRENT_DATE()
    ORDER BY l.due_date ASC";
try {
    $stmt_overdue = $dbh->query($sql_overdue);
    if ($stmt_overdue) {
        $overdue_loans = $stmt_overdue->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // app_log("Error fetching overdue loans: " . $e->getMessage(), 'error', 'CRITICAL');
    $overdue_loans = [];
    // echo "<p class='text-danger'>Error fetching overdue loans: " . $e->getMessage() . "</p>"; // Uncomment for debugging
}
?>

<h1>Admin Dashboard</h1>

<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-bottom: 30px;">
    <a href="/onss/admin/users/index.php" class="dashboard-card-link">
        <div class="dashboard-card">
            <h2>Total Users</h2>
            <p class="card-blue"><?php echo $total_users; ?></p>
        </div>
    </a>
    
    <a href="/onss/admin/books/index.php" class="dashboard-card-link">
        <div class="dashboard-card">
            <h2>Total Books</h2>
            <p class="card-green"><?php echo $total_books; ?></p>
        </div>
    </a>

    <a href="/onss/admin/loans/index.php" class="dashboard-card-link">
        <div class="dashboard-card">
            <h2>Active Loans</h2>
            <p class="card-orange"><?php echo $active_loans; ?></p>
        </div>
    </a>
</div>

<h2>Overdue Books</h2>
<?php if (empty($overdue_loans)): ?>
    <p>No overdue books at the moment. Great job!</p>
<?php else: ?>
    <table class="overdue-table">
        <thead>
            <tr>
                <th>Loan ID</th>
                <th>Book Title</th>
                <th>Loaned To</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($overdue_loans as $loan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                    <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($loan['loaned_to_user']); ?></td>
                    <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($loan['days_overdue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>