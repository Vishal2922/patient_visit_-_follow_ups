<?php
include '../includes/auth_check.php';
include '../config/db.php';
include '../includes/header.php';


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

$order_clause = ($sort == 'time') ? "join_date DESC" : "name ASC";

//  PAGINATION CALCULATIONS ---
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total results for pagination
$count_sql = "SELECT COUNT(*) AS total FROM patients WHERE name LIKE '%$search%' OR phone LIKE '%$search%'";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);


$sql = "SELECT *, 
        TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age_yrs,
        TIMESTAMPDIFF(MONTH, dob, CURDATE()) % 12 AS age_mos 
        FROM patients 
        WHERE name LIKE '%$search%' OR phone LIKE '%$search%'
        ORDER BY $order_clause 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4 p-3 bg-light">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or phone..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Sort: Alphabetical (A-Z)</option>
                    <option value="time" <?= $sort == 'time' ? 'selected' : '' ?>>Sort: Registration Time</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="add.php" class="btn btn-success w-100"><i class="fas fa-user-plus me-1"></i> New</a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Patient Profile</th>
                        <th>Age/DOB</th>
                        <th>Contact Info</th>
                        <th>Join Date</th>
                        <th class="text-end pe-4">Manage Record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">
                                        <a href="view.php?id=<?= $row['patient_id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($row['name']) ?>
                                        </a>
                                    </div>
                                    <small class="text-muted">ID: #<?= $row['patient_id'] ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold">
                                        <?= $row['age_yrs'] ?>y <?= $row['age_mos'] ?>m
                                    </div>
                                    <small class="text-muted"><?= $row['dob'] ?></small>
                                </td>
                                <td>
                                    <div class="small"><i class="fas fa-phone me-1 text-primary"></i> <?= $row['phone'] ?></div>
                                    <div class="tiny text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars(substr($row['address'], 0, 20)) ?>...
                                    </div>
                                </td>
                                <td><?= $row['join_date'] ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['patient_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Profile">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <?php if (isAdmin()): ?>
                                            <a href="delete.php?id=<?= $row['patient_id'] ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('WARNING: Permanently delete this patient record?')"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No clinical records match your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white py-3 border-0">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-none" href="?page=<?= $page - 1 ?>&search=<?= $search ?>&sort=<?= $sort ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link shadow-none" href="?page=<?= $i ?>&search=<?= $search ?>&sort=<?= $sort ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-none" href="?page=<?= $page + 1 ?>&search=<?= $search ?>&sort=<?= $sort ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>