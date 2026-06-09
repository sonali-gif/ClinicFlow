<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ClinicFlow - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f62fe;
            --primary-light: #e8f0fe;
            --text-dark: #161616;
            --text-muted: #525252;
        }
        body { 
            background-color: #ffffff; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }
        .hero-section {
            padding: 100px 0 60px;
            background: radial-gradient(circle at top right, var(--primary-light) 0%, #ffffff 50%);
        }
        .welcome-badge {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 24px;
        }
        .display-4 {
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-dark);
        }
        .login-card {
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 32px;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .login-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(15, 98, 254, 0.08);
        }
        .icon-box {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }
        .card-admin .icon-box { background: #f4f4f4; color: #161616; }
        .card-doctor .icon-box { background: #e5f6ed; color: #24a148; }
        .card-lab .icon-box { background: #e8f0fe; color: #0072c3; }
        .card-pharma .icon-box { background: #f6f2ff; color: #6f42c1; }
        .card-patient .icon-box { background: var(--primary-light); color: var(--primary); }

        .login-card h4 {
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-dark);
        }
        .login-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }
        .footer {
            padding: 40px 0;
            border-top: 1px solid #f4f4f4;
            margin-top: 60px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="hero-section">
    <div class="container text-center">
        <span class="welcome-badge">Welcome to the future of healthcare</span>
        <h1 class="display-4 mb-3">ClinicFlow</h1>
        <p class="lead mb-5 text-muted mx-auto" style="max-width: 600px;">
            A modern hospital management ecosystem designed for real-time tracking, seamless coordination, and better patient care.
        </p>
        
        <div class="row g-4 justify-content-center mt-2">
            <!-- Admin -->
            <div class="col-md-4 col-lg-3">
                <a href="admin/login.php" class="login-card card-admin">
                    <div class="icon-box"><i class="fas fa-user-shield"></i></div>
                    <h4>System Admin</h4>
                    <p>Manage hospital staff, departments, and core system settings.</p>
                </a>
            </div>
            <!-- Doctor -->
            <div class="col-md-4 col-lg-3">
                <a href="doctor/login.php" class="login-card card-doctor">
                    <div class="icon-box"><i class="fas fa-user-md"></i></div>
                    <h4>Doctor Portal</h4>
                    <p>Access patient records, prescriptions, and daily appointments.</p>
                </a>
            </div>
            <!-- Lab -->
            <div class="col-md-4 col-lg-3">
                <a href="test_manager/login.php" class="login-card card-lab">
                    <div class="icon-box"><i class="fas fa-microscope"></i></div>
                    <h4>Laboratory</h4>
                    <p>Process test requests and upload digital lab reports.</p>
                </a>
            </div>
            <!-- Pharmacy -->
            <div class="col-md-4 col-lg-3">
                <a href="pharmacy/login.php" class="login-card card-pharma">
                    <div class="icon-box"><i class="fas fa-pills"></i></div>
                    <h4>Pharmacy</h4>
                    <p>Fulfill medical prescriptions and manage stock alerts.</p>
                </a>
            </div>
            <!-- Desk Manager -->
            <div class="col-md-4 col-lg-3">
                <a href="manager/login.php" class="login-card card-lab" style="background: #e5f6ff;">
                    <div class="icon-box" style="background: #0072c3; color: white;"><i class="fas fa-desktop"></i></div>
                    <h4>Front Desk</h4>
                    <p>Patient registration, intake, and doctor assignments.</p>
                </a>
            </div>
            <!-- Patient Portal -->
            <div class="col-md-4 col-lg-3">
                <a href="patient_dashboard.php" class="login-card card-patient" style="border: 2px solid var(--primary);">
                    <div class="icon-box"><i class="fas fa-hospital-user"></i></div>
                    <h4>Patient Portal</h4>
                    <p>Real-time status tracking for patients and family members.</p>
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="footer text-center">
    <div class="container">
        <p class="mb-0">&copy; 2026 ClinicFlow &bull; Hospital Management System</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
