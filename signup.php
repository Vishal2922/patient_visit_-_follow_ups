<?php
include 'config/db.php';

$error = "";
$input = ['username' => '', 'name' => '', 'phone' => '', 'dob' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input['username'] = mysqli_real_escape_string($conn, $_POST['username']);
    $input['name'] = mysqli_real_escape_string($conn, $_POST['name']);
    $input['phone'] = mysqli_real_escape_string($conn, $_POST['phone']);
    $input['dob'] = mysqli_real_escape_string($conn, $_POST['dob']);
    $input['address'] = mysqli_real_escape_string($conn, $_POST['address']);
    
    // NEW: Hash the password
    $plain_password = $_POST['password']; 
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    
    $today = date('Y-m-d');

    // Validation
    $check_user = mysqli_query($conn, "SELECT username FROM users WHERE username = '{$input['username']}'");
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (mysqli_num_rows($check_user) > 0) {
        $error = "Error: Username already taken.";
    } elseif (!preg_match($password_pattern, $plain_password)) {
        $error = "Security Error: Password must be 8+ characters with Upper, Lower, Number, and Symbol.";
    } else {
        // INSERT Patient
        $p_sql = "INSERT INTO patients (name, phone, dob, address, join_date) 
                  VALUES ('{$input['name']}', '{$input['phone']}', '{$input['dob']}', '{$input['address']}', '$today')";
        
        if (mysqli_query($conn, $p_sql)) {
            $new_patient_id = mysqli_insert_id($conn);

            // Use the $hashed_password here
            $u_sql = "INSERT INTO users (username, password, role, patient_id) 
                      VALUES ('{$input['username']}', '$hashed_password', 'patient', $new_patient_id)";
            
            if (mysqli_query($conn, $u_sql)) {
                header("Location: login.php?msg=signup_success");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Registration | Health-Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .signup-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .section-title { font-size: 0.9rem; font-weight: bold; color: #0d6efd; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body class="py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card signup-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-clinic-medical fa-3x text-primary mb-2"></i>
                        <h3 class="fw-bold text-dark">Patient Registration</h3>
                        <p class="text-muted small">Your unique ID will be synced with your medical records</p>
                    </div>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger border-0 small shadow-sm mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="section-title mb-3 border-bottom pb-1">Account Credentials</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Username (Must be unique)</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($input['username']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted" style="font-size: 0.7rem;">Complexity: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special (@$!%*?&)</small>
                            </div>
                        </div>

                        <div class="section-title mb-3 border-bottom pb-1 mt-4">Personal Information</div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($input['name']) ?>" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($input['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($input['dob']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($input['address']) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="fas fa-user-plus me-2"></i> Create & Sync Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>