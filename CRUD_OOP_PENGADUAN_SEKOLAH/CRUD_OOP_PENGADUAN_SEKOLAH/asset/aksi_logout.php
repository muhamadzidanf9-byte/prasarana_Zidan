<?php

session_start();

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Kembali ke halaman login
header("Location: ../view/v_login.php");
exit;

?>