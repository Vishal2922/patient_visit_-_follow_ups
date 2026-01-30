<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

// 1. Secure Input
$p_id = isset($_GET['patient_id']) ? mysqli_real_escape_string($conn, $_GET['patient_id']) : 0;

// Calculates Total Visits, Lifetime Fees, and Treatment Span entirely in SQL
$summary_sql = "SELECT 
                    COUNT(visit_id) as total_count,
                    SUM(consultation_fee + lab_fee) as lifetime_fees,
                    DATEDIFF(MAX(visit_date), MIN(visit_date)) as span_days,
                    MIN(visit_date) as first_visit,
                    MAX(visit_date) as last_visit
                FROM visits 
                WHERE patient_id = $p_id";
$summary_res = mysqli_fetch_assoc(mysqli_query($conn, $summary_sql));

//  Detailed History with Interval Tracking
// SQL logic: Find days passed since the previous visit for clinical continuity
$list_sql = "SELECT v.*, p.name as patient_name,
             DATEDIFF(v.visit_date, (SELECT MAX(v2.visit_date) FROM visits v2 WHERE v2.patient_id = v.patient_id AND v2.visit_date < v.visit_date)) as days_since_prev
             FROM visits v
             JOIN patients p ON v.patient_id = p.patient_id
             WHERE v.patient_id = $p_id 
             ORDER BY v.visit_date DESC";
$list_res = mysqli_query($conn, $list_sql);
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM patients WHERE patient_id = $p_id"));
?>

<style>
    .history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .stat-card { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .stat-card h4 { margin: 0 0 10px 0; font-size: 0.85rem; color: #666; text-transform: uppercase; }
    .stat-card p { margin: 0; font-size: 1.5rem; font-weight: bold; color: #1a73e8; }
    
    .timeline-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
    .timeline-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; }
    .timeline-table td { padding: 15px; border-bottom: 1px solid #eee; }
    .interval-badge { font-size: 0.75rem; background: #e8f0fe; color: #1967d2; padding: 4px 8px; border-radius: 12px; }
    .fee-total { font-weight: bold; color: #2e7d32; }
</style>

<div class="history-header">
    <h2>Medical Timeline: <?= htmlspecialchars($patient['name']) ?></h2>
    <a href="add.php?patient_id=<?= $p_id ?>" style="text-decoration: none; background: #1a73e8; color: #fff; padding: 10px 20px; border-radius: 5px;">+ Record New Visit</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h4>Total Encounters</h4>
        <p><?= $summary_res['total_count'] ?></p>
    </div>
    <div class="stat-card">
        <h4>Treatment Span</h4>
        <p><?= $summary_res['span_days'] ?> <span style="font-size: 0.9rem; font-weight: normal;">Days</span></p>
    </div>
    <div class="stat-card">
        <h4>Lifetime Revenue</h4>
        <p>$<?= number_format($data['total_spent'] ?? 0, 2) ?></p>
    </div>
</div>

<table class="timeline-table">
    <thead>
        <tr>
            <th>Date & Context</th>
            <th>Billing (Cons. + Lab)</th>
            <th>Clinical Continuity</th>
            <th>Next Scheduled</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($list_res)): ?>
        <tr>
            <td>
                <strong><?= $row['visit_date'] ?></strong><br>
                <small style="color: #666;">Encounter #<?= $row['visit_id'] ?></small>
            </td>
            <td>
                <span class="fee-total">$<?= number_format($row['consultation_fee'] + $row['lab_fee'], 2) ?></span><br>
                <small style="color: #888;">$<?= $row['consultation_fee'] ?> + $<?= $row['lab_fee'] ?></small>
            </td>
            <td>
                <?php if($row['days_since_prev']): ?>
                    <span class="interval-badge"><?= $row['days_since_prev'] ?> days since last visit</span>
                <?php else: ?>
                    <span class="interval-badge" style="background: #e6fffa; color: #234e52;">Initial Consultation</span>
                <?php endif; ?>
            </td>
            <td>
                <span style="color: #d93025; font-weight: bold;"><?= $row['follow_up_due'] ?></span>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>