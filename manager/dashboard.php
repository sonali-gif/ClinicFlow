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
    $next_appointment = !empty($_POST['next_appointment']) ? $_POST['next_appointment'] : null;

    $stmt = $conn->prepare("UPDATE patients SET next_appointment = ? WHERE id = ?");
    if ($stmt->execute([$next_appointment, $patient_id])) {
        $success = "Next appointment updated successfully!";
    } else {
        $error = "Failed to update appointment.";
    }
}

// Delete Patient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_patient'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Patient deleted successfully!";
        // Refresh patients list
        $patients = $conn->query("
            SELECT p.*, d.full_name as doctor_name 
            FROM patients p 
            LEFT JOIN doctors d ON p.assigned_doctor_id = d.id 
            ORDER BY p.created_at DESC
        ")->fetchAll();
    } else {
        $error = "Failed to delete patient.";
    }
}

// Edit Patient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_patient'])) {
    $id = $_POST['id'];
    $patient_id = $_POST['patient_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $symptoms = $_POST['symptoms'];
    $doctor_id = $_POST['doctor_id'];
    $next_appointment = !empty($_POST['next_appointment']) ? $_POST['next_appointment'] : null;

    $stmt = $conn->prepare("UPDATE patients SET patient_id = ?, name = ?, age = ?, gender = ?, symptoms = ?, assigned_doctor_id = ?, next_appointment = ? WHERE id = ?");
    if ($stmt->execute([$patient_id, $name, $age, $gender, $symptoms, $doctor_id, $next_appointment, $id])) {
        $success = "Patient updated successfully!";
        // Refresh patients list
        $patients = $conn->query("
            SELECT p.*, d.full_name as doctor_name 
            FROM patients p 
            LEFT JOIN doctors d ON p.assigned_doctor_id = d.id 
            ORDER BY p.created_at DESC
        ")->fetchAll();
    } else {
        $error = "Failed to update patient.";
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

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="icon-badge shadow-sm" style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fas fa-hospital"></i>
        </div>
        <h4>ClinicFlow</h4>
    </div>
    <div class="p-3">
        <p class="small text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.7rem;">Main Menu</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-user-plus"></i> Patient Intake</a></li>
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
            <h2 class="fw-bold mb-1">Patients</h2>
            <p class="text-muted mb-0">Manage and register your clinic patients.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
            <i class="fas fa-plus me-2"></i> Add Patient
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="patientSearch" class="form-control border-start-0" placeholder="Search patient...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Assigned Doctor</th>
                            <th>Contact</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'];
                        $base_url = "$protocol://$host/ClinicFlow/patient_dashboard.php?id=";
                        
                        foreach ($patients as $p): 
                        ?>
                            <tr>
                                <td><span class="fw-600 text-primary"><?php echo htmlspecialchars($p['patient_id']); ?></span></td>
                                <td>
                                    <div class="fw-600"><?php echo htmlspecialchars($p['name']); ?></div>
                                </td>
                                <td><?php echo $p['age']; ?></td>
                                <td><?php echo $p['gender']; ?></td>
                                <td>
                                    <div class="small fw-600 text-dark"><?php echo htmlspecialchars($p['doctor_name'] ?: 'Not Assigned'); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">ID: <?php echo $p['assigned_doctor_id']; ?></div>
                                </td>
                                <td><div class="small text-muted">9876543210</div></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-light border show-qr" 
                                                data-url="<?php echo $base_url . urlencode($p['patient_id']); ?>" 
                                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                                data-id="<?php echo htmlspecialchars($p['patient_id']); ?>">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary edit-patient" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPatientModal"
                                                data-id="<?php echo $p['id']; ?>"
                                                data-patient-id="<?php echo htmlspecialchars($p['patient_id']); ?>"
                                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                                data-age="<?php echo $p['age']; ?>"
                                                data-gender="<?php echo $p['gender']; ?>"
                                                data-symptoms="<?php echo htmlspecialchars($p['symptoms']); ?>"
                                                data-doctor="<?php echo $p['assigned_doctor_id']; ?>"
                                                data-appointment="<?php echo $p['next_appointment']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this patient?');">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" name="delete_patient" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="small text-muted">Showing 1 to <?php echo count($patients); ?> of <?php echo count($patients); ?> entries</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div class="modal fade" id="addPatientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Register New Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Patient ID</label>
                            <input type="text" name="patient_id" class="form-control" placeholder="e.g. PAT-001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" class="form-control" placeholder="Age" required>
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
                            <textarea name="symptoms" class="form-control" rows="3" placeholder="Describe symptoms..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Doctor</label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="" disabled selected>Select doctor</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['full_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_patient" class="btn btn-primary w-100 mt-2">Register Patient</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div class="modal fade" id="editPatientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Edit Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Patient ID</label>
                            <input type="text" name="patient_id" id="edit_patient_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" id="edit_age" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" id="edit_gender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Symptoms</label>
                            <textarea name="symptoms" id="edit_symptoms" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Doctor</label>
                            <select name="doctor_id" id="edit_doctor_id" class="form-select" required>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['full_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Appointment</label>
                            <input type="date" name="next_appointment" id="edit_next_appointment" class="form-control">
                        </div>
                        <button type="submit" name="edit_patient" class="btn btn-primary w-100 mt-2">Update Patient</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="qrModalLabel">Patient History QR Code</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <h5 id="qrPatientName" class="mb-1"></h5>
                <p id="qrPatientID" class="text-muted small mb-4"></p>
                <div id="qrcode" class="d-flex justify-content-center mb-4"></div>
                <p class="small text-muted">Scan this QR code to view real-time patient status and history.</p>
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-link me-1"></i> <span id="qrUrlText" style="word-break: break-all;"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnPrintQR">Print QR</button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #qrModal, #qrModal * { visibility: visible; }
    #qrModal { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
    .modal-footer, .btn-close { display: none !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
    const qrcodeContainer = document.getElementById('qrcode');
    const btnPrintQR = document.getElementById('btnPrintQR');
    let qrcode = null;

    btnPrintQR.addEventListener('click', function() {
        window.print();
    });

    document.querySelectorAll('.show-qr').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const name = this.getAttribute('data-name');
            const id = this.getAttribute('data-id');

            document.getElementById('qrPatientName').innerText = name;
            document.getElementById('qrPatientID').innerText = 'ID: ' + id;
            document.getElementById('qrUrlText').innerText = url;

            // Clear previous QR code
            qrcodeContainer.innerHTML = '';
            
            // Generate new QR code
            new QRCode(qrcodeContainer, {
                text: url,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            qrModal.show();
        });
    });

    // Edit Patient Functionality
    document.querySelectorAll('.edit-patient').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const patientId = this.getAttribute('data-patient-id');
            const name = this.getAttribute('data-name');
            const age = this.getAttribute('data-age');
            const gender = this.getAttribute('data-gender');
            const symptoms = this.getAttribute('data-symptoms');
            const doctorId = this.getAttribute('data-doctor');
            const appointment = this.getAttribute('data-appointment');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_patient_id').value = patientId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_age').value = age;
            document.getElementById('edit_gender').value = gender;
            document.getElementById('edit_symptoms').value = symptoms;
            document.getElementById('edit_doctor_id').value = doctorId;
            document.getElementById('edit_next_appointment').value = appointment || '';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
