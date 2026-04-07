<?php
session_start();
require_once '../db_config.php';

if (isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $specialization = $_POST['specialization'];

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM doctors WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = "Username already exists. Please choose another one.";
    } else {
        // Insert new doctor
        $stmt = $conn->prepare("INSERT INTO doctors (full_name, username, password, specialization) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$full_name, $username, $password, $specialization])) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration - ClinicFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container { max-width: 500px; margin-top: 50px; margin-bottom: 50px; }
    </style>
</head>
<body>
<div class="container register-container">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h3>ClinicFlow - Doctor Registration</h3>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Dr. John Doe" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g. Cardiologist" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Register</button>
            </form>
            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                <a href="../index.php" class="text-decoration-none small">Back to Home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
