<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';
$book = null;
$departments = [];

// Fetch departments for the dropdown
try {
    $stmt_dept = $dbh->query("SELECT id, name FROM tbldepartments ORDER BY name ASC");
    $departments = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<p style='color:red;'>Database error fetching departments: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Get book ID from URL or POST
$book_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : (isset($_POST['id']) ? filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT) : null);

if (!$book_id) {
    $message = "<p style='color:red;'>No book ID provided.</p>";
} else {
    try {
        // Fetch current book data
        $stmt = $dbh->prepare("SELECT id, title, author, isbn, publication_year, publisher, genre, total_copies, available_copies, department_id FROM tblbooks WHERE id = :id");
        $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            $message = "<p style='color:red;'>Book not found.</p>";
            $book_id = null; // Invalidate ID if book not found
        }
    } catch (PDOException $e) {
        $message = "<p style='color:red;'>Database error fetching book: " . htmlspecialchars($e->getMessage()) . "</p>";
        $book_id = null;
    }
}

if ($book_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for update
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publication_year = filter_var($_POST['publication_year'], FILTER_VALIDATE_INT);
    $publisher = trim($_POST['publisher']);
    $genre = trim($_POST['genre']);
    $new_total_copies = filter_var($_POST['total_copies'], FILTER_VALIDATE_INT);
    $department_id = filter_var($_POST['department_id'], FILTER_VALIDATE_INT);

    // Basic validation
    if (empty($title) || empty($author) || empty($isbn) || $new_total_copies === false || $new_total_copies < 0) {
        $message = "<p style='color:red;'>Title, Author, ISBN, and Total Copies (must be non-negative number) are required.</p>";
    } else {
        try {
            // Check if ISBN already exists for another book
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tblbooks WHERE isbn = :isbn AND id != :id");
            $stmt_check->bindParam(':isbn', $isbn);
            $stmt_check->bindParam(':id', $book_id);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $message = "<p style='color:red;'>A book with this ISBN already exists.</p>";
            } else {
                // Calculate available copies based on change in total copies
                $old_total_copies = $book['total_copies'];
                $old_available_copies = $book['available_copies'];
                
                $delta_copies = $new_total_copies - $old_total_copies;
                $new_available_copies = $old_available_copies + $delta_copies;

                // Ensure available copies don't exceed total copies or go below zero
                if ($new_available_copies > $new_total_copies) {
                    $new_available_copies = $new_total_copies;
                }
                if ($new_available_copies < 0) {
                    // This scenario means you're reducing total copies below what's currently loaned out.
                    // You might want to prevent this, or just cap available at 0.
                    // For now, we'll cap at 0 and issue a warning.
                    $message .= "<p style='color:orange;'>Warning: Total copies reduced, some books might still be on loan making available copies negative. Current loans need to be returned.</p>";
                    $new_available_copies = 0; // Or better: prevent if new_total_copies < (total_copies - available_copies)
                }

                // If new_total_copies is less than (old_total_copies - old_available_copies), it means
                // the new total is less than the number of books *currently out on loan*.
                // This is a critical error. We should prevent it.
                $books_currently_on_loan = $old_total_copies - $old_available_copies;
                if ($new_total_copies < $books_currently_on_loan) {
                    $message = "<p style='color:red;'>Error: You cannot reduce total copies below the number of books currently on loan (" . $books_currently_on_loan . " books currently out).</p>";
                    // Re-fetch book to display current values if error occurs
                    $stmt = $dbh->prepare("SELECT id, title, author, isbn, publication_year, publisher, genre, total_copies, available_copies, department_id FROM tblbooks WHERE id = :id");
                    $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $book = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $sql = "UPDATE tblbooks SET 
                                title = :title, 
                                author = :author, 
                                isbn = :isbn, 
                                publication_year = :publication_year, 
                                publisher = :publisher, 
                                genre = :genre, 
                                total_copies = :total_copies, 
                                available_copies = :available_copies, 
                                department_id = :department_id 
                            WHERE id = :id";
                    $stmt = $dbh->prepare($sql);
                    
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':author', $author);
                    $stmt->bindParam(':isbn', $isbn);
                    $stmt->bindParam(':publication_year', $publication_year, PDO::PARAM_INT);
                    $stmt->bindParam(':publisher', $publisher);
                    $stmt->bindParam(':genre', $genre);
                    $stmt->bindParam(':total_copies', $new_total_copies, PDO::PARAM_INT);
                    $stmt->bindParam(':available_copies', $new_available_copies, PDO::PARAM_INT);

                    $dept_id_param = ($department_id > 0) ? $department_id : null;
                    $stmt->bindParam(':department_id', $dept_id_param, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $book_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        header('Location: index.php?status=success_edit');
                        exit();
                    } else {
                        $message = "<p style='color:red;'>Error updating book.</p>";
                    }
                }
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Edit Book</h1>

<?php echo $message; ?>

<?php if ($book): ?>
    <form action="edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($book['id']); ?>">
        <div style="margin-bottom: 15px;">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="publication_year">Publication Year:</label>
            <input type="number" id="publication_year" name="publication_year" value="<?php echo htmlspecialchars($book['publication_year']); ?>" min="1000" max="<?php echo date('Y'); ?>" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="publisher">Publisher:</label>
            <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="total_copies">Total Copies:</label>
            <input type="number" id="total_copies" name="total_copies" value="<?php echo htmlspecialchars($book['total_copies']); ?>" required min="<?php echo ($book['total_copies'] - $book['available_copies']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            <small style="color: #666;">Cannot be less than books currently on loan (<?php echo ($book['total_copies'] - $book['available_copies']); ?>).</small>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="available_copies">Available Copies (calculated):</label>
            <input type="text" id="available_copies" value="<?php echo htmlspecialchars($book['available_copies']); ?>" disabled style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #eee; background-color: #f9f9f9; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="department_id">Department:</label>
            <select id="department_id" name="department_id" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
                <option value="0">-- None --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo ($book['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-success">Update Book</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>