<?php 
include '../includes/auth_check.php';
include '../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize Patient Inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $join_date = mysqli_real_escape_string($conn, $_POST['join_date']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);


    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $today = date('Y-m-d');

    
    $user_check = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");

    
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (empty($name) || empty($username) || empty($password)) {
        $error = "Clinical Error: All fields, including Account Credentials, are mandatory.";
    } elseif (mysqli_num_rows($user_check) > 0) {
        $error = "System Error: The username '$username' is already taken.";
    } elseif (!preg_match($password_pattern, $password)) {
        $error = "Security Error: Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character (@$!%*?&).";
    } elseif ($dob > $today) {
        $error = "Clinical Error: Date of Birth cannot be in the future.";
    } else {

        $p_sql = "INSERT INTO patients (name, dob, join_date, phone, address) 
                  VALUES ('$name', '$dob', '$join_date', '$phone', '$address')";

        if (mysqli_query($conn, $p_sql)) {
            $new_id = mysqli_insert_id($conn);

            $u_sql = "INSERT INTO users (username, password, role, patient_id) 
                      VALUES ('$username', '$password', 'patient', $new_id)";

            if (mysqli_query($conn, $u_sql)) {
                header("Location: list.php?msg=new_patient_added");
                exit();
            } else {
                $error = "User Account Error: " . mysqli_error($conn);
            }
        } else {
            $error = "Patient Record Error: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger shadow-sm border-start border-danger border-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>New Patient & Portal Registration</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">

                    <div class="section-title mb-3 text-primary fw-bold border-bottom pb-2">
                        <i class="fas fa-key me-2"></i>Portal Credentials
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Login Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="Unique username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Login Password <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" required>
                            <div class="form-text text-muted" style="font-size: 0.75rem;">
                                Requirement: 8+ characters, A-Z, a-z, 0-9, and a symbol (@, $, !, etc.)
                            </div>
                        </div>
                    </div>

                    <div class="section-title mb-3 text-primary fw-bold border-bottom pb-2">
                        <i class="fas fa-id-card me-2"></i>Clinical Profile
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="dob" class="form-control" value="<?= $_POST['dob'] ?? '' ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Booking Date <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" class="form-control" value="<?= $_POST['join_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="555-0101" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Residential Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-save me-2"></i>Create Profile & Account
                        </button>
                        <a href="list.php" class="btn btn-link text-secondary text-decoration-none text-center">Cancel Registration</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>