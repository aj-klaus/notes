<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';
$department = null;

// Get department ID from URL
if (isset($_GET['id'])) {
    $department_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    if ($department_id) {
        try {
            $stmt = $dbh->prepare("SELECT id, name, description FROM tbldepartments WHERE id = :id");
            $stmt->bindParam(':id', $department_id, PDO::PARAM_INT);
            $stmt->execute();
            $department = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$department) {
                $message = "<p style='color:red;'>Department not found.</p>";
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Invalid department ID.</p>";
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for update
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $message = "<p style='color:red;'>Department Name is required.</p>";
    } else if (!$id) {
        $message = "<p style='color:red;'>Invalid department ID for update.</p>";
    } else {
        try {
            // Check if department name already exists for another ID
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tbldepartments WHERE name = :name AND id != :id");
            $stmt_check->bindParam(':name', $name);
            $stmt_check->bindParam(':id', $id);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $message = "<p style='color:red;'>A department with this name already exists.</p>";
            } else {
                $sql = "UPDATE tbldepartments SET name = :name, description = :description WHERE id = :id";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // Redirect to the list page on success
                    header('Location: index.php?status=success_edit');
                    exit();
                } else {
                    $message = "<p style='color:red;'>Error updating department.</p>";
                }
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    // Re-fetch the department data if there was an error, to pre-fill the form
    if (!$department && $id) {
        $stmt = $dbh->prepare("SELECT id, name, description FROM tbldepartments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} else {
    $message = "<p style='color:red;'>No department ID provided.</p>";
}
?>

<h1>Edit Department</h1>

<?php echo $message; ?>

<?php if ($department): ?>
    <form action="edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($department['id']); ?>">
        <div style="margin-bottom: 15px;">
            <label for="name">Department Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($department['name']); ?>" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="description">Description (Optional):</label>
            <textarea id="description" name="description" rows="4" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($department['description']); ?></textarea>
        </div>
        <div>
            <button type="submit" class="btn btn-success">Update Department</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>