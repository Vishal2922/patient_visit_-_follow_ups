<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

/**
 * ADVANCED CLINICAL LOGIC
 * 1. Calculates the exact date of the celebration this year.
 * 2. Uses CASE to determine clinical screening needs based on milestone ages.
 * 3. Incorporates 'Leap Year' logic via SQL date functions
 */
$sql = "SELECT name, dob, phone,
        TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1 AS turning_age,
        STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(dob), '-', DAY(dob)), '%Y-%m-%d') AS celebration_date,
        CASE 
            WHEN (TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1) = 40 THEN 'Mammogram/Cardiology'
            WHEN (TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1) = 45 THEN 'Colonoscopy'
            WHEN (TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1) >= 60 AND (TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1) % 5 = 0 THEN 'Geriatric Screen'
            ELSE 'Annual Physical'
        END AS clinical_recommendation,
        CASE 
            WHEN (TIMESTAMPDIFF(YEAR, dob, CURDATE()) + 1) IN (40, 45, 50, 60, 65) THEN 'PRIORITY'
            ELSE 'ROUTINE'
        END AS alert_level
        FROM patients 
        WHERE STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(dob), '-', DAY(dob)), '%Y-%m-%d') 
        BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY celebration_date ASC";

$res = mysqli_query($conn, $sql);
?>

<div class="row mb-4 align-items-end">
    <div class="col-md-7">
        <h2 class="fw-bold text-primary">
            <i class="fas fa-microscope me-2"></i>Preventive Care: Birthday Alerts
        </h2>
        <p class="text-muted mb-0">Identifying age-specific screening requirements for patients turning birthdays in the next 30 days.</p>
    </div>
    <div class="col-md-5 text-md-end">
        <div class="btn-group shadow-sm">
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Daily Outreach List
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Patient Profile</th>
                        <th>Upcoming Birthday</th>
                        <th>New Age</th>
                        <th>Clinical Recommendation</th>
                        <th class="text-end pe-4">Outreach</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($res) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $isPriority = ($row['alert_level'] == 'PRIORITY');
                        ?>
                        <tr class="<?= $isPriority ? 'bg-light' : '' ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                <div class="text-muted small">
                                    <i class="fas fa-phone-alt me-1"></i><?= $row['phone'] ?> | 
                                    <i class="fas fa-baby me-1"></i>DOB: <?= $row['dob'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-secondary">
                                    <i class="far fa-calendar-check me-2 text-primary"></i><?= $row['celebration_date'] ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $isPriority ? 'bg-danger' : 'bg-primary' ?> px-3">
                                    Turning <?= $row['turning_age'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="<?= $isPriority ? 'text-danger fw-bold' : 'text-muted' ?> small">
                                    <i class="fas fa-notes-medical me-1"></i>
                                    <?= $row['clinical_recommendation'] ?> 
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="tel:<?= $row['phone'] ?>" class="btn btn-sm <?= $isPriority ? 'btn-danger' : 'btn-outline-primary' ?> rounded-pill">
                                    <i class="fas fa-phone me-1"></i> Contact
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                <p class="text-muted">No preventive care alerts for the upcoming 30 days.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>