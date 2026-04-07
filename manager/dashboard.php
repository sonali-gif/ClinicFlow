<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
checkSession('manager');

$error = '';
$success = '';

// Add Patient and Assign Doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_patient'])) {
    $patient_id = $_POST['patient_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $symptoms = $_POST['symptoms'];
    $doctor_id = $_POST['doctor_id'];
    $next_appointment = !empty($_POST['next_appointment']) ? $_POST['next_appointment'] : null;

    $stmt = $conn->prepare("INSERT INTO patients (patient_id, name, age, gender, symptoms, assigned_doctor_id, next_appointment) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$patient_id, $name, $age, $gender, $symptoms, $doctor_id, $next_appointment])) {
        $success = "Patient added and doctor assigned successfully!";
    } else {
        $error = "Failed to add patient. Please try again.";
    }
}

// Update Next Appointment for existing patient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_appointment'])) {
    $patient_id = $_POST['patient_id'];
    $next_appointment = $_POST['next_appointment'];

    $stmt = $conn->prepare("UPDATE patients SET next_appointment = ? WHERE id = ?");
    if ($stmt->execute([$next_appointment, $patient_id])) {
        $success = "Next appointment updated successfully!";
    } else {
        $error = "Failed to update appointment.";
    }
}

$doctors = $conn->query("SELECT * FROM doctors ORDER BY full_name ASC")->fetchAll();
$patients = $conn->query("
    SELECT p.*, d.full_name as doctor_name 
    FROM patients p 
    LEFT JOIN doctors d ON p.assigned_doctor_id = d.id 
    ORDER BY p.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="p-3">
                <h4>ClinicFlow</h4>
                <p class="small text-muted">Desk Manager Panel</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-user-plus me-2"></i> Patient Intake</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Patient Intake & Doctor Assignment</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-primary">Welcome, <?php echo $_SESSION['manager_name']; ?></span>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Patient Form -->
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">New Patient Entry</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Patient ID</label>
                                    <input type="text" name="patient_id" class="form-control" placeholder="e.g. PAT-001" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <input type="number" name="age" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Symptoms</label>
                                    <textarea name="symptoms" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Assign Doctor</label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="" disabled selected>Select a doctor...</option>
                                        <?php foreach ($doctors as $d): ?>
                                            <option value="<?php echo $d['id']; ?>"><?php echo $d['full_name']; ?> (<?php echo $d['specialization']; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Next Appointment (Optional)</label>
                                    <input type="date" name="next_appointment" class="form-control">
                                </div>
                                <button type="submit" name="add_patient" class="btn btn-primary w-100">Assign Patient to Doctor</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Recent Assignments -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Recent Patient Assignments</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Patient Name</th>
                                            <th>Age/Gender</th>
                                            <th>Symptoms</th>
                                            <th>Assigned Doctor</th>
                                            <th>Status</th>
                                            <th>Next Appointment</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patients as $p): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['patient_id']); ?></span></td>
                                                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                                <td><?php echo $p['age']; ?> / <?php echo $p['gender']; ?></td>
                                                <td><small><?php echo htmlspecialchars($p['symptoms']); ?></small></td>
                                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($p['doctor_name']); ?></span></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo $p['status'] == 'Pending' ? 'bg-warning text-dark' : ($p['status'] == 'In Progress' ? 'bg-primary' : 'bg-success'); 
                                                    ?>">
                                                        <?php echo $p['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-flex gap-1">
                                                        <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                                                        <input type="date" name="next_appointment" class="form-control form-control-sm" value="<?php echo $p['next_appointment']; ?>" required>
                                                        <button type="submit" name="update_appointment" class="btn btn-sm btn-outline-primary" title="Set Next Appointment"><i class="fas fa-save"></i></button>
                                                    </form>
                                                </td>
                                                <td><small class="text-muted"><?php echo date('H:i d M', strtotime($p['created_at'])); ?></small></td>
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
