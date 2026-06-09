<?php
require_once '../includes/session_check.php';
require_once '../db_config.php';
require_once '../includes/utils.php';
checkSession('test_manager');

$success = '';
$error = '';

// Function to parse assigned tests text into potential test items
function parseTestsForBilling($testsText) {
    $items = [];
    $lines = explode("\n", $testsText);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        // Simple heuristic: take the whole line as an item for now
        $items[] = $line;
    }
    return $items;
}

// Save Test Bill/Report
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_test_bill'])) {
    $p_id = $_POST['patient_id'];
    $test_bill_items = $_POST['test_item'] ?? [];
    $test_bill_amounts = $_POST['test_amount'] ?? [];

    $formatted_bill = [];
    foreach ($test_bill_items as $index => $item) {
        $item = trim($item);
        $amount = (float)($test_bill_amounts[$index] ?? 0);
        if (!empty($item) && $amount >= 0) {
            $formatted_bill[] = htmlspecialchars($item) . ': ' . number_format($amount, 2);
        }
    }
    $test_bill_string = implode("\n", $formatted_bill);

    $stmt = $conn->prepare("UPDATE patients SET test_bill = ? WHERE id = ?");
    if ($stmt->execute([$test_bill_string, $p_id])) {
        $success = "Test bill/report saved successfully!";
    } else {
        $error = "Failed to save test bill.";
    }
}

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

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="icon-badge shadow-sm" style="width: 40px; height: 40px; border-radius: 10px; background: #0072c3; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fas fa-microscope"></i>
        </div>
        <h4>Lab Portal</h4>
    </div>
    <div class="p-3">
        <p class="small text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.7rem;">Main Menu</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-flask"></i> Assigned Tests</a></li>
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
            <h2 class="fw-bold mb-1">Laboratory Dashboard</h2>
            <p class="text-muted mb-0">Review assigned tests and upload medical reports.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <p class="small fw-bold mb-0"><?php echo $_SESSION['test_manager_name']; ?></p>
                <p class="small text-muted mb-0">Lab Manager</p>
            </div>
            <div class="icon-badge" style="width: 48px; height: 48px; background: #e8f0fe; color: #0072c3; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-vial"></i>
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
                    <h5 class="mb-0">Tests Assigned & Billing</h5>
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
                                    <th>Uploaded Report</th>
                                    <th>Test Bill/Report Details</th>
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
                                                <form method="POST" class="mb-0">
                                                    <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                                                    <div id="test_bill_form_<?php echo $p['id']; ?>">
                                                        <?php
                                                        $currentBillItems = [];
                                                        if (!empty($p['test_bill'])) {
                                                            $parsedBill = parseBill($p['test_bill']);
                                                            foreach ($parsedBill['items'] as $item) {
                                                                $currentBillItems[] = ['item' => $item['item'], 'amount' => $item['amount']];
                                                            }
                                                        } else {
                                                            // If no bill, try to pre-populate from assigned tests
                                                            $testItems = parseTestsForBilling($p['tests']);
                                                            foreach ($testItems as $item) {
                                                                $currentBillItems[] = ['item' => $item, 'amount' => ''];
                                                            }
                                                        }

                                                        foreach ($currentBillItems as $index => $billItem):
                                                        ?>
                                                            <div class="input-group input-group-sm mb-1 test-item-row">
                                                                <input type="text" name="test_item[]" class="form-control" placeholder="Test Name" value="<?php echo htmlspecialchars($billItem['item']); ?>">
                                                                <input type="number" name="test_amount[]" class="form-control text-end" placeholder="Amount" step="0.01" value="<?php echo htmlspecialchars($billItem['amount']); ?>">
                                                                <button type="button" class="btn btn-outline-danger remove-test-item"><i class="fas fa-times"></i></button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                                        <span class="small fw-bold">Total Amount:</span>
                                                        <span class="small fw-bold text-info bill-total" id="bill_total_<?php echo $p['id']; ?>">₹<?php echo number_format($parsedBill['total'] ?? 0, 2); ?></span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2 add-test-item" data-patient-id="<?php echo $p['id']; ?>"><i class="fas fa-plus me-1"></i> Add Item</button>
                                                    <button type="submit" name="save_test_bill" class="btn btn-sm btn-info w-100 text-dark">
                                                        <i class="fas fa-save me-1"></i> Save Bill
                                                    </button>
                                                </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update total for a specific patient
    function updatePatientTotal(patientId) {
        const container = document.getElementById(`test_bill_form_${patientId}`);
        const totalDisplay = document.getElementById(`bill_total_${patientId}`);
        const amounts = container.querySelectorAll('input[name="test_amount[]"]');
        let total = 0;
        amounts.forEach(input => {
            const val = parseFloat(input.value) || 0;
            total += val;
        });
        totalDisplay.textContent = '₹' + total.toFixed(2);
    }

    // Add Item functionality
    document.querySelectorAll('.add-test-item').forEach(button => {
        button.addEventListener('click', function() {
            const patientId = this.dataset.patientId;
            const container = document.getElementById(`test_bill_form_${patientId}`);
            const newRow = document.createElement('div');
            newRow.classList.add('input-group', 'input-group-sm', 'mb-1', 'test-item-row');
            newRow.innerHTML = `
                <input type="text" name="test_item[]" class="form-control" placeholder="Test Name">
                <input type="number" name="test_amount[]" class="form-control text-end test-amount-input" placeholder="Amount" step="0.01">
                <button type="button" class="btn btn-outline-danger remove-test-item"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(newRow);
            
            // Add listener to new input
            newRow.querySelector('.test-amount-input').addEventListener('input', () => updatePatientTotal(patientId));
        });
    });

    // Remove Item functionality (delegated event listener)
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-test-item') || event.target.closest('.remove-test-item')) {
            const button = event.target.closest('.remove-test-item');
            const row = button.closest('.test-item-row');
            const container = row.closest('[id^="test_bill_form_"]');
            const patientId = container.id.replace('test_bill_form_', '');
            row.remove();
            updatePatientTotal(patientId);
        }
    });

    // Add initial listeners to existing amount inputs
    document.querySelectorAll('input[name="test_amount[]"]').forEach(input => {
        const container = input.closest('[id^="test_bill_form_"]');
        if (container) {
            const patientId = container.id.replace('test_bill_form_', '');
            input.addEventListener('input', () => updatePatientTotal(patientId));
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
