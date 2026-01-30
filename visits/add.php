<?php 
include '../config/db.php'; 

$error = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $patient_id = (int)$_POST['patient_id'];
    $visit_date = mysqli_real_escape_string($conn, $_POST['visit_date']);
    $consultation_fee = mysqli_real_escape_string($conn, $_POST['consultation_fee']);
    $lab_fee = mysqli_real_escape_string($conn, $_POST['lab_fee']);
    $follow_up_due = mysqli_real_escape_string($conn, $_POST['follow_up_due']);
    
    $today = date('Y-m-d');


    if (empty($visit_date) || empty($consultation_fee) || empty($follow_up_due)) {
        $error = "Clinical Error: Visit Date, Consultation Fee, and Follow-up Date are mandatory.";
    } elseif ($visit_date > $today) {
        $error = "System Error: You cannot record a visit for a future date.";
    } elseif ($follow_up_due < $visit_date) {
        $error = "Clinical Error: Follow-up date cannot be earlier than the visit date.";
    } elseif (!is_numeric($consultation_fee) || !is_numeric($lab_fee)) {
        $error = "Financial Error: Fees must be numeric values.";
    } else {
    
        $sql = "INSERT INTO visits (patient_id, visit_date, consultation_fee, lab_fee, follow_up_due) 
                VALUES ($patient_id, '$visit_date', '$consultation_fee', '$lab_fee', '$follow_up_due')";
        
        if(mysqli_query($conn, $sql)) {
            // REDIRECT TO VISIT LOGS
            header("Location: list.php?msg=visit_recorded");
            exit(); 
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}

include '../includes/header.php'; 

$patients_res = mysqli_query($conn, "SELECT patient_id, name FROM patients ORDER BY name ASC");
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
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- Choose Patient --</option>
                            <?php while($p = mysqli_fetch_assoc($patients_res)): ?>
                                <option value="<?= $p['patient_id'] ?>" <?= (isset($_GET['patient_id']) && $_GET['patient_id'] == $p['patient_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
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

                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label fw-bold">Consultation Fee <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="consultation_fee" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">Lab Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="lab_fee" class="form-control" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Submit Encounter
                        </button>
                        <a href="list.php" class="btn btn-link text-secondary text-decoration-none text-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>