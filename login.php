<?php
session_start();
include 'config/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'patient') {
        header("Location: patients/view.php?id=" . $_SESSION['patient_id']);
    } else {
        header("Location: index.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']); 

    
    $query = "SELECT * FROM users WHERE username = '$user'";
    $res = mysqli_query($conn, $query);
    $u = mysqli_fetch_assoc($res);

    /**
     * THE MODIFIED CHECK
     * Directly comparing the input $pass with the database $u['password']
     */
    if ($u && $pass === $u['password']) {
        // Regenerate for security
        session_regenerate_id(true);

        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['role'] = $u['role'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['patient_id'] = $u['patient_id'] ?? null;

        // Redirect based on identity
        if ($u['role'] === 'patient') {
            header("Location: patients/view.php?id=" . $u['patient_id']);
        } else {
            header("Location: index.php");
        }
        exit(); 
    } else {
        $error = "The credentials provided do not match our medical records.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clinic Management | Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .login-card { border: none; border-radius: 15px; overflow: hidden; }
        .login-header { background: #0d6efd; color: white; padding: 2rem; text-align: center; }
        .btn-login { padding: 0.8rem; font-weight: bold; transition: 0.3s; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'signup_success'): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 text-center">
                    <i class="fas fa-check-circle me-2"></i> Registration successful!<br>Login with your new credentials.
                </div>
            <?php endif; ?>

            <div class="card login-card shadow-lg">
                <div class="login-header">
                    <i class="fas fa-hospital-user fa-3x mb-3"></i>
                    <h4 class="mb-0">Patient Management</h4>
                    <small>Secure Access Portal</small>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 small border-0 mb-4"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-login shadow-sm">
                            Sign In <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-center">
                    <p class="mb-0 small text-muted">New Patient? <a href="signup.php" class="fw-bold text-decoration-none">Create Account</a></p>
                </div>
            </div>
            <p class="text-center mt-4 text-muted small">&copy; 2026 Health-Sync Systems. All Rights Reserved.</p>
        </div>
    </div>
</div>

</body>
</html>