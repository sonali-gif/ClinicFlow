<?php
require_once 'db_config.php';
require_once 'includes/utils.php';

$patient = null;
$error = '';
$search_id = $_GET['id'] ?? '';

if ($search_id) {
    $stmt = $conn->prepare("
        SELECT p.*, d.full_name as doctor_name, d.specialization 
        FROM patients p 
        LEFT JOIN doctors d ON p.assigned_doctor_id = d.id 
        WHERE p.patient_id = ?
    ");
    $stmt->execute([$search_id]);
    $patient = $stmt->fetch();

    if (!$patient) {
        $error = "No patient found with ID: " . htmlspecialchars($search_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient & Family Portal - ClinicFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #0f62fe;
            --primary-light: #e8f0fe;
            --bg-light: #f4f7fb;
        }
        body { 
            background-color: var(--bg-light); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #161616;
        }
        .portal-header { 
            background: #ffffff; 
            padding: 40px 0; 
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 40px;
        }
        .status-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
            background: #ffffff;
        }
        .search-container {
            max-width: 600px;
            margin: -30px auto 0;
            position: relative;
            z-index: 10;
        }
        .search-container .form-control {
            height: 60px;
            border-radius: 16px;
            padding-left: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            font-size: 1.1rem;
        }
        .search-container .btn {
            position: absolute;
            right: 8px;
            top: 8px;
            height: 44px;
            border-radius: 12px;
            padding: 0 24px;
        }
        .info-label {
            color: #525252;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 700;
            color: #161616;
            margin-bottom: 16px;
        }
        .badge-status { 
            font-size: 0.9rem; 
            padding: 8px 16px; 
            border-radius: 100px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="portal-header">
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
            <div class="icon-badge shadow-sm" style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fas fa-hospital"></i>
            </div>
            <h2 class="fw-bold mb-0">ClinicFlow Portal</h2>
        </div>
        <p class="text-muted">Real-time medical updates for patients & families</p>
    </div>
</div>

<div class="container mb-5">
    <div class="search-container mb-5">
        <form action="patient_dashboard.php" method="GET" class="position-relative">
            <input type="text" name="id" class="form-control" placeholder="Enter Patient ID (e.g. PAT-001)" value="<?php echo htmlspecialchars($search_id); ?>" required>
            <button class="btn btn-primary" type="submit"><i class="fas fa-search me-2"></i>Track</button>
        </form>
    </div>

<div class="container mb-5">
    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($patient): ?>
        <div class="row g-4">
            <!-- Patient Info -->
            <div class="col-md-4">
                <div class="card status-card h-100 shadow-sm border-0">
                    <div class="card-body p-4 text-center">
                        <div class="icon-badge mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; background: #f4f7fb; color: var(--primary); border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800;">
                            <?php echo substr($patient['name'], 0, 1); ?>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($patient['name']); ?></h3>
                        <span class="badge bg-light text-primary px-3 py-2 rounded-pill mb-4"><?php echo htmlspecialchars($patient['patient_id']); ?></span>
                        
                        <div class="text-start border-top pt-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <p class="info-label mb-1">Age</p>
                                    <p class="info-value mb-0"><?php echo $patient['age']; ?> Years</p>
                                </div>
                                <div class="col-6">
                                    <p class="info-label mb-1">Gender</p>
                                    <p class="info-value mb-0"><?php echo $patient['gender']; ?></p>
                                </div>
                                <div class="col-12">
                                    <p class="info-label mb-1">Admission</p>
                                    <p class="info-value mb-0"><?php echo date('d M Y, H:i', strtotime($patient['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary w-100" id="btnShowShareQR">
                                <i class="fas fa-qrcode me-2"></i>Share Tracking QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Status -->
            <div class="col-md-8">
                <div class="card status-card mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-bold mb-0">Current Status</h4>
                            <?php 
                            $statusColor = $patient['status'] == 'Pending' ? '#856404' : ($patient['status'] == 'In Progress' ? '#0f62fe' : '#198038');
                            $statusBg = $patient['status'] == 'Pending' ? '#fff9e6' : ($patient['status'] == 'In Progress' ? '#e8f0fe' : '#e5f6ed');
                            ?>
                            <span class="badge-status" style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>;">
                                <i class="fas fa-circle me-1 small"></i> <?php echo $patient['status']; ?>
                            </span>
                        </div>
                        
                        <div class="row g-4 p-4 rounded-4" style="background: #fbfbfb; border: 1px solid #f0f0f0;">
                            <div class="col-md-6">
                                <p class="info-label">Assigned Doctor</p>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-badge shadow-sm" style="width: 44px; height: 44px; background: #ffffff; color: #24a148; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($patient['doctor_name'] ?: 'Not Assigned'); ?></h5>
                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($patient['specialization'] ?: 'General Practice'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 border-start-md">
                                <p class="info-label">Next Appointment</p>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-badge shadow-sm" style="width: 44px; height: 44px; background: #ffffff; color: #0072c3; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-primary">
                                            <?php echo $patient['next_appointment'] ? date('d M Y', strtotime($patient['next_appointment'])) : 'To be scheduled'; ?>
                                        </h5>
                                        <p class="small text-muted mb-0">Follow-up visit</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="row g-4">
                    <!-- Prescription -->
                    <div class="col-md-6">
                        <div class="card status-card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-purple"><i class="fas fa-pills me-2"></i> Prescription & Billing</h5>
                            </div>
                            <div class="card-body">
                                <h6>Prescription:</h6>
                                <?php if ($patient['prescription']): ?>
                                    <?php
                                    $prescriptionData = json_decode($patient['prescription'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($prescriptionData)): ?>
                                        <div class="table-responsive small mb-3">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Medicine</th>
                                                        <th>Dosage</th>
                                                        <th>Freq.</th>
                                                        <th>Duration</th>
                                                        <th>Instructions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($prescriptionData as $medicine): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($medicine['name'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($medicine['dosage'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($medicine['frequency'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($medicine['duration'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($medicine['instructions'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($patient['prescription'])); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted italic small">No prescription details available yet.</p>
                                <?php endif; ?>

                                <?php if ($patient['prescription_file']): ?>
                                    <a href="uploads/<?php echo $patient['prescription_file']; ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100 mb-3">
                                        <i class="fas fa-file-pdf me-2"></i> View Signed Prescription
                                    </a>
                                <?php endif; ?>

                                <hr>
                                <h6>Medicine Bill:</h6>
                                <?php if ($patient['medicine_bill']): ?>
                                    <?php $medicineBillData = parseBill($patient['medicine_bill']); ?>
                                    <?php if (!empty($medicineBillData['items'])): ?>
                                        <div class="table-responsive small mb-2">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($medicineBillData['items'] as $item): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['item']); ?></td>
                                                            <td class="text-end">₹<?php echo number_format($item['amount'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th class="text-end">Total:</th>
                                                        <th class="text-end">₹<?php echo number_format($medicineBillData['total'], 2); ?></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted italic small mb-0">Bill details not in a recognizable format.</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted italic small mb-0">Bill not generated yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tests -->
                    <div class="col-md-6">
                        <div class="card status-card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-info"><i class="fas fa-microscope me-2"></i> Assigned Tests & Billing</h5>
                            </div>
                            <div class="card-body">
                                <h6>Tests Required:</h6>
                                <?php if ($patient['tests']): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($patient['tests'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted italic small">No tests assigned yet.</p>
                                <?php endif; ?>

                                <?php if ($patient['test_file']): ?>
                                    <a href="uploads/<?php echo $patient['test_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info w-100 mb-3">
                                        <i class="fas fa-file-alt me-2"></i> View Test Report
                                    </a>
                                <?php endif; ?>

                                <hr>
                                <h6>Test Bill/Report Details:</h6>
                                <?php if ($patient['test_bill']): ?>
                                    <?php $testBillData = parseBill($patient['test_bill']); ?>
                                    <?php if (!empty($testBillData['items'])): ?>
                                        <div class="table-responsive small mb-0">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($testBillData['items'] as $item): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['item']); ?></td>
                                                            <td class="text-end">₹<?php echo number_format($item['amount'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th class="text-end">Total:</th>
                                                        <th class="text-end">₹<?php echo number_format($testBillData['total'], 2); ?></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted italic small mb-0">Report details not in a recognizable format.</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted italic small mb-0">Report details not available yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (!$error): ?>
        <div class="text-center py-5">
            <div class="opacity-25 mb-4">
                <i class="fas fa-hospital-user fa-10x"></i>
            </div>
            <h3 class="text-muted">Enter a Patient ID above to view real-time status</h3>
            <p class="text-muted">You can find the Patient ID on the registration slip provided at the main desk.</p>
        </div>
    <?php endif; ?>
</div>

<div class="text-center mt-5 mb-4">
    <a href="index.php" class="text-decoration-none text-muted fw-bold"><i class="fas fa-arrow-left me-1 small"></i> Back to ClinicFlow Home</a>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="shareQRModal" tabindex="-1" aria-labelledby="shareQRModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="icon-badge mx-auto mb-3" style="width: 56px; height: 56px; background: #e8f0fe; color: var(--primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h5 class="fw-bold mb-1">Share History</h5>
                <p class="small text-muted mb-4">Scan this code to track this patient's real-time status.</p>
                <div id="share_qrcode" class="d-flex justify-content-center mb-3 p-3 bg-light rounded-4"></div>
                <h6 class="fw-bold mb-0 small text-primary"><?php echo htmlspecialchars($patient['name']); ?></h6>
                <p class="text-muted tiny mb-0" style="font-size: 0.7rem;">ID: <?php echo htmlspecialchars($patient['patient_id']); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnShowShareQR = document.getElementById('btnShowShareQR');
    if (btnShowShareQR) {
        const shareQRModal = new bootstrap.Modal(document.getElementById('shareQRModal'));
        const shareQrcodeContainer = document.getElementById('share_qrcode');
        const currentUrl = window.location.href;

        btnShowShareQR.addEventListener('click', function() {
            shareQrcodeContainer.innerHTML = '';
            new QRCode(shareQrcodeContainer, {
                text: currentUrl,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#f8f9fa",
                correctLevel : QRCode.CorrectLevel.H
            });
            shareQRModal.show();
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
