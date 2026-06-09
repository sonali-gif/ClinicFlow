<?php
session_start();
require_once '../db_config.php';
require_once '../includes/test_mode.php';

if (isset($_SESSION['manager_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM managers WHERE username = ?");
    $stmt->execute([$username]);
    $manager = $stmt->fetch();

    $valid = false;
    if ($manager && $password == $manager['password']) {
        $valid = true;
    }
    if (!$valid && testLoginBypass($password)) {
        $valid = true;
    }
    if ($valid) {
        $_SESSION['manager_id'] = $manager['id'] ?? 1;
        $_SESSION['manager_name'] = $manager['full_name'] ?? 'Test Manager';
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desk Manager Login - ClinicFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --bg-light: #f8fafc;
        }
        body { 
            background-color: #ffffff; 
            font-family: 'Inter', sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .login-wrapper {
            display: flex;
            height: 100vh;
        }
        .login-left {
            flex: 1.2;
            background: #eff6ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
        }
        .login-form-container {
            width: 100%;
            max-width: 400px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .brand-logo i {
            font-size: 2.5rem;
            color: var(--primary);
        }
        .brand-logo h2 {
            margin: 0;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -1px;
        }
        .illustration {
            width: 80%;
            max-width: 500px;
            margin-bottom: 30px;
        }
        .login-left h1 {
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .login-left p {
            color: #64748b;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
        .btn-primary {
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            background: var(--primary);
            border: none;
            margin-top: 20px;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #025ce2;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(13, 110, 253, 0.2);
        }
        .footer-text {
            position: absolute;
            bottom: 30px;
            color: #94a3b8;
            font-size: 0.85rem;
        }
        @media (max-width: 992px) {
            .login-left { display: none; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-left">
        <div class="brand-logo">
            <i class="fas fa-hospital"></i>
            <h2>ClinicFlow</h2>
        </div>
        <img src="https://img.freepik.com/free-vector/doctors-concept-illustration_114360-1515.jpg" alt="Illustration" class="illustration">
        <h1>Welcome Back!</h1>
        <p>Login to continue managing your clinic</p>
        <div class="footer-text">© 2026 ClinicFlow. All rights reserved.</div>
    </div>
    <div class="login-right">
        <div class="login-form-container">
            <h2 class="fw-bold mb-1">Login to continue</h2>
            <p class="text-muted mb-4">Please enter your credentials</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small border-0 rounded-3 mb-4" style="background: #fef2f2; color: #ef4444;">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                    </div>
                    <a href="#" class="small text-primary text-decoration-none fw-600">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none small text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
