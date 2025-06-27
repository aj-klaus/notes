<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

include(__DIR__ . '/includes/dbconnection.php');

// Check if user is logged in and has the 'user' role
if (!isset($_SESSION['uid']) || empty($_SESSION['uid']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'user')) {
    header('location:logout.php');
    exit();
}

// Fetch books and availability
$sql = "
SELECT 
    b.id,
    b.title,
    b.author,
    b.isbn,
    b.total_copies,
    COUNT(CASE WHEN l.status = 'on_loan' THEN l.id END) AS loaned_out_copies,
    (b.total_copies - COUNT(CASE WHEN l.status = 'on_loan' THEN l.id END)) AS available_copies
FROM 
    tblbooks b
LEFT JOIN 
    tbllans l ON b.id = l.book_id
GROUP BY 
    b.id, b.title, b.author, b.isbn, b.total_copies
ORDER BY 
    b.title ASC;
";

$query = $dbh->prepare($sql);
$query->execute();
$books = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OLMS || View Books</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap & Theme CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
    body {
        font-family: 'Heebo', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
    }

    .main-content {
        margin-left: 250px;
        margin-top: 20px;
        margin-right: 20px;
        padding: 30px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        min-height: 90vh;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        border: 1px solid #dee2e6;
    }

    th {
        background-color: #e9ecef;
    }

    tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .back-btn {
        background-color: #0d6efd;
        color: #fff;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 5px;
    }

    .back-btn:hover {
        background-color: #0056b3;
    }

    h6 {
        margin: 0;
        font-weight: 600;
    }
</style>

</head>
<body>

    <?php include(__DIR__ . '/includes/header.php'); ?>
    <?php include(__DIR__ . '/includes/sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="text-center flex-grow-1">Available Books in Library</h6>
            <span></span>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Total Copies</th>
                    <th>Loaned Out</th>
                    <th>Available Copies</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->rowCount() > 0): ?>
                    <?php $cnt = 1; foreach ($books as $row): ?>
                        <tr>
                            <td><?= htmlentities($cnt++); ?></td>
                            <td><?= htmlentities($row['title']); ?></td>
                            <td><?= htmlentities($row['author']); ?></td>
                            <td><?= htmlentities($row['isbn']); ?></td>
                            <td class="text-center"><?= htmlentities($row['total_copies']); ?></td>
                            <td class="text-center"><?= htmlentities($row['loaned_out_copies']); ?></td>
                            <td class="text-center"><?= htmlentities($row['available_copies']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No books found in the library.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include(__DIR__ . '/includes/footer.php'); ?>
    

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
