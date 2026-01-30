<?php
include '../includes/auth_check.php';
include '../config/db.php';
include '../includes/header.php';

// ID SYNC & SECURITY
if (isPatient()) {
    // Patients are locked to their own unique ID via session
    $id = (int)$_SESSION['patient_id']; 
} else {
    // Admin/Staff can view any patient passed in the URL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if (isset($_GET['search_name']) && !isPatient()) {
    $name = mysqli_real_escape_string($conn, $_GET['search_name']);
    $search_sql = "SELECT patient_id FROM patients WHERE name LIKE '%$name%' LIMIT 1";
    $search_res = mysqli_query($conn, $search_sql);
    if ($found = mysqli_fetch_assoc($search_res)) {
        header("Location: view.php?id=" . $found['patient_id']);
        exit();
    }
}

if ($id === 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Clinical Error: No valid record selected.</div></div>";
    include '../includes/footer.php';
    exit();
}

$sql = "SELECT p.*, 
        TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age_yrs,
        CONCAT(TIMESTAMPDIFF(YEAR, dob, CURDATE()), 'y ', 
               TIMESTAMPDIFF(MONTH, dob, CURDATE()) % 12, 'm') AS age_full,
        (SELECT SUM(consultation_fee + lab_fee) FROM visits WHERE patient_id = p.patient_id) as total_spent,
        (SELECT MAX(visit_date) FROM visits WHERE patient_id = p.patient_id) as last_visit,
        (SELECT follow_up_due FROM visits WHERE patient_id = p.patient_id ORDER BY visit_date DESC LIMIT 1) as next_due,
        CASE 
            WHEN (SELECT follow_up_due FROM visits WHERE patient_id = p.patient_id ORDER BY visit_date DESC LIMIT 1) < CURDATE() THEN 'CRITICAL'
            WHEN (SELECT follow_up_due FROM visits WHERE patient_id = p.patient_id ORDER BY visit_date DESC LIMIT 1) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'CAUTION'
            ELSE 'STABLE'
        END AS clinical_status
        FROM patients p WHERE p.patient_id = $id";

$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "<div class='container mt-4'><div class='alert alert-warning'>Clinical record not found.</div></div>";
    include '../includes/footer.php';
    exit();
}

$statusColor = ($data['clinical_status'] == 'CRITICAL') ? '#d93025' : (($data['clinical_status'] == 'CAUTION') ? '#f29900' : '#1e8e3e');
?>

<style>
    .profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
    .patient-card { background: #fff; padding: 25px; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .risk-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; color: #fff; font-weight: bold; font-size: 0.8rem; margin-top: 10px; }
    .data-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
    .data-label { color: #666; font-weight: 500; }
    .data-value { font-weight: bold; color: #333; }
    .action-btn { display: inline-block; background: #1a73e8; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 8px; margin-top: 20px; transition: 0.3s; font-weight: 600; text-align: center; border: none; }
    .action-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .secondary-btn { background: #f8f9fa; color: #333; border: 1px solid #ddd; }
    .critical-box { margin-top: 25px; padding: 20px; border-left: 4px solid #d93025; background: #fff5f5; border-radius: 0 8px 8px 0; }
</style>

<div class="container py-4">
    <div class="profile-grid">
        <div class="patient-card" style="border-top: 5px solid <?= $statusColor ?>;">
            <h2 style="margin-bottom: 5px;"><?= htmlspecialchars($data['name']) ?></h2>
            <small class="text-muted">Clinical Identifier: #<?= $data['patient_id'] ?></small>
            <br>
            <div class="risk-badge" style="background: <?= $statusColor ?>;">
                <i class="fas fa-heartbeat"></i> <?= $data['clinical_status'] ?>
            </div>
            
            <div style="margin-top: 25px;">
                <div class="data-row"><span class="data-label">Clinical Age</span><span class="data-value"><?= $data['age_full'] ?></span></div>
                <div class="data-row"><span class="data-label">Phone</span><span class="data-value"><?= $row['phone'] ?? $data['phone'] ?></span></div>
                <div class="data-row"><span class="data-label">DOB</span><span class="data-value"><?= $data['dob'] ?></span></div>
            </div>

            <?php if(!isPatient()): ?>
                <a href="tel:<?= $data['phone'] ?>" class="action-btn w-100">
                    <i class="fas fa-phone-alt me-2"></i> Contact Patient
                </a>
            <?php endif; ?>
        </div>

        <div class="patient-card">
            <h4 style="color: #5f6368; border-bottom: 2px solid #1a73e8; padding-bottom: 10px;">Clinical Insight</h4>
            
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">Lifetime Revenue</small>
                        <span class="h4 fw-bold text-success">$<?= number_format($data['total_spent'] ?? 0, 2) ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="text-muted d-block mb-1">Last Engagement</small>
                        <span class="h4 fw-bold text-primary"><?= $data['last_visit'] ?? 'New Patient' ?></span>
                    </div>
                </div>
            </div>

            <div class="critical-box" style="border-left-color: <?= $statusColor ?>; background: <?= $statusColor ?>10;">
                <h5 style="margin-top: 0; color: <?= $statusColor ?>;">Follow-up Status</h5>
                <p class="h5 mb-2"><?= $data['next_due'] ?? 'No Future Visit' ?></p>
                <?php if($data['clinical_status'] == 'CRITICAL'): ?>
                    <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Patient is currently overdue for clinical review.</small>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="../visits/patient_visits.php?patient_id=<?= $id ?>" class="action-btn secondary-btn flex-grow-1">
                    <i class="fas fa-history me-2"></i> History
                </a>

                <?php if(!isPatient()): ?>
                    <a href="../visits/add.php?patient_id=<?= $id ?>" class="action-btn flex-grow-1">
                        <i class="fas fa-plus me-2"></i> Record Visit
                    </a>
                <?php elseif($id === (int)$_SESSION['patient_id']): ?>
                    <a href="../visits/add.php?patient_id=<?= $id ?>" class="action-btn flex-grow-1" style="background-color: #1e8e3e;">
                        <i class="fas fa-calendar-plus me-2"></i> Book My Visit
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>