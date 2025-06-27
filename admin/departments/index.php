<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch all departments
$departments = [];
try {
    $stmt = $dbh->query("SELECT id, name, description FROM tbldepartments ORDER BY name ASC");
    $allDepartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalDepartments = count($allDepartments);
    $totalPages = ceil($totalDepartments / $limit);

    // Slice for current page
    $departments = array_slice($allDepartments, $offset, $limit);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Departments Management</h1>

<p>
    <a href="create.php" class="btn btn-success">Add New Department</a>
</p>

<?php if (empty($departments)): ?>
    <p>No departments found. Please add a new department.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th> <!-- Row number -->
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($departments as $i => $dept): ?>
                <tr>
                    <td><?php echo $offset + $i + 1; ?></td> <!-- Row number -->
                    <td><?php echo htmlspecialchars($dept['name']); ?></td>
                    <td><?php echo htmlspecialchars($dept['description']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($dept['id']); ?>" class="btn">Edit</a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($dept['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this department? This will also affect books linked to it (department_id will become NULL).');">Delete</a>
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
