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
$prescription_count = 0;

foreach ($patients as $p) {
    if ($p['status'] == 'Pending') $pending_count++;
    elseif ($p['status'] == 'In Progress') $in_progress_count++;
    elseif ($p['status'] == 'Completed') $completed_count++;
    
    if (!empty($p['prescription']) || !empty($p['prescription_file'])) {
        $prescription_count++;
    }
}

// Fetch actual doctor count
$total_doctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

include '../includes/header.php';
?>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="icon-badge shadow-sm" style="width: 40px; height: 40px; border-radius: 10px; background: #161616; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fas fa-user-shield"></i>
        </div>
        <h4>Admin Panel</h4>
    </div>
    <div class="p-3">
        <p class="small text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.7rem;">Main Menu</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-chart-line"></i> Oversight</a></li>
        </ul>
        <p class="small text-uppercase fw-bold text-muted mt-4 mb-2 px-3" style="font-size: 0.7rem;">System</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">System Oversight</h2>
            <p class="text-muted mb-0">Monitor hospital operations and patient flow in real-time.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <p class="small fw-bold mb-0"><?php echo $_SESSION['admin_name']; ?></p>
                <p class="small text-muted mb-0">System Administrator</p>
            </div>
            <div class="icon-badge" style="width: 48px; height: 48px; background: #f4f4f4; color: #161616; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_patients; ?></h3>
                            <p>Total Patients</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pending_count + $in_progress_count; ?></h3>
                            <p>Active Cases</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon icon-purple">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_doctors; ?></h3>
                            <p>Total Doctors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-icon icon-red">
                            <i class="fas fa-file-prescription"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $prescription_count; ?></h3>
                            <p>Prescriptions</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Upcoming Appointments Table -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Upcoming Appointments</h5>
                            <a href="#" class="btn btn-sm btn-light border small px-3">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Patient Name</th>
                                            <th>Doctor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($patients, 0, 5) as $p): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-600"><?php echo htmlspecialchars($p['name']); ?></div>
                                                    <div class="small text-muted"><?php echo $p['patient_id']; ?></div>
                                                </td>
                                                <td>
                                                    <div class="small fw-500"><?php echo htmlspecialchars($p['doctor_name']); ?></div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $statusClass = $p['status'] == 'Pending' ? 'badge-pending' : ($p['status'] == 'In Progress' ? 'badge-confirmed' : 'badge-confirmed');
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $p['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Patients Table -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Recent Patients</h5>
                            <a href="#" class="btn btn-sm btn-light border small px-3">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Age</th>
                                            <th>Contact</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($patients, 0, 5) as $p): ?>
                                            <tr>
                                                <td><div class="fw-600"><?php echo htmlspecialchars($p['name']); ?></div></td>
                                                <td><?php echo $p['age']; ?></td>
                                                <td><div class="small text-muted">9876543210</div></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
