<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
checkSession('doctor');

$doctor_id = $_SESSION['doctor_id'];
$success = '';

// Update Patient Status
if (isset($_GET['complete_id'])) {
    $complete_id = $_GET['complete_id'];
    $stmt = $conn->prepare("UPDATE patients SET status = 'Completed' WHERE id = ? AND assigned_doctor_id = ?");
    if ($stmt->execute([$complete_id, $doctor_id])) {
        $success = "Patient marked as completed!";
    }
}

if (isset($_GET['progress_id'])) {
    $progress_id = $_GET['progress_id'];
    $stmt = $conn->prepare("UPDATE patients SET status = 'In Progress' WHERE id = ? AND assigned_doctor_id = ?");
    if ($stmt->execute([$progress_id, $doctor_id])) {
        $success = "Patient marked as in progress!";
    }
}

// Save Prescription and Tests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_medical_info'])) {
    $p_id = $_POST['patient_id'];
    $prescription = $_POST['prescription'];
    $tests = $_POST['tests'];

    // File Upload Handling
    $upload_dir = "../uploads/";
    $prescription_file = null;
    $test_file = null;

    // Fetch existing file names in case they are not updated
    $stmt_fetch = $conn->prepare("SELECT prescription_file, test_file FROM patients WHERE id = ?");
    $stmt_fetch->execute([$p_id]);
    $current_files = $stmt_fetch->fetch();

    if (!empty($_FILES['prescription_file']['name'])) {
        $prescription_file = time() . "_p_" . basename($_FILES['prescription_file']['name']);
        move_uploaded_file($_FILES['prescription_file']['tmp_name'], $upload_dir . $prescription_file);
    } else {
        $prescription_file = $current_files['prescription_file'];
    }

    if (!empty($_FILES['test_file']['name'])) {
        $test_file = time() . "_t_" . basename($_FILES['test_file']['name']);
        move_uploaded_file($_FILES['test_file']['tmp_name'], $upload_dir . $test_file);
    } else {
        $test_file = $current_files['test_file'];
    }

    $stmt = $conn->prepare("UPDATE patients SET prescription = ?, tests = ?, prescription_file = ?, test_file = ? WHERE id = ? AND assigned_doctor_id = ?");
    if ($stmt->execute([$prescription, $tests, $prescription_file, $test_file, $p_id, $doctor_id])) {
        $success = "Medical info and files saved successfully!";
    } else {
        $error = "Failed to save medical info.";
    }
}

// Fetch only patients assigned to this specific doctor
$stmt = $conn->prepare("SELECT * FROM patients WHERE assigned_doctor_id = ? ORDER BY created_at DESC");
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar" style="background: #198754;">
            <div class="p-3">
                <h4>ClinicFlow</h4>
                <p class="small text-muted">Doctor Panel</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-list me-2"></i> My Patients</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Assigned Patients</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-success">Welcome, <?php echo $_SESSION['doctor_name']; ?></span>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">List of Assigned Patients</h5>
                    <span class="badge bg-success">Total: <?php echo count($patients); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Age/Gender</th>
                                    <th>Symptoms</th>
                                    <th>Medical Info</th>
                                    <th>Status</th>
                                    <th>Arrival Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($patients) > 0): ?>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['patient_id']); ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                            <td><?php echo $p['age']; ?> / <?php echo $p['gender']; ?></td>
                                            <td><?php echo htmlspecialchars($p['symptoms']); ?></td>
                                            <td>
                                                <form method="POST" enctype="multipart/form-data" class="mb-0">
                                                    <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                                                    <div class="mb-2">
                                                        <textarea name="prescription" class="form-control form-control-sm mb-1" placeholder="Prescription" rows="2"><?php echo htmlspecialchars($p['prescription']); ?></textarea>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text small">File:</span>
                                                            <input type="file" name="prescription_file" class="form-control form-control-sm">
                                                        </div>
                                                        <?php if (!empty($p['prescription_file'])): ?>
                                                            <div class="mt-1 small">
                                                                <a href="../uploads/<?php echo $p['prescription_file']; ?>" target="_blank" class="text-decoration-none">
                                                                    <i class="fas fa-file-pdf text-danger me-1"></i> View Prescription
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <input type="text" name="tests" class="form-control form-control-sm mb-1" placeholder="Tests to be taken" value="<?php echo htmlspecialchars($p['tests']); ?>">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text small">File:</span>
                                                            <input type="file" name="test_file" class="form-control form-control-sm">
                                                        </div>
                                                        <?php if (!empty($p['test_file'])): ?>
                                                            <div class="mt-1 small">
                                                                <a href="../uploads/<?php echo $p['test_file']; ?>" target="_blank" class="text-decoration-none">
                                                                    <i class="fas fa-microscope text-info me-1"></i> View Test Report
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button type="submit" name="save_medical_info" class="btn btn-sm btn-outline-success w-100 mt-1">
                                                        <i class="fas fa-save me-1"></i> Save Medical Info
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $p['status'] == 'Pending' ? 'bg-warning text-dark' : ($p['status'] == 'In Progress' ? 'bg-primary' : 'bg-success'); 
                                                ?>">
                                                    <?php echo $p['status']; ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('H:i d M', strtotime($p['created_at'])); ?></small></td>
                                            <td>
                                                <?php if ($p['status'] == 'Pending'): ?>
                                                    <a href="dashboard.php?progress_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary w-100 mb-1">Start</a>
                                                <?php elseif ($p['status'] == 'In Progress'): ?>
                                                    <a href="dashboard.php?complete_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-success w-100 mb-1">Complete</a>
                                                <?php endif; ?>
                                                <a href="logout.php" class="btn btn-sm btn-outline-danger w-100" style="display:none;">Logout</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No patients assigned to you yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
