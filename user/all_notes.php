<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/includes/dbconnection.php');

// Fetch notes and department info
$sql = "
       SELECT 
        d.name AS department_name,
        d.description,
        n.File1, n.File2, n.File3, n.File4
    FROM 
        tblnotes n
    JOIN 
        tbldepartments d ON n.id = d.id
    ORDER BY 
        d.name ASC
";

$query = $dbh->prepare($sql);
$query->execute();
$notes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ONSS || All Shared Notes</title>

    <!-- Import styles like in signin.php -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Heebo', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .btn-download {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="text-primary mb-4"><i class="fas fa-file-pdf me-2"></i>All Shared Notes</h3>

    <?php if (empty($notes)): ?>
        <div class="alert alert-info">No notes have been uploaded yet.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Description</th>
                    <th>Note Files</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <?php for ($i = 1; $i <= 4; $i++): 
                                $fileCol = "File$i";
                                if (!empty($row[$fileCol])): 
                                    $filePath = "notes_files/" . htmlspecialchars($row[$fileCol]);
                            ?>
                                <a class="btn btn-sm btn-outline-primary btn-download" href="<?= $filePath ?>" target="_blank" download>
                                    <i class="fas fa-download"></i> File<?= $i ?>
                                </a>
                            <?php 
                                endif;
                            endfor; 
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
