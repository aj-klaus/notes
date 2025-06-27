<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch all books with department names
$books = [];
try {
    $sql = "SELECT 
                b.id, b.title, b.author, b.isbn, b.publication_year, b.publisher, b.genre, 
                b.total_copies, b.available_copies, d.name AS department_name
            FROM tblbooks b
            LEFT JOIN tbldepartments d ON b.department_id = d.id
            ORDER BY b.title ASC";
    $stmt = $dbh->query($sql);
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalBooks = count($allBooks);
    $totalPages = ceil($totalBooks / $limit);

    // Slice for current page
    $books = array_slice($allBooks, $offset, $limit);
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Books Management</h1>

<p>
    <a href="create.php" class="btn btn-success">Add New Book</a>
</p>

<?php if (empty($books)): ?>
    <p>No books found. Please add a new book.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th> <!-- Row number column -->
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Dept.</th>
                <th>Total Copies</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $i => $book): ?>
                <tr>
                    <td><?php echo $offset + $i + 1; ?></td> <!-- Row number -->
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($book['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($book['total_copies']); ?></td>
                    <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="btn">Edit</a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book? All associated loan records will also be deleted.');">Delete</a>
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
