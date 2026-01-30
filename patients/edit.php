<?php 
include '../includes/auth_check.php'; 
include '../config/db.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = "";

// Fetch the clinical record AND linked user account credentials
$sql = "SELECT p.*, u.username, u.password 
        FROM patients p 
        LEFT JOIN users u ON p.patient_id = u.patient_id 
        WHERE p.patient_id = $id";
$current = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if (!$current) {
    header("Location: list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize Patient inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $join_date = mysqli_real_escape_string($conn, $_POST['join_date']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Sanitize Account inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 
    
    $today = date('Y-m-d');

    // 2. VALIDATION LOGIC
    $user_check = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$username' AND patient_id != $id");
    
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if ($dob > $today) {
        $error = "Clinical Error: Date of Birth cannot be in the future.";
    } elseif (mysqli_num_rows($user_check) > 0) {
        $error = "System Error: The username '$username' is already taken by another user.";
    } elseif (!preg_match($password_pattern, $password)) {
        $error = "Security Error: Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character (@$!%*?&).";
    } else {
        // Update Patients Table
        $update_p = "UPDATE patients SET 
                        name='$name', dob='$dob', join_date='$join_date', 
                        phone='$phone', address='$address' 
                     WHERE patient_id=$id";

        // Update Users Table
        $update_u = "UPDATE users SET 
                        username='$username', password='$password' 
                     WHERE patient_id=$id";

        if(mysqli_query($conn, $update_p) && mysqli_query($conn, $update_u)) {
            header("Location: list.php?msg=updated");
            exit();
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger shadow-sm border-start border-danger border-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Update Clinical & Portal Profile</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    
                    <div class="section-title mb-3 text-primary fw-bold border-bottom pb-2">
                        <i class="fas fa-key me-2"></i>Login Credentials
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($current['username']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password</label>
                            <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($current['password']) ?>" required>
                            <small class="text-muted" style="font-size: 0.75rem;">Requirement: 8+ chars, Upper, Lower, Number, Symbol.</small>
                        </div>
                    </div>

                    <div class="section-title mb-3 text-primary fw-bold border-bottom pb-2">
                        <i class="fas fa-id-card me-2"></i>Clinical Information
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($current['name']) ?>" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?= $current['dob'] ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Registration Date</label>
                            <input type="date" name="join_date" class="form-control" value="<?= $current['join_date'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($current['phone']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Residential Address</label>
                        <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($current['address']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-save me-2"></i>Save All Changes
                        </button>
                        <a href="list.php" class="btn btn-outline-secondary text-center">Discard Changes</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>