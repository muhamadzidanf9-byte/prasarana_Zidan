<?php

session_start();

/* =========================
   CEK HAK AKSES ADMIN
========================= */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header("Location: v_login.php");
    exit;
}

/* =========================
   KONEKSI DATABASE
========================= */
require_once "../model/m_koneksi.php";

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

/* =========================
   DATA STATISTIK
========================= */
$total_siswa = (int) $conn->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'siswa'
")->fetchColumn();

$total_pengaduan = (int) $conn->query("
    SELECT COUNT(*)
    FROM pengaduan
")->fetchColumn();

$menunggu = (int) $conn->query("
    SELECT COUNT(*)
    FROM pengaduan
    WHERE status = 'Menunggu'
")->fetchColumn();

$diproses = (int) $conn->query("
    SELECT COUNT(*)
    FROM pengaduan
    WHERE status = 'Diproses'
")->fetchColumn();

$selesai = (int) $conn->query("
    SELECT COUNT(*)
    FROM pengaduan
    WHERE status = 'Selesai'
")->fetchColumn();

$nama_admin = $_SESSION['nama'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Pengaduan Sekolah</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #0f1035;
            transition: background .3s ease, color .3s ease;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            padding: 25px 15px;
            background: #0f1035;
        }

        .logo {
            margin-bottom: 35px;
            color: white;
            font-size: 21px;
            font-weight: bold;
            text-align: center;
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 65px;
            height: 65px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: #365486;
        }

        .logo-icon i {
            color: white;
            font-size: 30px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: bold;
        }

        /* MENU SIDEBAR */
        .menu {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
            padding: 14px 18px;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            text-decoration: none;
            transition: background .2s ease;
        }

        .menu i {
            flex-shrink: 0;
            width: 20px;
            font-size: 17px;
            text-align: center;
        }

        .menu:hover,
        .menu.active {
            background: #365486;
        }

        /* LOGOUT */
        .menu.logout {
            margin-top: 25px;
            background: #b83232;
            color: white;
        }

        .menu.logout:hover {
            background: #d64545;
        }

        /* KONTEN */
        .content {
            min-width: 0;
            margin-left: 250px;
            padding: 35px;
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
        }

        .top h1 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .top p {
            color: #666;
            overflow-wrap: anywhere;
        }

        /* TOMBOL TEMA */
        .theme-button {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 8px;
            background: #365486;
            color: white;
            font-size: 17px;
            cursor: pointer;
            transition: .2s ease;
        }

        .theme-button:hover {
            background: #7fc7d9;
            color: #0f1035;
        }

        /* WELCOME */
        .welcome {
            margin-bottom: 30px;
            padding: 30px;
            border-radius: 12px;
            background: linear-gradient(135deg, #365486, #7fc7d9);
            color: white;
        }

        .welcome h2 {
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .welcome p {
            line-height: 1.6;
            opacity: .95;
        }

        /* STATISTIK */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            min-width: 0;
            padding: 25px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            transition: background .3s ease, transform .2s ease;
        }

        .card:hover,
        .quick-card:hover {
            transform: translateY(-3px);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #666;
            font-size: 15px;
            line-height: 1.4;
        }

        .card-icon,
        .quick-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 8px;
            background: #365486;
            color: white;
        }

        .card-icon {
            width: 42px;
            height: 42px;
        }

        .card-icon i {
            font-size: 18px;
        }

        .card .number {
            color: #365486;
            font-size: 30px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        /* AKSES CEPAT */
        .section-title {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .quick-menu {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .quick-card {
            display: block;
            min-width: 0;
            padding: 25px;
            border-radius: 12px;
            background: white;
            color: #0f1035;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            transition: background .3s ease, transform .2s ease;
        }

        .quick-card-icon {
            width: 45px;
            height: 45px;
            margin-bottom: 15px;
        }

        .quick-card-icon i {
            font-size: 20px;
        }

        .quick-card h3 {
            margin-bottom: 8px;
            font-size: 16px;
            line-height: 1.4;
        }

        .quick-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* MODE GELAP */
        body.dark {
            background: #111827;
            color: white;
        }

        body.dark .top p {
            color: #cbd5e1;
        }

        body.dark .card,
        body.dark .quick-card {
            background: #1f2937;
            color: white;
        }

        body.dark .card h3,
        body.dark .quick-card p {
            color: #cbd5e1;
        }

        body.dark .card .number {
            color: #7fc7d9;
        }

        body.dark .section-title {
            color: white;
        }

        /* RESPONSIVE TABLET */
        @media (max-width: 1100px) {
            .stats,
            .quick-menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* RESPONSIVE HP */
        @media (max-width: 700px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                overflow: visible;
                padding: 18px 15px;
            }

            .logo {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                margin-bottom: 20px;
                text-align: left;
            }

            .logo-icon {
                flex-shrink: 0;
                width: 45px;
                height: 45px;
                margin: 0;
                border-radius: 10px;
            }

            .logo-icon i {
                font-size: 21px;
            }

            .logo-text {
                font-size: 18px;
            }

            .menu {
                gap: 8px;
                padding: 14px 10px;
                font-size: 12px;
            }

            .menu i {
                font-size: 15px;
            }

            .menu.logout {
                margin-top: 5px;
            }

            .content {
                width: 100%;
                margin-left: 0;
                padding: 20px 14px;
            }

            .top {
                align-items: center;
                margin-bottom: 22px;
            }

            .top h1 {
                font-size: 22px;
            }

            .top p {
                font-size: 13px;
                line-height: 1.5;
            }

            .welcome {
                padding: 22px 18px;
            }

            .welcome h2 {
                font-size: 19px;
            }

            .welcome p {
                font-size: 13px;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .card {
                padding: 17px 14px;
            }

            .card-header {
                align-items: flex-start;
                gap: 7px;
            }

            .card h3 {
                font-size: 13px;
            }

            .card-icon {
                width: 34px;
                height: 34px;
            }

            .card-icon i {
                font-size: 15px;
            }

            .card .number {
                font-size: 25px;
            }

            .quick-menu {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .quick-card {
                padding: 20px;
            }
        }

        @media (max-width: 360px) {
            .menu {
                gap: 6px;
                padding: 11px 7px;
                font-size: 11px;
            }

            .content {
                padding: 16px 10px;
            }

            .stats {
                gap: 9px;
            }

            .card {
                padding: 14px 10px;
            }

            .card .number {
                font-size: 22px;
            }

            .card h3 {
                font-size: 12px;
            }

            .card-icon {
                width: 30px;
                height: 30px;
            }

            .top h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="logo">
            <div class="logo-icon">
                <i class="fa-solid fa-school"></i>
            </div>

            <div class="logo-text">
                Pengaduan Sekolah
            </div>
        </div>

        <a href="v_dashboard_admin.php" class="menu active">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="v_kelola_siswa.php" class="menu">
            <i class="fa-solid fa-users"></i>
            <span>Kelola Data Siswa</span>
        </a>

        <a href="v_data_pengaduan.php" class="menu">
            <i class="fa-solid fa-file-lines"></i>
            <span>Data Pengaduan</span>
        </a>

        <a href="v_tanggapan.php" class="menu">
            <i class="fa-solid fa-comments"></i>
            <span>Tanggapan</span>
        </a>

        <a href="v_kategori.php" class="menu">
            <i class="fa-solid fa-layer-group"></i>
            <span>Kategori</span>
        </a>

        <a
            href="../asset/aksi_logout.php"
            class="menu logout"
            onclick="return confirm('Yakin ingin logout?');"
        >
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </aside>

    <!-- KONTEN UTAMA -->
    <main class="content">

        <header class="top">
            <div>
                <h1>Dashboard Admin</h1>
                <p>
                    Selamat datang,
                    <?= htmlspecialchars($nama_admin, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <button
                type="button"
                class="theme-button"
                id="themeButton"
                title="Ganti Tema"
                aria-label="Ganti tema"
            >
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
        </header>

        <!-- WELCOME -->
        <section class="welcome">
            <h2>Selamat Datang di Sistem Pengaduan Sekolah</h2>
            <p>
                Kelola data siswa, pengaduan, status pengaduan,
                dan tanggapan melalui halaman admin.
            </p>
        </section>

        <!-- STATISTIK -->
        <section class="stats">

            <div class="card">
                <div class="card-header">
                    <h3>Total Siswa</h3>
                    <div class="card-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="number">
                    <?= $total_siswa; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Total Pengaduan</h3>
                    <div class="card-icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                </div>
                <div class="number">
                    <?= $total_pengaduan; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Menunggu</h3>
                    <div class="card-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="number">
                    <?= $menunggu; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Diproses</h3>
                    <div class="card-icon">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </div>
                <div class="number">
                    <?= $diproses; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Pengaduan Selesai</h3>
                    <div class="card-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="number">
                    <?= $selesai; ?>
                </div>
            </div>

        </section>

        <!-- AKSES CEPAT -->
        <h2 class="section-title">Akses Cepat</h2>

        <section class="quick-menu">

            <a href="v_kelola_siswa.php" class="quick-card">
                <div class="quick-card-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Kelola Data Siswa</h3>
                <p>Tambah, edit, dan hapus data siswa.</p>
            </a>

            <a href="v_data_pengaduan.php" class="quick-card">
                <div class="quick-card-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <h3>Data Pengaduan</h3>
                <p>Lihat dan ubah status pengaduan siswa.</p>
            </a>

            <a href="v_tanggapan.php" class="quick-card">
                <div class="quick-card-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h3>Tanggapan</h3>
                <p>Berikan tanggapan terhadap pengaduan siswa.</p>
            </a>

        </section>

    </main>

    <!-- JAVASCRIPT TEMA -->
    <script>
        const themeButton = document.getElementById("themeButton");
        const themeIcon = document.getElementById("themeIcon");

        function updateThemeIcon() {
            themeIcon.className = document.body.classList.contains("dark")
                ? "fa-solid fa-sun"
                : "fa-solid fa-moon";
        }

        function loadTheme() {
            try {
                const savedTheme = localStorage.getItem("adminTheme");

                document.body.classList.toggle(
                    "dark",
                    savedTheme === "dark"
                );
            } catch (error) {
                // Halaman tetap berjalan jika localStorage tidak tersedia.
            }

            updateThemeIcon();
        }

        themeButton.addEventListener("click", function () {
            document.body.classList.toggle("dark");

            try {
                localStorage.setItem(
                    "adminTheme",
                    document.body.classList.contains("dark")
                        ? "dark"
                        : "light"
                );
            } catch (error) {
                // Tema tetap berubah selama halaman terbuka.
            }

            updateThemeIcon();
        });

        loadTheme();
    </script>

</body>
</html>