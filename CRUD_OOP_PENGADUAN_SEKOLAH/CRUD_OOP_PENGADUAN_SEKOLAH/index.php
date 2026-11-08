<?php
session_start();

// Jika pengguna sudah login
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {

    // Jika pengguna adalah admin
    if ($_SESSION['role'] == 'admin') {
        header("Location: view/v_dashboard_admin.php");
        exit;
    }

    // Jika pengguna adalah siswa
    elseif ($_SESSION['role'] == 'siswa') {
        header("Location: view/v_dashboard_siswa.php");
        exit;
    }
}

// Jika belum login, arahkan ke halaman login
header("Location: view/v_login.php");
exit;
?>