<?php
session_start();
include('../includes/dbconnection.php'); // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    try {
        $sql = "UPDATE tbluser SET failed_attempts = 0, locked_until = NULL WHERE ID = :id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['msg'] = "✅ User has been successfully unlocked.";
    } catch (PDOException $e) {
        $_SESSION['msg'] = "❌ Error unlocking user: " . htmlspecialchars($e->getMessage());
    }
}

header("Location: index.php"); // Redirect back to user list
exit();
