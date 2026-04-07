<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
checkSession('pharmacist');

// Fetch patients who have prescriptions assigned
$stmt = $conn->query("
    SELECT p.*, d.full_name as doctor_name 
    FROM patients p 
    JOIN doctors d ON p.assigned_doctor_id = d.id 
    WHERE (p.prescription IS NOT NULL AND p.prescription != '') OR (p.prescription_file IS NOT NULL AND p.prescription_file != '')
    ORDER BY p.created_at DESC
");
$patients = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar" style="background: #6f42c1;">
            <div class="p-3 text-white">
                <h4>ClinicFlow</h4>
                <p class="small opacity-75">Pharmacy Panel</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active text-white"><i class="fas fa-pills me-2"></i> Prescriptions</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pharmacy Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-purple text-white p-2" style="background-color: #6f42c1;">Welcome, <?php echo $_SESSION['pharmacist_name']; ?></span>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Patient Prescriptions</h5>
                    <span class="badge bg-light text-dark">Total: <?php echo count($patients); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Assigned Doctor</th>
                                    <th>Prescription Details</th>
                                    <th>Uploaded Prescription</th>
                                    <th>Arrival Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($patients) > 0): ?>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['patient_id']); ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                            <td><span class="text-primary fw-bold"><?php echo htmlspecialchars($p['doctor_name']); ?></span></td>
                                            <td>
                                                <div class="p-2 border rounded bg-light small" style="max-width: 300px;">
                                                    <?php echo nl2br(htmlspecialchars($p['prescription'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($p['prescription_file'])): ?>
                                                    <a href="../uploads/<?php echo $p['prescription_file']; ?>" target="_blank" class="btn btn-sm btn-outline-purple" style="color: #6f42c1; border-color: #6f42c1;">
                                                        <i class="fas fa-file-prescription me-1"></i> View File
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small italic">No file uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('H:i d M', strtotime($p['created_at'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No prescriptions available.</td>
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
