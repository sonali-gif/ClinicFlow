<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
checkSession('admin');

// Fetch all patient details with doctor names
$stmt = $conn->query("
    SELECT p.*, d.full_name as doctor_name 
    FROM patients p 
    LEFT JOIN doctors d ON p.assigned_doctor_id = d.id 
    ORDER BY p.created_at DESC
");
$patients = $stmt->fetchAll();

// Get counts for dashboard
$total_patients = count($patients);
$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;

foreach ($patients as $p) {
    if ($p['status'] == 'Pending') $pending_count++;
    elseif ($p['status'] == 'In Progress') $in_progress_count++;
    elseif ($p['status'] == 'Completed') $completed_count++;
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar" style="background: #212529;">
            <div class="p-3 text-white">
                <h4>ClinicFlow</h4>
                <p class="small opacity-75">Admin Panel</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active text-white"><i class="fas fa-eye me-2"></i> All Patients</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Oversight Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-dark p-2">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body py-4">
                            <h6>Total Patients</h6>
                            <h3><?php echo $total_patients; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body py-4">
                            <h6>Pending</h6>
                            <h3><?php echo $pending_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body py-4">
                            <h6>In Progress</h6>
                            <h3><?php echo $in_progress_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body py-4">
                            <h6>Completed</h6>
                            <h3><?php echo $completed_count; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">All Patient Process & Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Patient Detail</th>
                                    <th>Doctor</th>
                                    <th>Symptoms</th>
                                    <th>Medical Info</th>
                                    <th>Status</th>
                                    <th>Next Appointment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($total_patients > 0): ?>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['patient_id']); ?></span></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                                                <small class="text-muted"><?php echo $p['age']; ?>y / <?php echo $p['gender']; ?></small>
                                            </td>
                                            <td><span class="text-primary"><?php echo htmlspecialchars($p['doctor_name']); ?></span></td>
                                            <td><small><?php echo htmlspecialchars($p['symptoms']); ?></small></td>
                                            <td>
                                                <?php if ($p['prescription'] || $p['tests']): ?>
                                                    <div class="small">
                                                        <strong>P:</strong> <?php echo mb_strimwidth(htmlspecialchars($p['prescription']), 0, 50, "..."); ?><br>
                                                        <strong>T:</strong> <?php echo mb_strimwidth(htmlspecialchars($p['tests']), 0, 50, "..."); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">None</span>
                                                <?php endif; ?>
                                                
                                                <div class="mt-1">
                                                    <?php if ($p['prescription_file']): ?>
                                                        <a href="../uploads/<?php echo $p['prescription_file']; ?>" target="_blank" title="View Prescription File"><i class="fas fa-file-pdf text-danger me-2"></i></a>
                                                    <?php endif; ?>
                                                    <?php if ($p['test_file']): ?>
                                                        <a href="../uploads/<?php echo $p['test_file']; ?>" target="_blank" title="View Test File"><i class="fas fa-file-alt text-info"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $p['status'] == 'Pending' ? 'bg-warning text-dark' : ($p['status'] == 'In Progress' ? 'bg-primary' : 'bg-success'); 
                                                ?>">
                                                    <?php echo $p['status']; ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo $p['next_appointment'] ? date('d M Y', strtotime($p['next_appointment'])) : 'Not Set'; ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No patient records found in the system.</td>
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
