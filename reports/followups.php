<?php 
include '../config/db.php'; 
include '../includes/header.php'; 

// Pagination & Search Configuration
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and Sort inputs
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) && $_GET['sort'] == 'desc' ? 'DESC' : 'ASC';

// SQL for Total Count (Filtered by Search)
$count_query = "SELECT COUNT(*) AS total 
                FROM visits v 
                JOIN patients p ON v.patient_id = p.patient_id 
                WHERE p.name LIKE '%$search%' AND v.follow_up_due IS NOT NULL";
$count_res = mysqli_query($conn,$count_query);
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $limit);

/**
 * ADVANCED SQL LOGIC 
 * 1. DATEDIFF for real-time age of follow-up
 * 2. CASE logic for triage (Overdue vs. Upcoming) .
 * 3. Search and Sort integration.
 */
$sql = "SELECT p.name, p.phone, v.follow_up_due,
        DATEDIFF(v.follow_up_due, CURDATE()) AS days_diff,
        CASE 
            WHEN v.follow_up_due < CURDATE() THEN 'OVERDUE'
            WHEN v.follow_up_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'UPCOMING'
            ELSE 'FUTURE'
        END AS status
        FROM visits v 
        JOIN patients p ON v.patient_id = p.patient_id
        WHERE p.name LIKE '%$search%'
        ORDER BY p.name $sort
        LIMIT $limit OFFSET $offset";

$res = mysqli_query($conn, $sql);
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h2 class="fw-bold"><i class="fas fa-calendar-check text-primary me-2"></i>Follow-Up Management</h2>
    </div>
    <div class="col-md-7">
        <form method="GET" class="row g-2 justify-content-end">
            <div class="col-auto">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="asc" <?= $sort == 'ASC' ? 'selected' : '' ?>>Patient: A-Z</option>
                    <option value="desc" <?= $sort == 'DESC' ? 'selected' : '' ?>>Patient: Z-A</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search Patient..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <?php if($search): ?>
                <div class="col-auto">
                    <a href="followups.php" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Follow-up Date</th>
                        <th>Healthcare Status</th>
                        <th class="text-end pe-4">Clinical Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($res) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $badgeClass = 'bg-secondary';
                            $rowClass = '';
                            if ($row['status'] == 'OVERDUE') {
                                $badgeClass = 'bg-danger';
                                $rowClass = 'table-danger-light';
                            } elseif ($row['status'] == 'UPCOMING') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($row['status'] == 'FUTURE') {
                                $badgeClass = 'bg-success';
                            }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1 small"></i><?= $row['phone'] ?></small>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= $row['follow_up_due'] ?></div>
                                <small class="text-muted">Due Date </small>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill">
                                    <?= $row['status'] ?> 
                                    (<?= abs($row['days_diff']) ?> days <?= $row['days_diff'] < 0 ? 'ago' : 'away' ?>)
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if($row['status'] == 'OVERDUE'): ?>
                                    <a href="tel:<?= $row['phone'] ?>" class="btn btn-sm btn-danger shadow-sm">
                                        <i class="fas fa-phone"></i> Urgent Call
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled">Monitor</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-5 text-muted">No active follow-ups found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer bg-white py-3 border-0">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
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

<div class="mt-4">
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
        <i class="fas fa-exclamation-circle fa-2x me-3"></i>
        <div>
            <strong>Healthcare Protocol</strong> 
            Patients marked as <strong>OVERDUE</strong> should be contacted immediately. Missed follow-ups impact clinic quality metrics.
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>