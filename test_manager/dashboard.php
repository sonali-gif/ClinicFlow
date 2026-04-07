<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
checkSession('test_manager');

// Fetch patients who have tests assigned
$stmt = $conn->query("
    SELECT p.*, d.full_name as doctor_name 
    FROM patients p 
    JOIN doctors d ON p.assigned_doctor_id = d.id 
    WHERE p.tests IS NOT NULL AND p.tests != ''
    ORDER BY p.created_at DESC
");
$patients = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar" style="background: #0dcaf0;">
            <div class="p-3 text-white">
                <h4>ClinicFlow</h4>
                <p class="small opacity-75">Test Department Panel</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active text-white"><i class="fas fa-microscope me-2"></i> Assigned Tests</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Patient Tests Overview</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-info text-dark p-2">Welcome, <?php echo $_SESSION['test_manager_name']; ?></span>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tests Assigned by Doctors</h5>
                    <span class="badge bg-info text-dark">Total: <?php echo count($patients); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Assigned Doctor</th>
                                    <th>Tests Required</th>
                                    <th>Uploaded Test File</th>
                                    <th>Status</th>
                                    <th>Assigned Time</th>
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
                                                <div class="p-2 border rounded bg-light small">
                                                    <?php echo nl2br(htmlspecialchars($p['tests'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($p['test_file'])): ?>
                                                    <a href="../uploads/<?php echo $p['test_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-file-pdf me-1"></i> View Report
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small italic">No file uploaded yet</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $p['status'] == 'Pending' ? 'bg-warning text-dark' : ($p['status'] == 'In Progress' ? 'bg-primary' : 'bg-success'); 
                                                ?>">
                                                    <?php echo $p['status']; ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('H:i d M', strtotime($p['created_at'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No tests have been assigned by any doctors yet.</td>
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
