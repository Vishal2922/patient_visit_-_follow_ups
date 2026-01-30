<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//protected page - redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {

    $login_path = (file_exists('login.php')) ? 'login.php' : '../login.php';
    header("Location: $login_path");
    exit();
}


function isAdmin()
{
    return $_SESSION['role'] === 'admin';
}
function isPatient()
{
    return $_SESSION['role'] === 'patient';
}
