<?php
session_start();

require_once __DIR__ . '/../model/m_koneksi.php';

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'siswa'
) {
    header("Location: v_login.php");
    exit;
}

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$idSiswa = $_SESSION['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT
        p.id AS pengaduan_id,
        p.judul,
        p.isi,
        p.tanggal AS tanggal_pengaduan,
        p.status,
        k.nama_kategori,
        t.isi_tanggapan,
        t.tanggal AS tanggal_tanggapan
    FROM pengaduan p
    LEFT JOIN kategori k
        ON p.kategori_id = k.id
    INNER JOIN tanggapan t
        ON t.pengaduan_id = p.id
    WHERE p.user_id = :user_id
    ORDER BY t.tanggal DESC
");

$stmt->execute(['user_id' => $idSiswa]);
$tanggapan = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatTanggal($tanggal)
{
    if (!$tanggal) {
        return '-';
    }

    $waktu = strtotime($tanggal);

    return $waktu ? date('d-m-Y H:i', $waktu) : e($tanggal);
}

function classStatus($status)
{
    switch (strtolower(trim((string) $status))) {
        case 'menunggu':
            return 'menunggu';
        case 'diproses':
            return 'diproses';
        case 'selesai':
            return 'selesai';
        default:
            return 'lainnya';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggapan Saya</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #1e293b;
            transition: background .2s, color .2s;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            padding: 25px 16px;
            background: #0f1035;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 28px;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #365486;
            font-size: 20px;
        }

        .logo h2 {
            margin: 0;
            font-size: 17px;
        }

        .logo p {
            margin: 5px 0 0;
            color: #cbd5e1;
            font-size: 12px;
        }

        .menu-title {
            margin: 28px 12px 12px;
            color: #9ca3af;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            margin-bottom: 7px;
            border-radius: 8px;
            color: #dbe4f0;
            text-decoration: none;
            font-size: 14px;
            transition: background .2s, color .2s;
        }

        .menu a i {
            width: 20px;
            flex-shrink: 0;
            text-align: center;
        }

        .menu a:hover,
        .menu a.active {
            background: #365486;
            color: white;
        }

        /* LOGOUT */

        .menu a[href="../asset/aksi_logout.php"] {
            background: #b83232;
            color: white;
        }

        .menu a[href="../asset/aksi_logout.php"]:hover {
            background: #d64545;
            color: white;
        }

        /* CONTENT */

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 25px;
        }

        .topbar p {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .theme-button {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            color: #365486;
            cursor: pointer;
        }

        /* SUMMARY */

        .summary {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 22px;
            padding: 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 18px rgba(15,16,53,.05);
        }

        .summary-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #dce8f8;
            color: #365486;
            font-size: 21px;
        }

        .summary h3 {
            margin: 0 0 5px;
            font-size: 14px;
            color: #64748b;
            font-weight: normal;
        }

        .summary strong {
            font-size: 25px;
        }

        /* CONTENT CARD */

        .content-card {
            padding: 24px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 18px rgba(15,16,53,.05);
        }

        .content-card h2 {
            margin: 0 0 20px;
            font-size: 18px;
        }

        .tanggapan-card {
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .tanggapan-card:last-child {
            margin-bottom: 0;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .card-top h3 {
            margin: 0 0 8px;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            color: #64748b;
            font-size: 12px;
        }

        .meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* STATUS */

        .status {
            display: inline-block;
            flex-shrink: 0;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status.menunggu {
            background: #fef3c7;
            color: #92400e;
        }

        .status.diproses {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status.selesai {
            background: #dcfce7;
            color: #166534;
        }

        .status.lainnya {
            background: #e5e7eb;
            color: #374151;
        }

        .label {
            margin: 18px 0 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .isi-pengaduan,
        .isi-tanggapan {
            padding: 13px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.7;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .isi-pengaduan {
            background: #f8fafc;
            color: #475569;
        }

        .isi-tanggapan {
            background: #eef4ff;
            color: #243b61;
            border-left: 4px solid #365486;
        }

        /* EMPTY */

        .empty {
            padding: 45px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty i {
            margin-bottom: 15px;
            font-size: 38px;
            color: #94a3b8;
        }

        .empty h3 {
            margin: 0 0 8px;
            color: #334155;
            font-size: 17px;
        }

        .empty p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        /* DARK MODE */

        body.dark {
            background: #111827;
            color: #e5e7eb;
        }

        body.dark .topbar p {
            color: #9ca3af;
        }

        body.dark .summary,
        body.dark .content-card {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark .summary-icon {
            background: #263e69;
            color: #e5e7eb;
        }

        body.dark .summary h3,
        body.dark .meta {
            color: #9ca3af;
        }

        body.dark .tanggapan-card {
            border-color: #374151;
        }

        body.dark .isi-pengaduan {
            background: #111827;
            color: #d1d5db;
        }

        body.dark .isi-tanggapan {
            background: #263449;
            color: #e5e7eb;
            border-left-color: #7fc7d9;
        }

        body.dark .empty,
        body.dark .empty h3 {
            color: #9ca3af;
        }

        body.dark .theme-button {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        /* RESPONSIVE */

        @media (max-width: 760px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 16px;
            }

            .logo {
                padding-bottom: 15px;
            }

            .menu-title {
                margin-top: 18px;
            }

            .menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 5px;
            }

            .menu a {
                min-width: 0;
                margin-bottom: 0;
                padding: 11px 9px;
                font-size: 12px;
            }

            .main {
                margin-left: 0;
                padding: 20px 14px;
            }

            .topbar h1 {
                font-size: 21px;
            }

            .content-card {
                padding: 17px 13px;
            }

            .tanggapan-card {
                padding: 15px 12px;
            }

            .card-top {
                flex-direction: column;
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
            <div>
                <h2>Pengaduan Sekolah</h2>
                <p>Portal Siswa</p>
            </div>
        </div>

        <p class="menu-title">Menu Utama</p>

        <nav class="menu">
            <a href="v_dashboard_siswa.php">
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

            <a href="v_tanggapan_siswa.php" class="active">
                <i class="fa-solid fa-comments"></i>
                <span>Tanggapan</span>
            </a>

            <a href="v_profil_siswa.php">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>

            <a href="../asset/aksi_logout.php"
               onclick="return confirm('Yakin ingin logout?')">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- CONTENT -->

    <main class="main">
        <div class="topbar">
            <div>
                <h1>Tanggapan Saya</h1>
                <p>Lihat tanggapan admin terhadap pengaduan yang kamu kirim.</p>
            </div>

            <button type="button"
                    class="theme-button"
                    id="tombolTema"
                    aria-label="Ganti tema">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <div class="summary">
            <div class="summary-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <h3>Total Pengaduan yang Sudah Ditanggapi</h3>
                <strong><?= count($tanggapan); ?></strong>
            </div>
        </div>

        <section class="content-card">
            <h2>Daftar Tanggapan</h2>

            <?php if (count($tanggapan) > 0): ?>
                <?php foreach ($tanggapan as $item): ?>
                    <article class="tanggapan-card">
                        <div class="card-top">
                            <div>
                                <h3><?= e($item['judul']); ?></h3>

                                <div class="meta">
                                    <span>
                                        <i class="fa-solid fa-tag"></i>
                                        <?= e($item['nama_kategori'] ?: 'Tanpa kategori'); ?>
                                    </span>

                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        <?= e(formatTanggal($item['tanggal_pengaduan'])); ?>
                                    </span>
                                </div>
                            </div>

                            <span class="status <?= e(classStatus($item['status'])); ?>">
                                <?= e($item['status']); ?>
                            </span>
                        </div>

                        <p class="label">Isi Pengaduan</p>
                        <div class="isi-pengaduan"><?= e($item['isi']); ?></div>

                        <p class="label">Tanggapan Admin</p>
                        <div class="isi-tanggapan"><?= e($item['isi_tanggapan']); ?></div>

                        <div class="meta" style="margin-top: 12px;">
                            <span>
                                <i class="fa-regular fa-clock"></i>
                                Ditanggapi:
                                <?= e(formatTanggal($item['tanggal_tanggapan'])); ?>
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <i class="fa-regular fa-comment-dots"></i>
                    <h3>Belum Ada Tanggapan</h3>
                    <p>
                        Pengaduan kamu belum memiliki tanggapan dari admin.
                        Silakan periksa kembali nanti.
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        const body = document.body;
        const tombolTema = document.getElementById('tombolTema');

        function perbaruiIkonTema() {
            const ikon = tombolTema.querySelector('i');

            ikon.className = body.classList.contains('dark')
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
        }

        // Memuat tema siswa dengan aman.
        try {
            if (localStorage.getItem('siswaTheme') === 'dark') {
                body.classList.add('dark');
            }
        } catch (error) {
            // Halaman tetap berjalan jika penyimpanan browser tidak tersedia.
        }

        perbaruiIkonTema();

        tombolTema.addEventListener('click', function () {
            body.classList.toggle('dark');

            try {
                localStorage.setItem(
                    'siswaTheme',
                    body.classList.contains('dark') ? 'dark' : 'light'
                );
            } catch (error) {
                // Perubahan tema tetap berlaku selama halaman terbuka.
            }

            perbaruiIkonTema();
        });
    </script>
</body>
</html>