<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

/**
 * LOGIC: 6-Month Predictive Data Extraction
 * Fetches Visit Volume, Revenue, and calculates a growth trend using SQL.
 */
$sql = "SELECT 
            MONTHNAME(v.visit_date) AS m_name, 
            YEAR(v.visit_date) AS v_year,
            COUNT(v.visit_id) AS visit_count,
            SUM(v.consultation_fee + v.lab_fee) AS total_revenue,
            AVG(v.consultation_fee + v.lab_fee) AS avg_ticket
        FROM visits v 
        WHERE v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(v.visit_date), MONTH(v.visit_date)
        ORDER BY v.visit_date ASC"; // ASC for correct Chart flow

$res = mysqli_query($conn, $sql);

// Prepare data arrays for Chart.js
$labels = [];
$visitData = [];
$revenueData = [];

$tableRows = [];
while($row = mysqli_fetch_assoc($res)) {
    $labels[] = $row['m_name'];
    $visitData[] = $row['visit_count'];
    $revenueData[] = $row['total_revenue'];
    $tableRows[] = $row; // Store for table display
}
// Reverse table for "Latest Month First" display
$tableRows = array_reverse($tableRows);
?>

<style>
    :root { --neon-blue: #00d2ff; --neon-purple: #9d50bb; }
    .stat-card {
        background: white;
        border-radius: 15px;
        border: none;
        transition: all 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .table-glass { background: #fff; border-radius: 15px; overflow: hidden; }
    .status-indicator { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .bg-pulse-success { background: #28a745; box-shadow: 0 0 10px #28a745; animation: blink 2s infinite; }
    @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Clinic Performance Intelligence</h1>
            <p class="text-muted mb-0"><span class="status-indicator bg-pulse-success"></span> Live System Engine | Predictive SQL Analysis</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4" onclick="window.print()">
            <i class="fas fa-file-export me-2"></i> Export Intel
        </button>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card stat-card shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Revenue & Visit Growth Trend</h5>
                    <select class="form-select form-select-sm w-auto">
                        <option>Last 6 Months</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="analyticsChart"></canvas>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card shadow-sm p-3 border-start border-primary border-4">
                        <small class="text-muted d-block">Peak Performance</small>
                        <span class="h4 fw-bold"><?= max($visitData) ?> Visits</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card shadow-sm p-3 border-start border-success border-4">
                        <small class="text-muted d-block">Revenue Ceiling</small>
                        <span class="h4 fw-bold">$<?= number_format(max($revenueData), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card table-glass shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Temporal Data Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr class="small text-uppercase">
                                <th class="ps-4">Cycle</th>
                                <th>Load</th>
                                <th class="text-end pe-4">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tableRows as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= $row['m_name'] ?></div>
                                    <small class="text-muted"><?= $row['v_year'] ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border"><?= $row['visit_count'] ?> pts</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="fw-bold">$<?= number_format($row['total_revenue'], 2) ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">AVG: $<?= number_format($row['avg_ticket'], 0) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('analyticsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Total Revenue ($)',
            data: <?= json_encode($revenueData) ?>,
            borderColor: '#9d50bb',
            backgroundColor: 'rgba(157, 80, 187, 0.1)',
            fill: true,
            tension: 0.4,
            yAxisID: 'y',
        }, {
            label: 'Visit Count',
            data: <?= json_encode($visitData) ?>,
            type: 'bar',
            backgroundColor: 'rgba(0, 210, 255, 0.2)',
            borderColor: '#00d2ff',
            borderWidth: 1,
            yAxisID: 'y1',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, type: 'linear', position: 'left' },
            y1: { beginAtZero: true, type: 'linear', position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>