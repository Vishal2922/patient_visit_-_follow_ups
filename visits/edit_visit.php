<?php 
include '../config/db.php'; 
$id = (int)$_GET['id'];
$current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM visits WHERE visit_id = $id"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cons_fee = mysqli_real_escape_string($conn, $_POST['consultation_fee']);
    $lab_fee = mysqli_real_escape_string($conn, $_POST['lab_fee']);
    $follow_up = mysqli_real_escape_string($conn, $_POST['follow_up_due']);
    
    $sql = "UPDATE visits SET consultation_fee='$cons_fee', lab_fee='$lab_fee', follow_up_due='$follow_up' WHERE visit_id=$id";
    if(mysqli_query($conn, $sql)) {
        header("Location: list.php?msg=updated");
        exit();
    }
}
include '../includes/header.php'; 
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0">
            <div class="card-header bg-info text-white py-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Update Visit Financials</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Consultation Fee</label>
                        <input type="number" name="consultation_fee" class="form-control" value="<?= $current['consultation_fee'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lab Fee</label>
                        <input type="number" name="lab_fee" class="form-control" value="<?= $current['lab_fee'] ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Follow-up Due Date</label>
                        <input type="date" name="follow_up_due" class="form-control" value="<?= $current['follow_up_due'] ?>" required>
                    </div>
                    <button type="submit" class="btn btn-info w-100 text-white fw-bold">Apply Changes</button>
                    <a href="list.php" class="btn btn-link w-100 text-secondary mt-2">Discard</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>