<?php
include '../config/db.php';
include '../includes/auth_check.php';
if (!isAdmin()) {
    die("Access Denied: Admin role required for deletion.");
}

// Get the ID and force it to be a number
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {

    $sql = "DELETE FROM visits WHERE visit_id = $id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: list.php?msg=deleted");
        exit();
    } else {
        // If the query fails, show the MySQL error
        die("MySQL Error: " . mysqli_error($conn));
    }
} else {
    // If no ID was passed, just go back to the list
    header("Location: list.php");
    exit();
}
?>