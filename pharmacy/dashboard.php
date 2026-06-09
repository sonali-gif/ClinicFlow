<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
require_once '../includes/utils.php';
checkSession('pharmacist');

$success = '';
$error = '';

// Function to parse prescription text into potential medicine items
function parsePrescriptionForBilling($prescriptionText) {
    $items = [];
    $json_data = json_decode($prescriptionText, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
        // Handle nested structure: ['diagnosis' => ..., 'medicines' => [...]]
        if (isset($json_data['medicines']) && is_array($json_data['medicines'])) {
            foreach ($json_data['medicines'] as $medicine) {
                if (isset($medicine['name'])) {
                    $items[] = $medicine['name'];
                }
            }
        } 
        // Handle flat array structure: [{name: ...}, {name: ...}]
        else {
            foreach ($json_data as $medicine) {
                if (isset($medicine['name'])) {
                    $items[] = $medicine['name'];
                }
            }
        }
    } else {
        // Fallback to old plain text parsing
        $lines = explode("\n", $prescriptionText);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $items[] = $line;
        }
    }
    return $items;
}

// Save Medicine Bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_bill'])) {
    $p_id = $_POST['patient_id'];
    $medicine_bill_items = $_POST['medicine_item'] ?? [];
    $medicine_bill_amounts = $_POST['medicine_amount'] ?? [];

    $formatted_bill = [];
    foreach ($medicine_bill_items as $index => $item) {
        $item = trim($item);
        $amount = (float)($medicine_bill_amounts[$index] ?? 0);
        if (!empty($item) && $amount >= 0) {
            $formatted_bill[] = htmlspecialchars($item) . ': ' . number_format($amount, 2);
        }
    }
    $medicine_bill_string = implode("\n", $formatted_bill);

    $stmt = $conn->prepare("UPDATE patients SET medicine_bill = ? WHERE id = ?");
    if ($stmt->execute([$medicine_bill_string, $p_id])) {
        $success = "Medicine bill saved successfully!";
    } else {
        $error = "Failed to save medicine bill.";
    }
}

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

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="icon-badge shadow-sm" style="width: 40px; height: 40px; border-radius: 10px; background: #6f42c1; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fas fa-pills"></i>
        </div>
        <h4>Pharmacy</h4>
    </div>
    <div class="p-3">
        <p class="small text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.7rem;">Main Menu</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-prescription"></i> Prescriptions</a></li>
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
            <h2 class="fw-bold mb-1">Pharmacy Dashboard</h2>
            <p class="text-muted mb-0">Manage and fulfill patient prescriptions.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <p class="small fw-bold mb-0"><?php echo $_SESSION['pharmacist_name']; ?></p>
                <p class="small text-muted mb-0">Hospital Pharmacist</p>
            </div>
            <div class="icon-badge" style="width: 48px; height: 48px; background: #f6f2ff; color: #6f42c1; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-briefcase-medical"></i>
            </div>
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

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Patient Prescriptions & Billing</h5>
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
                                    <th>Medicine Bill</th>
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
                                                <?php
                                                $prescriptionData = json_decode($p['prescription'], true);
                                                $medicines = [];
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($prescriptionData)) {
                                                    if (isset($prescriptionData['medicines']) && is_array($prescriptionData['medicines'])) {
                                                        $medicines = $prescriptionData['medicines'];
                                                    } else {
                                                        $medicines = $prescriptionData;
                                                    }
                                                }

                                                if (!empty($medicines)): ?>
                                                    <div class="table-responsive small" style="max-width: 300px;">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Med</th>
                                                                    <th>Dosage</th>
                                                                    <th>Freq</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($medicines as $medicine): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($medicine['name'] ?? ''); ?></td>
                                                                        <td><?php echo htmlspecialchars($medicine['dosage'] ?? ''); ?></td>
                                                                        <td><?php echo htmlspecialchars($medicine['frequency'] ?? ''); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="p-2 border rounded bg-light small" style="max-width: 250px;">
                                                        <?php echo nl2br(htmlspecialchars($p['prescription'])); ?>
                                                    </div>
                                                <?php endif; ?>
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
                                            <td>
                                                <form method="POST" class="mb-0">
                                                    <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                                                    <div id="medicine_bill_form_<?php echo $p['id']; ?>">
                                                        <?php
                                                        $currentBillItems = [];
                                                        if (!empty($p['medicine_bill'])) {
                                                            $parsedBill = parseBill($p['medicine_bill']);
                                                            foreach ($parsedBill['items'] as $item) {
                                                                $currentBillItems[] = ['item' => $item['item'], 'amount' => $item['amount']];
                                                            }
                                                        } elseif (!empty($p['medicine_items'])) {
                                                            // If no bill, try to pre-populate from structured medicine_items
                                                            $items = explode("\n", $p['medicine_items']);
                                                            foreach ($items as $item) {
                                                                $item = trim($item);
                                                                if (!empty($item)) {
                                                                    $currentBillItems[] = ['item' => $item, 'amount' => ''];
                                                                }
                                                            }
                                                        } else {
                                                            // Fallback to parsing prescription
                                                            $prescriptionItems = parsePrescriptionForBilling($p['prescription']);
                                                            foreach ($prescriptionItems as $item) {
                                                                $currentBillItems[] = ['item' => $item, 'amount' => ''];
                                                            }
                                                        }

                                                        foreach ($currentBillItems as $index => $billItem):
                                                        ?>
                                                            <div class="input-group input-group-sm mb-1 medicine-item-row">
                                                                <input type="text" name="medicine_item[]" class="form-control" placeholder="Medicine Name" value="<?php echo htmlspecialchars($billItem['item']); ?>">
                                                                <input type="number" name="medicine_amount[]" class="form-control text-end" placeholder="Amount" step="0.01" value="<?php echo htmlspecialchars($billItem['amount']); ?>">
                                                                <button type="button" class="btn btn-outline-danger remove-medicine-item"><i class="fas fa-times"></i></button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                                        <span class="small fw-bold">Total Amount:</span>
                                                        <span class="small fw-bold text-purple bill-total" id="bill_total_<?php echo $p['id']; ?>">₹<?php echo number_format($parsedBill['total'] ?? 0, 2); ?></span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2 add-medicine-item" data-patient-id="<?php echo $p['id']; ?>"><i class="fas fa-plus me-1"></i> Add Item</button>
                                                    <button type="submit" name="save_bill" class="btn btn-sm btn-purple w-100 text-white" style="background-color: #6f42c1;">
                                                        <i class="fas fa-save me-1"></i> Save Bill
                                                    </button>
                                                </form>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('H:i d M', strtotime($p['created_at'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No prescriptions available.</td>
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
    // Function to update total for a specific patient
    function updatePatientTotal(patientId) {
        const container = document.getElementById(`medicine_bill_form_${patientId}`);
        const totalDisplay = document.getElementById(`bill_total_${patientId}`);
        const amounts = container.querySelectorAll('input[name="medicine_amount[]"]');
        let total = 0;
        amounts.forEach(input => {
            const val = parseFloat(input.value) || 0;
            total += val;
        });
        totalDisplay.textContent = '₹' + total.toFixed(2);
    }

    // Add Item functionality
    document.querySelectorAll('.add-medicine-item').forEach(button => {
        button.addEventListener('click', function() {
            const patientId = this.dataset.patientId;
            const container = document.getElementById(`medicine_bill_form_${patientId}`);
            const newRow = document.createElement('div');
            newRow.classList.add('input-group', 'input-group-sm', 'mb-1', 'medicine-item-row');
            newRow.innerHTML = `
                <input type="text" name="medicine_item[]" class="form-control" placeholder="Medicine Name">
                <input type="number" name="medicine_amount[]" class="form-control text-end medicine-amount-input" placeholder="Amount" step="0.01">
                <button type="button" class="btn btn-outline-danger remove-medicine-item"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(newRow);
            
            // Add listener to new input
            newRow.querySelector('.medicine-amount-input').addEventListener('input', () => updatePatientTotal(patientId));
        });
    });

    // Remove Item functionality (delegated event listener)
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-medicine-item') || event.target.closest('.remove-medicine-item')) {
            const button = event.target.closest('.remove-medicine-item');
            const row = button.closest('.medicine-item-row');
            const container = row.closest('[id^="medicine_bill_form_"]');
            const patientId = container.id.replace('medicine_bill_form_', '');
            row.remove();
            updatePatientTotal(patientId);
        }
    });

    // Add initial listeners to existing amount inputs
    document.querySelectorAll('input[name="medicine_amount[]"]').forEach(input => {
        const container = input.closest('[id^="medicine_bill_form_"]');
        if (container) {
            const patientId = container.id.replace('medicine_bill_form_', '');
            input.addEventListener('input', () => updatePatientTotal(patientId));
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
