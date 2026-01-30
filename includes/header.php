<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// This ensures links work from both the root and subfolders.
 
$base_path = (file_exists('index.php')) ? '' : '../';
$root_url = "/project_root/"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health+ | Secure Healthcare Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hc-primary: #0d6efd; --hc-secondary: #6c757d; }
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; transition: color 0.2s; }
        .container-main { min-height: 85vh; padding-top: 2rem; padding-bottom: 3rem; }
        .user-badge { font-size: 0.75rem; vertical-align: middle; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand text-primary" href="<?= $root_url ?><?= (isset($_SESSION['role']) && $_SESSION['role'] == 'patient') ? 'patients/view.php' : 'index.php' ?>">
            <i class="fas fa-notes-medical me-2"></i>HEALTH+
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hcNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="hcNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] !== 'patient'): ?>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= $root_url ?>index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= $root_url ?>patients/list.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= $root_url ?>visits/list.php">Visits</a></li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3" href="#" id="reportDrop" role="button" data-bs-toggle="dropdown">Reports</a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="<?= $root_url ?>reports/summary.php">Full Summary</a></li>
                            <li><a class="dropdown-item" href="<?= $root_url ?>reports/followups.php">Follow-ups</a></li>
                            <li><a class="dropdown-item" href="<?= $root_url ?>reports/birthdays.php">Birthdays</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $root_url ?>reports/monthly.php">Monthly Analytics</a></li>
                        </ul>
                    </li>

                    <li class="nav-item ms-lg-3 me-3">
                        <a href="<?= $root_url ?>visits/add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                            <i class="fas fa-plus me-1"></i> New Visit
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= $root_url ?>patients/view.php">My Profile</a></li>
                <?php endif; ?>

                <li class="nav-item border-start ps-lg-3 ms-lg-2 d-flex align-items-center">
                    <div class="text-white-50 me-3 small d-none d-xl-block">
                        <i class="fas fa-user-circle me-1"></i> 
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                        <span class="badge bg-primary user-badge ms-1"><?= strtoupper($_SESSION['role'] ?? '') ?></span>
                    </div>
                    <a href="<?= $root_url ?>logout.php" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Sign out from Health+?')">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container container-main">