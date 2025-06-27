<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['uid']) == 0) {
    header('location:logout.php');
} else {
    if (isset($_GET['delid'])) {
        $rid = intval($_GET['delid']);
        $sql = "DELETE FROM tblnotes WHERE ID = :rid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rid', $rid, PDO::PARAM_STR);
        $query->execute();
        echo "<script>alert('Data deleted');</script>";
        echo "<script>window.location.href = 'manage-notes.php'</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>OLMS || Manage Notes</title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Other styles -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid position-relative bg-white d-flex p-0">
    <?php include_once('includes/sidebar.php'); ?>
    <div class="content">
        <?php include_once('includes/header.php'); ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-light text-center rounded p-4">
                <h6 class="mb-0 mb-3">Manage Notes</h6>
                <div class="table-responsive">
                    <table id="notesTable" class="table table-striped table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-dark">
                                <th>#</th>
                                <th>Department</th>
                                <th>Subject</th>
                                <th>Notes Title</th>
                                <th>Creation Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ocasuid = $_SESSION['uid'];
                            $sql = "SELECT tblnotes.*, tbldepartments.name AS DepartmentName 
                                    FROM tblnotes 
                                    JOIN tbldepartments ON tblnotes.DepartmentID = tbldepartments.ID 
                                    WHERE tblnotes.UserID = :uid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':uid', $ocasuid, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            $cnt = 1;
                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                                    $downloadUrl = "notes_files/" . $row->ID . ".pdf";
                            ?>
                            <tr>
                                <td><?php echo htmlentities($cnt); ?></td>
                                <td><?php echo htmlentities($row->DepartmentName); ?></td>
                                <td><?php echo htmlentities($row->Subject); ?></td>
                                <td><?php echo htmlentities($row->NotesTitle); ?></td>
                                <td><?php echo htmlentities($row->CreationDate); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="edit-notes.php?editid=<?php echo htmlentities($row->ID); ?>">View</a>
                                    <a class="btn btn-sm btn-danger" href="manage-notes.php?delid=<?php echo $row->ID; ?>" onclick="return confirm('Do you really want to Delete ?');">Delete</a>
                                </td>
                            </tr>
                            <?php
                                    $cnt++;
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">No notes found.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include_once('includes/footer.php'); ?>
    </div>
    <?php include_once('includes/back-totop.php'); ?>
</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/chart/chart.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="js/main.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#notesTable').DataTable({
        pageLength: 7,
        lengthChange: false,
        ordering: true,
        autoWidth: false
    });
});
</script>
</body>
</html>
<?php } ?>
