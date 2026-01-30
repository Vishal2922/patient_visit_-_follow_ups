<?php
include '../config/db.php';
include '../includes/auth_check.php';
if (!isAdmin()) {
    die("Access Denied: Admin role required for deletion.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {

    $sql = "DELETE FROM patients WHERE patient_id = $id";
    
    if (mysqli_query($conn, $sql)) {

        header("Location: list.php?msg=patient_deleted");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: list.php");
    exit();
}
?>