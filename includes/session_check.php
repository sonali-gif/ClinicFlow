<?php
session_start();
require_once __DIR__ . '/test_mode.php';

if (defined('TEST_SESSION_BYPASS') && TEST_SESSION_BYPASS) {
    if (!isset($_SESSION['manager_id'])) {
        $_SESSION['manager_id'] = 1;
        $_SESSION['manager_name'] = 'Test Manager';
    }
    if (!isset($_SESSION['doctor_id'])) {
        $_SESSION['doctor_id'] = 1;
        $_SESSION['doctor_name'] = 'Dr. Test';
    }
    if (!isset($_SESSION['test_manager_id'])) {
        $_SESSION['test_manager_id'] = 1;
        $_SESSION['test_manager_name'] = 'Test Dept Manager';
    }
    if (!isset($_SESSION['pharmacist_id'])) {
        $_SESSION['pharmacist_id'] = 1;
        $_SESSION['pharmacist_name'] = 'Hospital Pharmacist';
    }
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'System Administrator';
    }
}

function checkSession($role) {
    if (defined('TEST_SESSION_BYPASS') && TEST_SESSION_BYPASS) {
        return true;
    }
    if (!isset($_SESSION[$role . '_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
