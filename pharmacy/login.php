<?php
session_start();
require_once '../db_config.php';

if (isset($_SESSION['pharmacist_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM pharmacists WHERE username = ?");
    $stmt->execute([$username]);
    $pharmacist = $stmt->fetch();

    if ($pharmacist && $password == $pharmacist['password']) {
        $_SESSION['pharmacist_id'] = $pharmacist['id'];
        $_SESSION['pharmacist_name'] = $pharmacist['full_name'];
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
    <title>Pharmacist Login - ClinicFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 400px; margin-top: 100px; }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="card shadow">
        <div class="card-header bg-purple text-white text-center" style="background-color: #6f42c1;">
            <h3>ClinicFlow - Pharmacy</h3>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-purple w-100 text-white" style="background-color: #6f42c1;">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-decoration-none small">Back to Home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
