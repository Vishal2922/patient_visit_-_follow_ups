<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Authentication Check: Are you logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/**
 * AUTHORIZATION: Role-Based Access
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPatient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'patient';
}

// Example Authorization: Block patients from the Admin dashboard
if (basename($_SERVER['PHP_SELF']) == 'index.php' && isPatient()) {
    header("Location: patients/view.php?id=" . $_SESSION['patient_id']);
    exit();
}
?>