<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch all users
$users = [];
try {
    $sql = "SELECT ID, FullName, Email, MobileNumber, RegistrationDate, Status, locked_until FROM tbluser ORDER BY RegistrationDate DESC";
    $stmt = $dbh->query($sql);
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalUsers = count($allUsers);
    $totalPages = ceil($totalUsers / $limit);

    // Slice users for current page
    $users = array_slice($allUsers, $offset, $limit);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>User Management</h1>

<p>
    <a href="create.php" class="btn btn-success">Add New User</a>
</p>

<?php if (empty($users)): ?>
    <p>No users registered yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th> <!-- Row number column -->
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Registration Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $i => $user): ?>
                <tr>
                    <td><?php echo $offset + $i + 1; ?></td> <!-- Row number -->
                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td><?php echo htmlspecialchars($user['MobileNumber']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['RegistrationDate']))); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($user['Status'])); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($user['ID']); ?>" class="btn">Edit</a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($user['ID']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their past loan records. If the user has active loans, deletion will be prevented.');">Delete</a>
                        
                        <?php if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()): ?>
                            <form method="post" action="unlock_user.php" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['ID']) ?>">
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to unlock this user?');">Unlock</button>
                            </form>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
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

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>
