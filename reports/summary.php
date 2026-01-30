<?php 
include '../config/db.php';
include '../includes/header.php';


$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) && $_GET['sort'] == 'desc' ? 'DESC' : 'ASC';


$count_query = "SELECT COUNT(*) AS total FROM patients WHERE name LIKE '%$search%'";
$count_res = mysqli_query($conn, $count_query);
$total_patients = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_patients / $limit);


$sql = "SELECT 
            p.name, 
            p.phone,
            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age_yrs,
            CONCAT(TIMESTAMPDIFF(YEAR, p.dob, CURDATE()), 'y ', 
                   TIMESTAMPDIFF(MONTH, p.dob, CURDATE()) % 12, 'm') AS age_full,
            COUNT(v.visit_id) AS total_visits,
            MAX(v.visit_date) AS last_visit,
            DATEDIFF(CURDATE(), MAX(v.visit_date)) AS days_since,
            DATE_ADD(MAX(v.visit_date), INTERVAL 7 DAY) AS next_due,
            CASE 
                WHEN DATE_ADD(MAX(v.visit_date), INTERVAL 7 DAY) < CURDATE() THEN 'Overdue'
                WHEN DATE_ADD(MAX(v.visit_date), INTERVAL 7 DAY) = CURDATE() THEN 'Due Today'
                WHEN MAX(v.visit_date) IS NULL THEN 'No History'
                ELSE 'Healthy'
            END AS health_status
        FROM patients p
        LEFT JOIN visits v ON p.patient_id = v.patient_id
        WHERE p.name LIKE '%$search%'
        GROUP BY p.patient_id
        ORDER BY p.name $sort
        LIMIT $limit OFFSET $offset";

$res = mysqli_query($conn, $sql);
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold"><i class="fas fa-file-medical-alt text-primary me-2"></i>Executive Summary</h2>
    </div>
    <div class="col-md-6">
        <form method="GET" class="row g-2 justify-content-end">
            <div class="col-auto">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="asc" <?= $sort == 'ASC' ? 'selected' : '' ?>>Sort: A-Z</option>
                    <option value="desc" <?= $sort == 'DESC' ? 'selected' : '' ?>>Sort: Z-A</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search name..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <?php if($search): ?>
                <div class="col-auto">
                    <a href="summary.php" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Patient Information</th>
                        <th>Age (Full)</th>
                        <th>Visit Stats</th>
                        <th>Last Interaction</th>
                        <th>Follow-up Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($res) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $statusColor = 'secondary';
                            if($row['health_status'] == 'Overdue') $statusColor = 'danger';
                            if($row['health_status'] == 'Due Today') $statusColor = 'warning text-dark';
                            if($row['health_status'] == 'Healthy') $statusColor = 'success';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                <small class="text-muted"><?= $row['phone'] ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $row['age_yrs'] ?> Years</div>
                                <small class="text-muted small"><?= $row['age_full'] ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= $row['total_visits'] ?> Total Visits
                                </span>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= $row['last_visit'] ?? '---' ?></div>
                                <small class="text-muted"><?= isset($row['days_since']) ? $row['days_since'].' days ago' : 'New Patient' ?></small>
                            </td>
                            <td>
                                <div class="badge bg-<?= $statusColor ?> px-3 py-2 rounded-pill">
                                    <?= $row['health_status'] ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted">No patients found matching your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer bg-white py-3">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= strtolower($sort) ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= strtolower($sort) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= strtolower($sort) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php include '../includes/footer.php'; ?>