<?php
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "../model/m_koneksi.php";

/* CEK LOGIN */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'siswa'
) {
    header("Location: v_login.php");
    exit;
}

/* DATA SISWA */
$nama = $_SESSION['nama'] ?? 'Siswa';
$username = $_SESSION['username'] ?? '-';
$kelas = $_SESSION['kelas'] ?? '-';
$user_id = $_SESSION['id'] ?? 0;

/* KONEKSI DATABASE */
$koneksi = new Koneksi();
$db = $koneksi->getConnection();

/* STATISTIK PENGADUAN */
$total = 0;
$menunggu = 0;
$diproses = 0;
$selesai = 0;

try {
    $stmt = $db->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(status = 'Menunggu'), 0) AS menunggu,
            COALESCE(SUM(status = 'Diproses'), 0) AS diproses,
            COALESCE(SUM(status = 'Selesai'), 0) AS selesai
        FROM pengaduan
        WHERE user_id = :user_id
    ");

    $stmt->execute([':user_id' => $user_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $total = (int) $data['total'];
        $menunggu = (int) $data['menunggu'];
        $diproses = (int) $data['diproses'];
        $selesai = (int) $data['selesai'];
    }
} catch (PDOException $e) {
    // Statistik tetap bernilai 0 jika query gagal.
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #222;
            transition: 0.3s;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            padding: 25px 16px;
            background: #0f1035;
            color: white;
            overflow-y: auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 55px;
            height: 55px;
            margin: 0 auto 10px;
            border-radius: 15px;
            background: #365486;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .logo h2 {
            font-size: 18px;
        }

        .logo p {
            margin-top: 5px;
            color: #b8c1d9;
            font-size: 12px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            color: #dce3f5;
            text-decoration: none;
            border-radius: 10px;
            transition: 0.2s;
        }

        .menu a i {
            width: 20px;
            text-align: center;
        }

        .menu a:hover,
        .menu a.active {
            background: #365486;
            color: white;
        }

        .logout {
            margin-top: 15px;
        }

        .menu .logout a {
            background: #b83232;
            color: white;
        }

        .menu .logout a:hover {
            background: #d64545;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .welcome h1 {
            color: #0f1035;
            font-size: 27px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }

        .theme-btn {
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            border: none;
            border-radius: 12px;
            background: #365486;
            color: white;
            font-size: 17px;
            cursor: pointer;
        }

        .welcome-card {
            padding: 28px;
            margin-bottom: 25px;
            border-radius: 18px;
            background: linear-gradient(135deg, #0f1035, #365486);
            color: white;
        }

        .welcome-card h2 {
            font-size: 23px;
            margin-bottom: 10px;
        }

        .welcome-card p {
            color: #dce5f7;
            font-size: 14px;
            line-height: 1.6;
        }

        .student-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 18px;
        }

        .student-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #dce5f7;
            font-size: 13px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: #edf2fa;
            color: #365486;
            font-size: 20px;
        }

        .stat-info h3 {
            color: #0f1035;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #777;
            font-size: 12px;
        }

        .section-title {
            color: #0f1035;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .quick-menu {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .quick-card {
            display: block;
            min-width: 0;
            padding: 23px;
            background: white;
            color: #222;
            text-decoration: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            transition: 0.2s;
        }

        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10);
        }

        .quick-icon {
            margin-bottom: 13px;
            color: #365486;
            font-size: 25px;
        }

        .quick-card h3 {
            color: #0f1035;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .quick-card p {
            color: #777;
            font-size: 13px;
            line-height: 1.6;
        }

        /* MODE GELAP */
        body.dark {
            background: #111827;
            color: #f9fafb;
        }

        body.dark .welcome h1,
        body.dark .section-title {
            color: #f9fafb;
        }

        body.dark .welcome p {
            color: #b8c1d1;
        }

        body.dark .stat-card,
        body.dark .quick-card {
            background: #1f2937;
        }

        body.dark .stat-info h3,
        body.dark .quick-card h3 {
            color: #f9fafb;
        }

        body.dark .stat-info p,
        body.dark .quick-card p {
            color: #b8c1d1;
        }

        body.dark .quick-card {
            color: #f9fafb;
        }

        body.dark .stat-icon {
            background: #273449;
            color: #7fc7d9;
        }

        body.dark .quick-icon {
            color: #7fc7d9;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 20px 14px;
            }

            .logo {
                margin-bottom: 20px;
            }

            .content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .welcome h1 {
                font-size: 23px;
            }

            .stats,
            .quick-menu {
                grid-template-columns: 1fr;
            }

            .welcome-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fa-solid fa-school"></i>
            </div>
            <h2>Pengaduan Sekolah</h2>
            <p>Portal Siswa</p>
        </div>

        <nav class="menu">
            <a href="v_dashboard_siswa.php" class="active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="v_buat_pengaduan.php">
                <i class="fa-solid fa-file-circle-plus"></i>
                <span>Buat Pengaduan</span>
            </a>

            <a href="v_pengaduan_saya.php">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Pengaduan Saya</span>
            </a>

            <a href="v_tanggapan_siswa.php">
                <i class="fa-solid fa-comments"></i>
                <span>Tanggapan</span>
            </a>

            <a href="v_profil_siswa.php">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>

            <div class="logout">
                <a href="../asset/aksi_logout.php"
                   onclick="return confirm('Yakin ingin logout?');">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="content">
        <div class="top">
            <div class="welcome">
                <h1>Dashboard Siswa</h1>
                <p>Selamat datang di sistem pengaduan sarana dan prasarana sekolah.</p>
            </div>

            <button type="button"
                    class="theme-btn"
                    id="themeButton"
                    onclick="toggleTheme()"
                    title="Ganti tema">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <section class="welcome-card">
            <h2>
                Halo, <?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>
            </h2>

            <p>
                Sampaikan keluhan atau masalah sarana dan prasarana sekolah
                melalui sistem pengaduan ini.
            </p>

            <div class="student-info">
                <span>
                    <i class="fa-solid fa-user"></i>
                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
                </span>

                <span>
                    <i class="fa-solid fa-graduation-cap"></i>
                    Kelas <?= htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </section>

        <section class="stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $total; ?></h3>
                    <p>Total Pengaduan</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $menunggu; ?></h3>
                    <p>Menunggu</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $diproses; ?></h3>
                    <p>Diproses</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $selesai; ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
        </section>

        <h2 class="section-title">Akses Cepat</h2>

        <section class="quick-menu">
            <a href="v_buat_pengaduan.php" class="quick-card">
                <div class="quick-icon">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <h3>Buat Pengaduan</h3>
                <p>Kirim laporan masalah sarana dan prasarana sekolah.</p>
            </a>

            <a href="v_pengaduan_saya.php" class="quick-card">
                <div class="quick-icon">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <h3>Pengaduan Saya</h3>
                <p>Lihat daftar pengaduan yang telah kamu kirim.</p>
            </a>

            <a href="v_tanggapan_siswa.php" class="quick-card">
                <div class="quick-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h3>Tanggapan</h3>
                <p>Lihat tanggapan admin mengenai pengaduanmu.</p>
            </a>

            <a href="v_profil_siswa.php" class="quick-card">
                <div class="quick-icon">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h3>Profil Saya</h3>
                <p>Lihat dan perbarui informasi profil akunmu.</p>
            </a>
        </section>
    </main>

    <script>
        function toggleTheme() {
            document.body.classList.toggle("dark");

            const icon = document.querySelector("#themeButton i");
            const isDark = document.body.classList.contains("dark");

            icon.className = isDark
                ? "fa-solid fa-sun"
                : "fa-solid fa-moon";

            try {
                localStorage.setItem(
                    "siswaTheme",
                    isDark ? "dark" : "light"
                );
            } catch (error) {
                // Abaikan jika localStorage tidak tersedia.
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const icon = document.querySelector("#themeButton i");
            let theme = "light";

            try {
                theme = localStorage.getItem("siswaTheme") || "light";
            } catch (error) {
                theme = "light";
            }

            if (theme === "dark") {
                document.body.classList.add("dark");
                icon.className = "fa-solid fa-sun";
            } else {
                document.body.classList.remove("dark");
                icon.className = "fa-solid fa-moon";
            }
        });
    </script>

</body>
</html>