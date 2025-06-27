<?php
// admin_header.php needs to start the session and include auth_check.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth_check.php'; // Ensures only admins can access

// No need to include dbconnection.php again here if auth_check.php already includes it and makes $dbh available globally or via a return.
// Assuming $dbh is available after including auth_check.php. If not, uncomment the next line:
// require_once __DIR__ . '/../../includes/dbconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/onss/css/bootstrap.min.css"> <link rel="stylesheet" href="/onss/css/style.css">         <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        /* Google Font *//* --- Styles for Active Dashboard Cards --- */
.dashboard-card-link {
    text-decoration: none; /* Remove underline from the link */
    color: inherit; /* Inherit text color from parent */
    display: block; /* Make the link a block element so it fills the card */
    flex: 1; /* Allow flex item to grow/shrink */
    min-width: 200px; /* Maintain minimum width for cards */
}

.dashboard-card-link .dashboard-card {
    height: 100%; /* Ensure the card fills the link's height */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px; /* Ensure padding is consistent */
    
    /* Hover Effects */
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.dashboard-card-link:hover .dashboard-card {
    transform: translateY(-5px); /* Lifts the card slightly */
    box-shadow: 0 8px 16px rgba(0,0,0,0.2); /* Stronger shadow on hover */
    cursor: pointer; /* Indicates it's clickable */
}

/* Ensure text inside cards is centered */
.dashboard-card h2,
.dashboard-card p {
    text-align: center;
    width: 100%;
}    


        /* Basic Admin CSS (you can move this to your style.css or a new admin.css) */
        body { font-family: 'Heebo', sans-serif; margin: 0; background-color: #f4f4f4; }
        .admin-nav { background-color: #007bff; color: white; padding: 10px 0; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .admin-nav a { color: white; padding: 10px 20px; text-decoration: none; display: inline-block; font-weight: 500; }
        .admin-nav a:hover { background-color: #0056b3; }
        .admin-container { padding: 20px; max-width: 1200px; margin: auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px;}
        h1, h2 { color: #333; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px 15px; text-align: left; }
        th { background-color: #f2f2f2; color: #555; }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
            font-size: 0.9em;
        }
        .btn-danger { background-color: #dc3545; }
        .btn-success { background-color: #28a745; }
        .btn:hover { opacity: 0.9; }
        .dashboard-card {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
            text-align: center;
            flex: 1;
            margin: 10px;
            min-width: 200px;
        }
        .dashboard-card h2 {
            margin-top: 0;
            font-size: 1.2em;
            color: #555;
        }
        .dashboard-card p {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0 0;
        }
        .card-blue { color: #007bff; }
        .card-green { color: #28a745; }
        .card-orange { color: #ffc107; }
        .overdue-table tr { background-color: #ffebe6; } /* Light red for overdue rows */
        .overdue-table tr td { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-nav">
        
        <a href="/onss/admin/index.php">Admin Dashboard</a>
        <a href="/onss/admin/departments/index.php">Departments</a>
        <a href="/onss/admin/books/index.php">Books</a>
        <a href="/onss/admin/loans/index.php">Loans</a>
        <a href="/onss/admin/users/index.php">Users</a>
        <!-- <a href="/onss/admin/locked_users.php"><i class="fas fa-lock text-warning me-1"></i>Locked Users</a> -->

<a href="/onss/admin/logout.php">Logout</a>    </div>
    <div class="admin-container">