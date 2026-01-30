<?php
include 'config/db.php';
include 'includes/header.php';
include 'includes/auth_check.php';

//  Advanced Quick Stats using SQL 
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM patients) as total_patients,
    (SELECT COUNT(*) FROM visits WHERE follow_up_due < CURDATE()) as overdue_count,
    (SELECT COUNT(*) FROM patients WHERE patient_id NOT IN (SELECT DISTINCT patient_id FROM visits)) as never_visited,
    (SELECT COUNT(*) FROM patients p WHERE NOT EXISTS (SELECT 1 FROM visits v WHERE v.patient_id = p.patient_id AND v.visit_date > DATE_SUB(CURDATE(), INTERVAL 180 DAY))) as inactive_180";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_sql));

// Birthday Alerts with SQL age calculation
$bday_sql = "SELECT name, dob, TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1 AS turning_age
             FROM patients 
             WHERE DATE_FORMAT(dob, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
             AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
             ORDER BY DATE_FORMAT(dob, '%m-%d') ASC";
$bdays = mysqli_query($conn, $bday_sql);

//  Recent Activity Feed
$recent_visits = mysqli_query($conn, "SELECT p.name, v.visit_date, v.consultation_fee 
                                      FROM visits v JOIN patients p ON v.patient_id = p.patient_id 
                                      ORDER BY v.visit_date DESC LIMIT 5");
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-secondary">Clinic Overview</h2>
        <p class="text-muted">Real-time healthcare metrics based on SQL calculations.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between px-md-1">
                    <div><h3 class="fw-bold"><?= $stats['total_patients'] ?></h3><p class="mb-0">Total Patients</p></div>
                    <div class="align-self-center"><i class="fas fa-users fa-2x opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between px-md-1">
                    <div><h3 class="fw-bold"><?= $stats['overdue_count'] ?></h3><p class="mb-0">Overdue Follow-ups</p></div>
                    <div class="align-self-center"><i class="fas fa-exclamation-triangle fa-2x opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between px-md-1">
                    <div><h3 class="fw-bold"><?= $stats['inactive_180'] ?></h3><p class="mb-0">Inactive (180+ Days)</p></div>
                    <div class="align-self-center"><i class="fas fa-user-clock fa-2x opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between px-md-1">
                    <div><h3 class="fw-bold"><?= $stats['never_visited'] ?></h3><p class="mb-0">Never Visited</p></div>
                    <div class="align-self-center"><i class="fas fa-user-slash fa-2x opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-birthday-cake text-primary me-2"></i>Upcoming Birthdays</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if(mysqli_num_rows($bdays) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($bdays)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><?= htmlspecialchars($row['name']) ?><br><small class="text-muted"><?= $row['dob'] ?></small></div>
                                <span class="badge bg-info rounded-pill">Turning <?= $row['turning_age'] ?></span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No birthdays in the next 30 days.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-history text-secondary me-2"></i>Recent Patient Activity</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Patient</th><th>Date</th><th>Fee</th></tr></thead>
                    <tbody>
                        <?php while($visit = mysqli_fetch_assoc($recent_visits)): ?>
                            <tr>
                                <td><?= htmlspecialchars($visit['name']) ?></td>
                                <td><?= $visit['visit_date'] ?></td>
                                <td>$<?= number_format($visit['consultation_fee'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-primary">
            <div class="card-body d-grid gap-3 align-content-center">
                <h5 class="text-center mb-3 fw-bold">Quick Actions</h5>
                <a href="patients/add.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>New Patient</a>
                <a href="visits/add.php" class="btn btn-outline-success"><i class="fas fa-notes-medical me-2"></i>Log Visit</a>
                <a href="reports/followups.php" class="btn btn-outline-danger"><i class="fas fa-calendar-alt me-2"></i>Follow-ups</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>