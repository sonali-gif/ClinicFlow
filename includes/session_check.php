<?php
session_start();

function checkSession($role) {
    if (!isset($_SESSION[$role . '_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
