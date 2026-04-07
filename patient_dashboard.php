<?php
require_once 'db_config.php';

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
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .portal-header { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 60px 0; margin-bottom: 30px; border-radius: 0 0 25px 25px; }
        .status-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .badge-status { font-size: 1rem; padding: 8px 20px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="portal-header text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2">ClinicFlow Patient Portal</h1>
        <p class="lead">Real-time status and medical information for patients and families</p>
        
        <div class="row justify-content-center mt-4">
            <div class="col-md-6">
                <form action="patient_dashboard.php" method="GET" class="input-group shadow-sm">
                    <input type="text" name="id" class="form-control form-control-lg" placeholder="Enter Patient ID (e.g. PAT-001)" value="<?php echo htmlspecialchars($search_id); ?>" required>
                    <button class="btn btn-dark btn-lg" type="submit"><i class="fas fa-search me-2"></i> Track Status</button>
                </form>
            </div>
        </div>
    </div>
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
                <div class="card status-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-primary opacity-25"></i>
                        </div>
                        <h3 class="mb-1"><?php echo htmlspecialchars($patient['name']); ?></h3>
                        <p class="text-muted">Patient ID: <strong><?php echo htmlspecialchars($patient['patient_id']); ?></strong></p>
                        <hr>
                        <div class="text-start mt-3">
                            <p><strong>Age:</strong> <?php echo $patient['age']; ?> Years</p>
                            <p><strong>Gender:</strong> <?php echo $patient['gender']; ?></p>
                            <p><strong>Admission:</strong> <?php echo date('d M Y, H:i', strtotime($patient['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Status -->
            <div class="col-md-8">
                <div class="card status-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0"><i class="fas fa-spinner text-primary me-2"></i> Current Status</h4>
                            <span class="badge badge-status <?php 
                                echo $patient['status'] == 'Pending' ? 'bg-warning text-dark' : ($patient['status'] == 'In Progress' ? 'bg-primary' : 'bg-success'); 
                            ?>">
                                <?php echo $patient['status']; ?>
                            </span>
                        </div>
                        
                        <div class="row bg-light p-3 rounded-3 mb-0">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Assigned Doctor</p>
                                <h5 class="mb-0"><?php echo htmlspecialchars($patient['doctor_name'] ?: 'Not Assigned'); ?></h5>
                                <small class="text-primary"><?php echo htmlspecialchars($patient['specialization'] ?: ''); ?></small>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <p class="text-muted small mb-1">Next Appointment</p>
                                <h5 class="mb-0 text-success">
                                    <?php echo $patient['next_appointment'] ? date('d M Y', strtotime($patient['next_appointment'])) : 'To be scheduled'; ?>
                                </h5>
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
                                <h5 class="mb-0 text-purple"><i class="fas fa-pills me-2"></i> Prescription</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($patient['prescription']): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($patient['prescription'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted italic small">No prescription details available yet.</p>
                                <?php endif; ?>

                                <?php if ($patient['prescription_file']): ?>
                                    <a href="uploads/<?php echo $patient['prescription_file']; ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fas fa-file-pdf me-2"></i> View Signed Prescription
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tests -->
                    <div class="col-md-6">
                        <div class="card status-card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-info"><i class="fas fa-microscope me-2"></i> Assigned Tests</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($patient['tests']): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($patient['tests'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted italic small">No tests assigned yet.</p>
                                <?php endif; ?>

                                <?php if ($patient['test_file']): ?>
                                    <a href="uploads/<?php echo $patient['test_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                        <i class="fas fa-file-alt me-2"></i> View Test Report
                                    </a>
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
    <a href="index.php" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i> Back to ClinicFlow Home</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
