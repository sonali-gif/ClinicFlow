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
    $diagnosis = $_POST['diagnosis'] ?? '';

    // Merge diagnosis into prescription JSON if it's a valid JSON
    $prescription_data = json_decode($prescription, true);
    if (is_array($prescription_data)) {
        // Save just the medicine names for the pharmacist
        $medicine_items = [];
        foreach ($prescription_data as $med) {
            if (isset($med['name'])) {
                $medicine_items[] = $med['name'];
            }
        }
        $medicine_items_str = implode("\n", $medicine_items);

        $prescription_data = ['diagnosis' => $diagnosis, 'medicines' => $prescription_data];
        $prescription = json_encode($prescription_data);
    } else {
        $medicine_items_str = '';
    }

    $stmt = $conn->prepare("UPDATE patients SET prescription = ?, medicine_items = ?, tests = ? WHERE id = ? AND assigned_doctor_id = ?");
    if ($stmt->execute([$prescription, $medicine_items_str, $tests, $p_id, $doctor_id])) {
        $success = "Medical information saved successfully!";
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

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="icon-badge shadow-sm" style="width: 40px; height: 40px; border-radius: 10px; background: #24a148; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fas fa-user-md"></i>
        </div>
        <h4>Doctor Portal</h4>
    </div>
    <div class="p-3">
        <p class="small text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.7rem;">Main Menu</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-list"></i> My Patients</a></li>
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
            <h2 class="fw-bold mb-1">New Prescription</h2>
            <p class="text-muted mb-0">Create and manage patient prescriptions.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if (defined('TEST_SESSION_BYPASS') && TEST_SESSION_BYPASS): ?>
                <div class="badge bg-warning text-dark me-2">Test Mode Active</div>
            <?php endif; ?>
            <div class="text-end">
                <p class="small fw-bold mb-0"><?php echo $_SESSION['doctor_name']; ?></p>
                <p class="small text-muted mb-0">Logged in ID: <?php echo $_SESSION['doctor_id']; ?></p>
            </div>
            <div class="icon-badge shadow-sm" style="width: 48px; height: 48px; background: #e5f6ed; color: #24a148; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-user-md"></i>
            </div>
        </div>
    </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <?php 
            $in_progress_count = 0;
            foreach ($patients as $p): 
                if ($p['status'] == 'In Progress'): 
                    $in_progress_count++;
            ?>
                    <form method="POST" class="prescription-form mb-5 p-4 rounded-4 border">
                        <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Patient</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($p['name']); ?> (<?php echo $p['patient_id']; ?>)" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Doctor</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $_SESSION['doctor_name']; ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="text" class="form-control bg-light" value="<?php echo date('d-m-Y'); ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Diagnosis</label>
                            <input type="text" name="diagnosis" id="diagnosis-<?php echo $p['id']; ?>" class="form-control" placeholder="e.g. Viral Fever" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                Medicines
                                <button type="button" class="btn btn-sm btn-outline-primary py-1 add-medicine-btn" data-patient-id="<?php echo $p['id']; ?>">
                                    <i class="fas fa-plus me-1"></i> Add Medicine
                                </button>
                            </label>
                            <div id="medicine-entries-container-<?php echo $p['id']; ?>" class="mb-3">
                                <!-- Medicine entries added here -->
                            </div>
                            <input type="hidden" name="prescription" id="prescription-input-<?php echo $p['id']; ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Laboratory Tests</label>
                            <input type="text" name="tests" class="form-control" placeholder="e.g. CBC, Blood Sugar, X-Ray" value="<?php echo htmlspecialchars($p['tests']); ?>">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="save_medical_info" class="btn btn-primary px-4">Save Medical Info</button>
                            <a href="dashboard.php?complete_id=<?php echo $p['id']; ?>" class="btn btn-success px-4">Complete Visit</a>
                            <button type="reset" class="btn btn-light border px-4">Reset</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($in_progress_count == 0): ?>
                <div class="text-center py-5 border rounded-4 mb-5 bg-light">
                    <div class="icon-badge mx-auto mb-3 shadow-sm" style="width: 64px; height: 64px; background: #ffffff; color: #64748b; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No Active Consultations</h5>
                    <p class="text-muted px-4">Select a patient from the <b>Pending Consultations</b> list below to start writing a prescription.</p>
                </div>
            <?php endif; ?>

            <div class="table-responsive mt-4">
                <h5 class="fw-bold mb-3">Pending Consultations</h5>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Patient Detail</th>
                            <th>Symptoms</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $pending_count = 0;
                        foreach ($patients as $p): 
                            if ($p['status'] == 'Pending'): 
                                $pending_count++;
                        ?>
                                <tr>
                                    <td>
                                        <div class="fw-600"><?php echo htmlspecialchars($p['name']); ?></div>
                                        <div class="small text-muted"><?php echo $p['patient_id']; ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['symptoms']); ?></td>
                                    <td><span class="badge" style="background: #fff9e6; color: #856404;">Pending</span></td>
                                    <td>
                                        <a href="dashboard.php?progress_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary px-3">Start Consultation</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if ($pending_count == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-1"></i> No pending consultations for you.
                                </td>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.add-medicine-btn').forEach(button => {
            button.addEventListener('click', function() {
                const patientId = this.dataset.patientId;
                addMedicineEntry(patientId);
            });
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const patientId = this.querySelector('input[name="patient_id"]').value;
                serializeMedicineData(patientId);
            });
        });

        // Initialize existing prescriptions
        <?php foreach ($patients as $p): ?>
            <?php if (!empty($p['prescription'])): ?>
                try {
                    const data = JSON.parse(<?php echo json_encode($p['prescription']); ?>);
                    
                    // Handle new structured format
                    if (data.diagnosis && data.medicines) {
                        document.getElementById('diagnosis-<?php echo $p['id']; ?>').value = data.diagnosis;
                        data.medicines.forEach(medicine => {
                            addMedicineEntry(<?php echo $p['id']; ?>, medicine);
                        });
                    } 
                    // Handle old array-only format
                    else if (Array.isArray(data)) {
                        data.forEach(medicine => {
                            addMedicineEntry(<?php echo $p['id']; ?>, medicine);
                        });
                    }
                } catch (e) {
                    console.error("Error parsing prescription for patient <?php echo $p['id']; ?>:", e);
                }
            <?php endif; ?>
        <?php endforeach; ?>
    });

    function addMedicineEntry(patientId, medicine = {}) {
        const container = document.getElementById(`medicine-entries-container-${patientId}`);
        const entryDiv = document.createElement('div');
        entryDiv.classList.add('input-group', 'input-group-sm', 'mb-1', 'medicine-entry');
        entryDiv.innerHTML = `
            <input type="text" class="form-control medicine-name" placeholder="Medicine" value="${medicine.name || ''}">
            <input type="text" class="form-control medicine-dosage" placeholder="Dose" value="${medicine.dosage || ''}">
            <input type="text" class="form-control medicine-frequency" placeholder="Freq" value="${medicine.frequency || ''}">
            <button type="button" class="btn btn-outline-danger remove-medicine-btn"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(entryDiv);

        entryDiv.querySelector('.remove-medicine-btn').addEventListener('click', function() {
            entryDiv.remove();
        });
    }

    function serializeMedicineData(patientId) {
        const medicineEntries = [];
        document.querySelectorAll(`#medicine-entries-container-${patientId} .medicine-entry`).forEach(entryDiv => {
            const name = entryDiv.querySelector('.medicine-name').value;
            const dosage = entryDiv.querySelector('.medicine-dosage').value;
            const frequency = entryDiv.querySelector('.medicine-frequency').value;
            if (name) { // Only add if medicine name is provided
                medicineEntries.push({ name, dosage, frequency });
            }
        });
        document.getElementById(`prescription-input-${patientId}`).value = JSON.stringify(medicineEntries);
    }
</script>
<?php include '../includes/footer.php'; ?>
