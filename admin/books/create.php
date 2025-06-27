<?php
// IMPORTANT: ob_start() MUST BE THE ABSOLUTE FIRST THING IN THE FILE.
// No spaces, no newlines, no characters before <?php or this line.
ob_start();

// Enable error reporting for debugging. Remember to turn these OFF in production.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header
// admin_header.php is assumed to handle session_start() and auth_check.php,
// and outputs the initial HTML structure.
// $dbh (PDO object) should be available from auth_check.php or dbconnection.php.

$departments = []; // Initialize departments array

// Fetch departments for the dropdown
try {
    $stmt = $dbh->query("SELECT id, name FROM tbldepartments ORDER BY name ASC");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Store error in session for display
    $_SESSION['error_message'] = "Database error fetching departments: " . htmlspecialchars($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming your submit button in the form has name="submit_add_book"
    // If not, you might want to use a check like: if (isset($_POST['title']))
    // or give your submit button a specific name attribute.

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publication_year = filter_var($_POST['publication_year'], FILTER_VALIDATE_INT);
    $publisher = trim($_POST['publisher']);
    $genre = trim($_POST['genre']);
    $total_copies = filter_var($_POST['total_copies'], FILTER_VALIDATE_INT);
    // department_id can be 0 if 'None' selected
    $department_id = filter_var($_POST['department_id'], FILTER_VALIDATE_INT); 

    // Basic validation
    if (empty($title) || empty($author) || empty($isbn) || $total_copies === false || $total_copies < 0) {
        $_SESSION['error_message'] = "Title, Author, ISBN, and Total Copies (must be non-negative number) are required.";
        header('Location: create.php'); // Redirect back to the form
        exit();
    } else {
        try {
            // --- Check if ISBN already exists ---
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tblbooks WHERE isbn = :isbn");
            $stmt_check->bindParam(':isbn', $isbn);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['error_message'] = "A book with this ISBN ('" . htmlspecialchars($isbn) . "') already exists.";
                header('Location: create.php'); // Redirect back to the form
                exit();
            } else {
                // --- Proceed with Insertion ---
                $sql = "INSERT INTO tblbooks (title, author, isbn, publication_year, publisher, genre, total_copies, available_copies, department_id)
                        VALUES (:title, :author, :isbn, :publication_year, :publisher, :genre, :total_copies, :available_copies, :department_id)";
                $stmt = $dbh->prepare($sql);

                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':author', $author);
                $stmt->bindParam(':isbn', $isbn);
                $stmt->bindParam(':publication_year', $publication_year, PDO::PARAM_INT);
                $stmt->bindParam(':publisher', $publisher);
                $stmt->bindParam(':genre', $genre);
                $stmt->bindParam(':total_copies', $total_copies, PDO::PARAM_INT);
                $stmt->bindParam(':available_copies', $total_copies, PDO::PARAM_INT); // Initially, all copies are available

                // Handle department_id (NULL if 'None' is selected or department_id is 0)
                $dept_id_param = ($department_id > 0) ? $department_id : null;
                $stmt->bindParam(':department_id', $dept_id_param, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Book '" . htmlspecialchars($title) . "' added successfully!";
                    header('Location: index.php?status=success_add'); // Redirect to books list page
                    exit();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $_SESSION['error_message'] = "Error adding book: " . (isset($errorInfo[2]) ? htmlspecialchars($errorInfo[2]) : "Unknown database error.");
                    header('Location: create.php'); // Redirect back to the form on failure
                    exit();
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . htmlspecialchars($e->getMessage());
            header('Location: create.php'); // Redirect back to the form on exception
            exit();
        }
    }
}

// --- Retrieve and Display Messages from Session (after potential redirects) ---
$errorMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after displaying
}

$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying
}
?>

<div class="admin-container">
    <h1>Add New Book</h1>


    <form action="create.php" method="post" style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 15px;">
            <label for="title" style="display: block; margin-bottom: 5px; font-weight: bold;">Title:</label>
            <input type="text" id="title" name="title" required 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="author" style="display: block; margin-bottom: 5px; font-weight: bold;">Author:</label>
            <input type="text" id="author" name="author" required 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="isbn" style="display: block; margin-bottom: 5px; font-weight: bold;">ISBN (e.g., 978-0321765723):</label>
            <input type="text" id="isbn" name="isbn" required 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="publication_year" style="display: block; margin-bottom: 5px; font-weight: bold;">Publication Year:</label>
            <input type="number" id="publication_year" name="publication_year" min="1000" max="<?php echo date('Y'); ?>" 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="publisher" style="display: block; margin-bottom: 5px; font-weight: bold;">Publisher:</label>
            <input type="text" id="publisher" name="publisher" 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="genre" style="display: block; margin-bottom: 5px; font-weight: bold;">Genre:</label>
            <input type="text" id="genre" name="genre" 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="total_copies" style="display: block; margin-bottom: 5px; font-weight: bold;">Total Copies:</label>
            <input type="number" id="total_copies" name="total_copies" required min="0" value="1" 
                   style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="department_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Department:</label>
            <select id="department_id" name="department_id" 
                    style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; background-color: #fff;">
                <option value="0">-- None --</option>
                <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No departments available</option>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <button type="submit" name="submit_add_book" class="btn btn-success">Add Book</button>

            
    <?php if ($errorMessage): ?>
        <div style="color: red; padding: 10px; border: 1px solid red; background-color: #ffebe6; margin-bottom: 15px; border-radius: 5px;">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div style="color: green; padding: 10px; border: 1px solid green; background-color: #e6ffe6; margin-bottom: 15px; border-radius: 5px;">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
ob_end_flush(); // Flushes the output buffer
?>