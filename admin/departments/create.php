<?php
require_once __DIR__ . '/../includes/admin_header.php'; // Path to admin header

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $message = "<p style='color:red;'>Department Name is required.</p>";
    } else {
        try {
            // Check if department name already exists
            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM tbldepartments WHERE name = :name");
            $stmt_check->bindParam(':name', $name);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $message = "<p style='color:red;'>A department with this name already exists.</p>";
            } else {
                $sql = "INSERT INTO tbldepartments (name, description) VALUES (:name, :description)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);

                if ($stmt->execute()) {
                    // Redirect to the list page on success
                    header('Location: index.php?status=success_add');
                    exit();
                } else {
                    $message = "<p style='color:red;'>Error adding department.</p>";
                }
            }
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Add New Department</h1>

<?php echo $message; ?>

<form action="create.php" method="post">
    <div style="margin-bottom: 15px;">
        <label for="name">Department Name:</label>
        <input type="text" id="name" name="name" required style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label for="description">Description (Optional):</label>
        <textarea id="description" name="description" rows="4" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;"></textarea>
    </div>
    <div>
        <button type="submit" class="btn btn-success">Add Department</button>
        <a href="index.php" class="btn">Cancel</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../includes/admin_footer.php'; // Path to admin footer
?>