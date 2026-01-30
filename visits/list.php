<?php
include '../config/db.php';
include '../includes/header.php';
include '../includes/auth_check.php';

//  SEARCH & SORT PARAMETERS 
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'time';
$order_clause = ($sort == 'alpha') ? "p.name ASC" : "v.visit_date DESC";

//  PAGINATION LOGIC (Maintained) 
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) AS total FROM visits v JOIN patients p ON v.patient_id = p.patient_id 
              WHERE p.name LIKE '%$search%'";
$count_res = mysqli_query($conn, $count_sql);
$total_visits = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_visits / $limit);

//  MAIN DATA QUERY also inclded the search & sort
$sql = "SELECT v.*, p.name, p.phone,
        DATEDIFF(CURDATE(), v.visit_date) AS days_since,
        (v.consultation_fee + v.lab_fee) AS total_bill,
        CASE 
            WHEN v.follow_up_due < CURDATE() THEN 'Critical Overdue'
            WHEN v.follow_up_due = CURDATE() THEN 'Due Today'
            WHEN v.follow_up_due BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'Urgent'
            ELSE 'Stable'
        END AS clinical_priority
        FROM visits v 
        JOIN patients p ON v.patient_id = p.patient_id 
        WHERE p.name LIKE '%$search%'
        ORDER BY $order_clause 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<div class="card shadow-sm border-0 mb-4 p-3 bg-light">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search by patient name..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="time" <?= $sort == 'time' ? 'selected' : '' ?>>Sort: Newest Visit</option>
                <option value="alpha" <?= $sort == 'alpha' ? 'selected' : '' ?>>Sort: Patient A-Z</option>
            </select>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        <div class="col-md-2 text-end"><a href="add.php" class="btn btn-success w-100"><i class="fas fa-plus"></i> New Visit</a></div>
    </form>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark text-uppercase small">
                <tr>
                    <th class="ps-4">Patient Profile</th>
                    <th>Visit Detail</th>
                    <th>Billing</th>
                    <th>Clinical Priority</th>
                    <th class="text-end pe-4">Clinical & Admin Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $priorityClass = ($row['clinical_priority'] == 'Critical Overdue') ? 'bg-danger' : (($row['clinical_priority'] == 'Due Today') ? 'bg-warning text-dark' : 'bg-success');
                ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                            <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?= $row['phone'] ?></small>
                        </td>
                        <td>
                            <div class="small fw-bold"><?= $row['visit_date'] ?></div>
                            <div class="text-muted tiny"><?= $row['days_since'] ?> days ago</div>
                        </td>
                        <td>
                            <div class="text-primary fw-bold">$<?= number_format($row['total_bill'], 2) ?></div>
                        </td>
                        <td><span class="badge <?= $priorityClass ?> rounded-pill px-3 py-2"><?= $row['clinical_priority'] ?></span></td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm">
                                <a href="patient_visits.php?patient_id=<?= $row['patient_id'] ?>" class="btn btn-sm btn-outline-primary" title="View History">
                                    <i class="fas fa-history"></i>
                                </a>
                                <a href="tel:<?= $row['phone'] ?>" class="btn btn-sm btn-outline-success" title="Call Patient">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <a href="edit_visit.php?id=<?= $row['visit_id'] ?>" class="btn btn-sm btn-outline-info" title="Edit Log">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_visit.php?id=<?= $row['visit_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Archive this visit record?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&sort=<?= $sort ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<?php include '../includes/footer.php'; ?>