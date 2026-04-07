<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ClinicFlow - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .welcome-card { max-width: 600px; width: 100%; }
        .login-option { transition: transform 0.3s; cursor: pointer; border: none; }
        .login-option:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .icon-box { font-size: 3rem; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="welcome-card mx-auto text-center">
        <h1 class="mb-5 display-4 fw-bold text-primary">ClinicFlow</h1>
        <p class="lead mb-5 text-muted">A Hospital Management System with Real-Time Patient Tracking</p>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <a href="admin/login.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4">
                        <div class="icon-box text-dark"><i class="fas fa-user-shield"></i></div>
                        <h4>System Admin</h4>
                        <p class="small text-muted">Complete oversight of all hospital processes.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="manager/login.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4">
                        <div class="icon-box text-primary"><i class="fas fa-desktop"></i></div>
                        <h4>Main Desk Login</h4>
                        <p class="small text-muted">Register and assign patients.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="doctor/login.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4">
                        <div class="icon-box text-success"><i class="fas fa-user-md"></i></div>
                        <h4>Doctor Login</h4>
                        <p class="small text-muted">Manage assigned patients.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="test_manager/login.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4">
                        <div class="icon-box text-info"><i class="fas fa-microscope"></i></div>
                        <h4>Test Manager Login</h4>
                        <p class="small text-muted">Laboratory test reports.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="pharmacy/login.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4">
                        <div class="icon-box text-purple" style="color: #6f42c1;"><i class="fas fa-pills"></i></div>
                        <h4>Pharmacy Login</h4>
                        <p class="small text-muted">View patient prescriptions.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="patient_dashboard.php" class="text-decoration-none">
                    <div class="card h-100 login-option p-4 border-primary border-2 shadow-sm">
                        <div class="icon-box text-primary"><i class="fas fa-hospital-user"></i></div>
                        <h4>Patient & Family Portal</h4>
                        <p class="small text-muted">Track status, tests, and prescriptions in real-time.</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="mt-5 text-muted">
            <p class="small">&copy; 2026 ClinicFlow – Hospital Management System</p>
        </div>
    </div>
</div>
</body>
</html>
