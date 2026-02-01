<?php 
// 1. SECURITY & CONFIG
include '../includes/auth_check.php'; 
include '../config/db.php'; 

$error = ""; 

// 2. IDENTITY LOCK: Force patient ID if the user is a patient
if (isPatient()) {
    $patient_id = (int)$_SESSION['patient_id'];
} else {
    // Admin can use the ID from the URL (from the "Record Encounter" button) or POST
    $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : (isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visit_date = mysqli_real_escape_string($conn, $_POST['visit_date']);
    $follow_up_due = mysqli_real_escape_string($conn, $_POST['follow_up_due']);
    $today = date('Y-m-d');

    // 3. FINANCIAL RESTRICTION (Only Admin sets fees)
    if (isAdmin()) {
        $consultation_fee = mysqli_real_escape_string($conn, $_POST['consultation_fee']);
        $lab_fee = mysqli_real_escape_string($conn, $_POST['lab_fee']);
    } else {
        $consultation_fee = 0;
        $lab_fee = 0;
    }

    if (empty($visit_date) || empty($follow_up_due)) {
        $error = "Clinical Error: Dates are mandatory.";
    } elseif ($visit_date > $today) {
        $error = "System Error: Cannot record future visits.";
    } else {
        $sql = "INSERT INTO visits (patient_id, visit_date, consultation_fee, lab_fee, follow_up_due) 
                VALUES ($patient_id, '$visit_date', '$consultation_fee', '$lab_fee', '$follow_up_due')";
        
        if(mysqli_query($conn, $sql)) {
            // REDIRECT FIX: Send user back to the specific patient's profile
            header("Location: ../patients/view.php?id=" . $patient_id . "&msg=visit_recorded");
            exit(); 
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 
// Fetch patients list only for Admins
$patients_res = !isPatient() ? mysqli_query($conn, "SELECT patient_id, name FROM patients ORDER BY name ASC") : null;
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger shadow-sm border-start border-danger border-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-header bg-success text-white py-3">
                <h4 class="mb-0"><i class="fas fa-file-medical me-2"></i>Record New Encounter</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    
                    <?php if(!isPatient()): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- Choose Patient --</option>
                            <?php while($p = mysqli_fetch_assoc($patients_res)): ?>
                                <option value="<?= $p['patient_id'] ?>" <?= ($patient_id == $p['patient_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info py-2 mb-3">
                            <i class="fas fa-user me-2"></i> Booking for: <strong><?= $_SESSION['username'] ?></strong>
                            <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">Visit Date <span class="text-danger">*</span></label>
                            <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Follow-up Due <span class="text-danger">*</span></label>
                            <input type="date" name="follow_up_due" class="form-control" required>
                        </div>
                    </div>

                    <?php if(isAdmin()): ?>
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label fw-bold">Consultation Fee ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="consultation_fee" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Lab Fee ($)</label>
                            <input type="number" step="0.01" name="lab_fee" class="form-control" value="0.00">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Submit Encounter
                        </button>
                        <a href="../patients/view.php?id=<?= $patient_id ?>" class="btn btn-link text-secondary text-decoration-none text-center">
                            Cancel and Return to Profile
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>