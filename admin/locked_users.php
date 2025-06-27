<?php
require_once __DIR__ . '/includes/admin_header.php';


$lockedUsers = [];
try {
    $sql = "SELECT ID, FullName, Email, MobileNumber, RegistrationDate, locked_until 
            FROM tbluser 
            WHERE locked_until IS NOT NULL AND locked_until > NOW()
            ORDER BY locked_until DESC";
    $stmt = $dbh->query($sql);
    $lockedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Locked Users</h1>

<?php if (empty($lockedUsers)): ?>
    <p>No users are currently locked.</p>
<?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Locked Until</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lockedUsers as $index => $user): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($user['FullName']) ?></td>
                    <td><?= htmlspecialchars($user['Email']) ?></td>
                    <td><?= htmlspecialchars($user['MobileNumber']) ?></td>
                    <td><?= htmlspecialchars($user['locked_until']) ?></td>
                    <td>
                        <form method="post" action="unlock_user.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['ID']) ?>">
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Unlock this user?');">
                                Unlock
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
