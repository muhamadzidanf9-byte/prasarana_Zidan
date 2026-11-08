<?php
session_start();

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'siswa'
) {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . '/../model/m_koneksi.php';

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$user_id = $_SESSION['id'] ?? 0;
$nama_siswa = $_SESSION['nama'] ?? 'Siswa';

$sql = "
    SELECT
        p.id,
        p.judul,
        p.isi,
        p.tanggal,
        p.status,
        p.foto,
        k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    WHERE p.user_id = ?
    ORDER BY p.tanggal DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusClass($status)
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
    <title>Pengaduan Saya</title>

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
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            padding: 25px 16px;
            background: #0f1035;
            color: white;
            z-index: 100;
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
            flex-shrink: 0;
            width: 43px;
            height: 43px;
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

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu li {
            margin-bottom: 7px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
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
        .menu li:last-child a {
            background: #b83232;
            color: white;
        }

        .menu li:last-child a:hover {
            background: #d64545;
            color: white;
        }

        /* CONTENT */
        .main {
            min-width: 0;
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
            line-height: 1.5;
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

        /* CARD */
        .card {
            width: 100%;
            padding: 24px;
            border-radius: 14px;
            background: white;
            box-shadow: 0 4px 18px rgba(15,16,53,.06);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-header i {
            color: #365486;
            font-size: 21px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 18px;
            color: #1e293b;
        }

        /* TABLE */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 8px;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th {
            padding: 15px;
            background: #0f1035;
            color: white;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
        }

        th:first-child {
            border-radius: 8px 0 0 0;
        }

        th:last-child {
            border-radius: 0 8px 0 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 13px;
            line-height: 1.6;
        }

        tbody tr:hover td {
            background: #f8faff;
        }

        .isi {
            max-width: 300px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .foto-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #365486;
            text-decoration: none;
            font-weight: bold;
            white-space: nowrap;
        }

        .foto-link:hover {
            text-decoration: underline;
        }

        .tidak-ada {
            color: #94a3b8;
        }

        /* STATUS */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
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

        /* EMPTY */
        .empty {
            padding: 45px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty > i {
            margin-bottom: 15px;
            color: #365486;
            font-size: 40px;
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

        body.dark .card {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark .card-header h2 {
            color: #e5e7eb;
        }

        body.dark .card-header i {
            color: #7fc7d9;
        }

        body.dark td {
            color: #e5e7eb;
            border-color: #374151;
        }

        body.dark tbody tr:hover td {
            background: #273449;
        }

        body.dark .theme-button {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark .foto-link {
            color: #93c5fd;
        }

        body.dark .tidak-ada {
            color: #9ca3af;
        }

        body.dark .empty {
            color: #9ca3af;
        }

        body.dark .empty h3 {
            color: #e5e7eb;
        }

        /* RESPONSIVE */
        @media (max-width: 760px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                max-height: none;
                padding: 16px;
                overflow: visible;
            }

            .logo {
                padding: 0 5px 15px;
            }

            .menu-title {
                margin-top: 18px;
            }

            .menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 5px;
            }

            .menu li {
                min-width: 0;
                margin: 0;
            }

            .menu a {
                min-width: 0;
                padding: 11px 9px;
                gap: 9px;
                font-size: 12px;
            }

            .menu a span {
                overflow-wrap: anywhere;
            }

            .main {
                width: 100%;
                margin-left: 0;
                padding: 20px 14px;
            }

            .topbar h1 {
                font-size: 21px;
            }

            .card {
                padding: 18px 13px;
            }

            .card-header h2 {
                font-size: 17px;
            }

            table {
                min-width: 850px;
            }
        }

        @media (max-width: 360px) {
            .menu a {
                gap: 7px;
                padding: 10px 7px;
                font-size: 11px;
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

        <ul class="menu">
            <li>
                <a href="v_dashboard_siswa.php">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="v_buat_pengaduan.php">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span>Buat Pengaduan</span>
                </a>
            </li>

            <li>
                <a href="v_pengaduan_saya.php" class="active">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Pengaduan Saya</span>
                </a>
            </li>

            <li>
                <a href="v_tanggapan_siswa.php">
                    <i class="fa-solid fa-comments"></i>
                    <span>Tanggapan</span>
                </a>
            </li>

            <li>
                <a href="v_profil_siswa.php">
                    <i class="fa-solid fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>

            <li>
                <a href="../asset/aksi_logout.php"
                   onclick="return confirm('Yakin ingin logout?')">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- CONTENT -->
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Pengaduan Saya</h1>
                <p>Lihat daftar pengaduan yang sudah kamu kirim.</p>
            </div>

            <button type="button"
                    class="theme-button"
                    id="tombolTema"
                    aria-label="Ganti tema">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <section class="card">
            <div class="card-header">
                <i class="fa-solid fa-clipboard-list"></i>
                <h2>Daftar Pengaduan</h2>
            </div>

            <?php if (count($pengaduan) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Judul</th>
                                <th>Isi Pengaduan</th>
                                <th>Foto</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pengaduan as $index => $row): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>

                                    <td>
                                        <?= e($row['nama_kategori'] ?? 'Tanpa kategori'); ?>
                                    </td>

                                    <td>
                                        <strong><?= e($row['judul']); ?></strong>
                                    </td>

                                    <td class="isi"><?= e($row['isi']); ?></td>

                                    <td>
                                        <?php if (!empty($row['foto'])): ?>
                                            <a
                                                href="../upload/pengaduan/<?= rawurlencode(basename($row['foto'])); ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="foto-link">
                                                <i class="fa-solid fa-image"></i>
                                                Lihat Foto
                                            </a>
                                        <?php else: ?>
                                            <span class="tidak-ada">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php
                                        $waktu = strtotime($row['tanggal']);
                                        echo $waktu ? date('d-m-Y H:i', $waktu) : '-';
                                        ?>
                                    </td>

                                    <td>
                                        <span class="status <?= e(statusClass($row['status'])); ?>">
                                            <?php if (strtolower((string) $row['status']) === 'menunggu'): ?>
                                                <i class="fa-solid fa-clock"></i>
                                            <?php elseif (strtolower((string) $row['status']) === 'diproses'): ?>
                                                <i class="fa-solid fa-spinner"></i>
                                            <?php elseif (strtolower((string) $row['status']) === 'selesai'): ?>
                                                <i class="fa-solid fa-circle-check"></i>
                                            <?php endif; ?>

                                            <?= e($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">
                    <i class="fa-regular fa-file-lines"></i>
                    <h3>Belum Ada Pengaduan</h3>
                    <p>Kamu belum membuat pengaduan.</p>
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

        try {
            if (localStorage.getItem('siswaTheme') === 'dark') {
                body.classList.add('dark');
            }
        } catch (error) {
            // Gunakan tema terang jika localStorage tidak tersedia.
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
                // Tema tetap dapat berubah selama halaman terbuka.
            }

            perbaruiIkonTema();
        });
    </script>

</body>
</html>