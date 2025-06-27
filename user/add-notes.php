<?php
session_start();
//error_reporting(0); // You might want to enable this for debugging later
include('includes/dbconnection.php');
if (strlen($_SESSION['uid']==0)) {
  header('location:logout.php');
  } else{
    if(isset($_POST['submit']))
  {

$uid=$_SESSION['uid'];
// ADDED: Department ID
$departmentid = $_POST['department'];

$subject=$_POST['subject'];
$notestitle=$_POST['notestitle'];
$notesdesc=$_POST['notesdesc'];
$file1=$_FILES["file1"]["name"];

$extension1 = substr($file1,strlen($file1)-4,strlen($file1));
$file2=$_FILES["file2"]["name"];
$extension2 = substr($file2,strlen($file2)-4,strlen($file2));
$file3=$_FILES["file3"]["name"];
$extension3 = substr($file3,strlen($file3)-4,strlen($file3));
$file4=$_FILES["file4"]["name"];
$extension4 = substr($file4,strlen($file4)-4,strlen($file4));
$allowed_extensions = array("docs",".doc",".pdf");

if(!in_array($extension1,$allowed_extensions))
{
echo "<script>alert('File has Invalid format. Only docs / doc/ pdf format allowed');</script>";
}

// Keep other file checks commented out as per your original code
// if(!in_array($extension2,$allowed_extensions))
// {
// echo "<script>alert('File has Invalid format. Only docs / doc/ pdf format allowed');</script>";
// }

// if(!in_array($extension3,$allowed_extensions))
// {
// echo "<script>alert('File has Invalid format. Only docs / doc/ pdf format allowed');</script>";
// }

// if(!in_array($extension4,$allowed_extensions))
// {
// echo "<script>alert('File has Invalid format. Only docs / doc/ pdf format allowed');</script>";
// }

else {

    $file1=md5($file1.time()).$extension1; // Fixed: Use $file1 instead of $file
    if($file2!=''):
    $file2=md5($file2.time()).$extension2; endif; // Fixed: Use $file2
    if($file3!=''):
    $file3=md5($file3.time()).$extension3; endif; // Fixed: Use $file3
    if($file4!=''):
    $file4=md5($file4.time()).$extension4; endif; // Fixed: Use $file4
    move_uploaded_file($_FILES["file1"]["tmp_name"],"folder1/".$file1);
    move_uploaded_file($_FILES["file2"]["tmp_name"],"folder2/".$file2);
    move_uploaded_file($_FILES["file3"]["tmp_name"],"folder3/".$file3);
    move_uploaded_file($_FILES["file4"]["tmp_name"],"folder4/".$file4);

// MODIFIED: Added DepartmentID to insert query
$sql="insert into tblnotes(UserID,DepartmentID,Subject,NotesTitle,NotesDecription,File1,File2,File3,File4)values(:uid,:departmentid,:subject,:notestitle,:notesdesc,:file1,:file2,:file3,:file4)";
$query=$dbh->prepare($sql);
$query->bindParam(':uid',$uid,PDO::PARAM_STR);
$query->bindParam(':departmentid',$departmentid,PDO::PARAM_INT); // BIND DepartmentID
$query->bindParam(':subject',$subject,PDO::PARAM_STR);
$query->bindParam(':notestitle',$notestitle,PDO::PARAM_STR);
$query->bindParam(':notesdesc',$notesdesc,PDO::PARAM_STR);
$query->bindParam(':file1',$file1,PDO::PARAM_STR);
$query->bindParam(':file2',$file2,PDO::PARAM_STR);
$query->bindParam(':file3',$file3,PDO::PARAM_STR);
$query->bindParam(':file4',$file4,PDO::PARAM_STR);

  $query->execute();

    $LastInsertId=$dbh->lastInsertId();
    if ($LastInsertId>0) {
    echo '<script>alert("Notes has been added.")</script>';
echo "<script>window.location.href ='add-notes.php'</script>";
  }
  else
    {
      echo '<script>alert("Something Went Wrong. Please try again")</script>';
    }

  
}

}
// MODIFIED: Changed DepartmentName to 'name'
$sql_departments = "SELECT ID, name AS DepartmentName FROM tbldepartments ORDER BY name ASC"; // Use 'name' and alias it as DepartmentName for consistency with object property
$query_departments = $dbh->prepare($sql_departments);
$query_departments->execute();
$departments = $query_departments->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ONSS || Add Notes</title>
 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">
    <script>
function getSubject(val) { 
    //alert(val);
  $.ajax({
type:"POST",
url:"get-subject.php",
data:'subid='+val,
success:function(data){
$("#subject").html(data);
}});
}
  </script>
</head>

<body>
    <div class="container-fluid position-relative bg-white d-flex p-0">
        
<?php include_once('includes/sidebar.php');?>


        <div class="content">
          <?php include_once('includes/header.php');?>


            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Add Notes</h6>
                            <form method="post" enctype="multipart/form-data">
                                
                                

                                <br />
                                
                                <div class="mb-3">
                                    <label for="department" class="form-label">Select Department</label>
                                    <select class="form-select" id="department" name="department" required='true'>
                                        <option value="">Select Department</option>
                                        <?php
                                        if ($departments) {
                                            foreach ($departments as $department) {
                                                // Access using 'DepartmentName' alias
                                                echo '<option value="' . htmlentities($department->ID) . '">' . htmlentities($department->DepartmentName) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">Notes Title</label>
                                    <input type="text" class="form-control"  name="notestitle" value="" required='true'>

                                  
                                </div>
                                  <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">Subject</label>
                                    <input type="text" class="form-control"  name="subject" value="" required='true'>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">Notes Description</label>
                                    <textarea class="form-control"  name="notesdesc" value="" required='true'></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">Upload File</label>
                                   <input type="file" class="form-control"  name="file1" value="" required='true'>

                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">More File</label>
                                   <input type="file" class="form-control"  name="file2" value="">
                                    
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">More File</label>
                                   <input type="file" class="form-control"  name="file3" value="" >
                                    
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail2" class="form-label">More File</label>
                                   <input type="file" class="form-control"  name="file4" value="" >
                                    
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once('includes/footer.php');?>
        </div>
        <?php include_once('includes/back-totop.php');?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <script src="js/main.js"></script>
</body>
</html>
<?php }  ?>