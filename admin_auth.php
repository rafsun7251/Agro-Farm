<?php

// ==========================================
// START SESSION
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


// ==========================================
// CHECK ADMIN ROLE
// ==========================================

if (
    !isset($_SESSION['role'])
    ||
    $_SESSION['role'] !== 'admin'
) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();

}

?>