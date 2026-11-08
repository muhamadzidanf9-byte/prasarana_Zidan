<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . "/../model/m_koneksi.php";

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$query = "
    SELECT
        pengaduan.id,
        pengaduan.judul,
        pengaduan.isi,
        pengaduan.foto,
        pengaduan.tanggal,
        pengaduan.status,
        users.nama,
        users.kelas,
        kategori.nama_kategori
    FROM pengaduan
    INNER JOIN users
        ON pengaduan.user_id = users.id
    INNER JOIN kategori
        ON pengaduan.kategori_id = kategori.id
    ORDER BY pengaduan.id DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();

$data_pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggapan Pengaduan - Admin</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #222;
            transition: background .3s, color .3s;
        }

        /* SIDEBAR SAMA DENGAN DASHBOARD ADMIN */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #0f1035;
            color: white;
            padding: 25px 15px;
            z-index: 1000;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
            margin-bottom: 35px;
            padding: 0 5px;
        }

        .logo-icon {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #365486;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .logo-icon i {
            font-size: 30px;
            color: white;
        }

        .logo-text {
            font-size: 20px;
            font-weight: bold;
            color: white;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            margin-bottom: 8px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            transition: background .3s, color .3s;
        }

        .menu a i {
            width: 20px;
            text-align: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .menu a:hover,
        .menu a.active {
            background: #365486;
            color: white;
        }

        /* LOGOUT MERAH */
        .menu .logout a {
            background: #b83232;
            color: white;
        }

        .menu .logout a:hover {
            background: #d64545;
        }

        /* CONTENT */
        .content {
            margin-left: 250px;
            padding: 35px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            color: #0f1035;
            font-size: 28px;
        }

        /* TOMBOL TEMA */
        .theme-btn {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border: none;
            background: #365486;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: .3s;
        }

        .theme-btn:hover {
            background: #7fc7d9;
            color: #0f1035;
        }

        .theme-btn i {
            pointer-events: none;
        }

        /* TABEL */
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            transition: background .3s, box-shadow .3s;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #0f1035;
            color: white;
            padding: 14px;
            text-align: left;
            white-space: nowrap;
        }

        th:first-child {
            border-radius: 10px 0 0 10px;
        }

        th:last-child {
            border-radius: 0 10px 10px 0;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            transition: background .3s, color .3s;
        }

        tbody tr:hover td {
            background: #f5f7fb;
        }

        /* FOTO */
        .foto {
            width: 90px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid #ddd;
            transition: .2s;
        }

        .foto:hover {
            transform: scale(1.05);
            border-color: #365486;
        }

        .tidak-ada {
            color: #888;
            font-size: 13px;
        }

        /* STATUS */
        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .menunggu {
            background: #fff3cd;
            color: #856404;
        }

        .diproses {
            background: #cfe2ff;
            color: #084298;
        }

        .selesai {
            background: #d1e7dd;
            color: #0f5132;
        }

        /* TOMBOL TANGGAPI */
        .btn-tanggapan {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: #365486;
            color: white;
            text-decoration: none;
            padding: 9px 12px;
            border-radius: 7px;
            font-size: 13px;
            white-space: nowrap;
            transition: .2s;
        }

        .btn-tanggapan:hover {
            background: #2c4470;
        }

        /* DATA KOSONG */
        .empty {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }

        .empty i {
            display: block;
            font-size: 42px;
            color: #365486;
            margin-bottom: 15px;
        }

        .empty h3 {
            margin-bottom: 8px;
            color: #333;
        }

        /* DARK MODE */
        body.dark {
            background: #151515;
            color: #eee;
        }

        body.dark .topbar h1 {
            color: white;
        }

        body.dark .table-container {
            background: #222;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
        }

        body.dark td {
            color: #eee;
            border-bottom-color: #444;
        }

        body.dark tbody tr:hover td {
            background: #2a2a2a;
        }

        body.dark .foto {
            border-color: #555;
        }

        body.dark .tidak-ada {
            color: #aaa;
        }

        body.dark .empty {
            color: #ccc;
        }

        body.dark .empty h3 {
            color: white;
        }

        body.dark .empty i {
            color: #7fc7d9;
        }

        /* RESPONSIVE TABLET */
        @media (max-width: 1000px) {
            .sidebar {
                width: 230px;
            }

            .content {
                margin-left: 230px;
                padding: 25px;
            }
        }

        /* RESPONSIVE HP */
        @media (max-width: 700px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 18px 15px;
                overflow: visible;
            }

            .logo {
                flex-direction: row;
                justify-content: flex-start;
                text-align: left;
                gap: 12px;
                margin-bottom: 20px;
                padding: 0 5px;
            }

            .logo-icon {
                width: 45px;
                height: 45px;
                border-radius: 12px;
            }

            .logo-icon i {
                font-size: 22px;
            }

            .logo-text {
                font-size: 18px;
            }

            .menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .menu li {
                margin-bottom: 0;
                min-width: 0;
            }

            .menu a {
                height: 100%;
                min-height: 48px;
                padding: 11px 10px;
                gap: 8px;
                font-size: 13px;
                overflow-wrap: anywhere;
            }

            .menu a i {
                width: 18px;
                font-size: 15px;
            }

            .content {
                margin-left: 0;
                padding: 18px 12px;
            }

            .topbar {
                margin-bottom: 18px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .table-container {
                padding: 15px;
                border-radius: 12px;
            }
        }

        @media (max-width: 380px) {
            .menu {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 15px 10px;
            }

            .table-container {
                padding: 12px;
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
        <span class="logo-text">Pengaduan Sekolah</span>
    </div>

    <ul class="menu">
        <li>
            <a href="v_dashboard_admin.php">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="v_kelola_siswa.php">
                <i class="fa-solid fa-users"></i>
                <span>Kelola Data Siswa</span>
            </a>
        </li>

        <li>
            <a href="v_data_pengaduan.php">
                <i class="fa-solid fa-file-lines"></i>
                <span>Data Pengaduan</span>
            </a>
        </li>

        <li>
            <a href="v_tanggapan.php" class="active">
                <i class="fa-solid fa-comments"></i>
                <span>Tanggapan</span>
            </a>
        </li>

        <li>
            <a href="v_kategori.php">
                <i class="fa-solid fa-layer-group"></i>
                <span>Kategori</span>
            </a>
        </li>

        <li class="logout">
            <a href="../asset/aksi_logout.php"
               onclick="return confirm('Yakin ingin logout?');">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<!-- CONTENT -->
<main class="content">
    <div class="topbar">
        <h1>Tanggapan Pengaduan</h1>

        <button
            type="button"
            class="theme-btn"
            id="themeButton"
            title="Ganti Tema"
            aria-label="Ganti tema"
        >
            <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Judul Pengaduan</th>
                    <th>Kategori</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (count($data_pengaduan) > 0): ?>
                <?php $no = 1; ?>

                <?php foreach ($data_pengaduan as $data): ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <?= htmlspecialchars(
                                $data['nama'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['kelas'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['judul'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $data['nama_kategori'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>

                        <!-- FOTO -->
                        <td>
                            <?php if (!empty($data['foto'])): ?>
                                <?php
                                $namaFoto = basename($data['foto']);
                                $urlFoto = '../upload/pengaduan/' .
                                    rawurlencode($namaFoto);
                                ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        $urlFoto,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    <img
                                        src="<?= htmlspecialchars(
                                            $urlFoto,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>"
                                        class="foto"
                                        alt="Foto Pengaduan"
                                    >
                                </a>
                            <?php else: ?>
                                <span class="tidak-ada">Tidak ada foto</span>
                            <?php endif; ?>
                        </td>

                        <!-- STATUS -->
                        <td>
                            <?php
                            $status = $data['status'] ?? '';
                            $kelasStatus = 'menunggu';

                            if ($status === 'Diproses') {
                                $kelasStatus = 'diproses';
                            } elseif ($status === 'Selesai') {
                                $kelasStatus = 'selesai';
                            }
                            ?>

                            <span class="status <?= $kelasStatus; ?>">
                                <?= htmlspecialchars(
                                    $status,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </span>
                        </td>

                        <!-- AKSI -->
                        <td>
                            <a
                                href="v_form_tanggapan.php?id=<?= (int) $data['id']; ?>"
                                class="btn-tanggapan"
                            >
                                <i class="fa-solid fa-comment-dots"></i>
                                Tanggapi
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="empty">
                            <i class="fa-regular fa-file-lines"></i>
                            <h3>Belum Ada Pengaduan</h3>
                            <p>Belum ada data pengaduan dari siswa.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    // DARK MODE DENGAN PENYIMPANAN TEMA ADMIN
    const themeButton = document.getElementById("themeButton");
    const themeIcon = document.getElementById("themeIcon");

    function updateThemeIcon() {
        if (document.body.classList.contains("dark")) {
            themeIcon.className = "fa-solid fa-sun";
        } else {
            themeIcon.className = "fa-solid fa-moon";
        }
    }

    function aturTema(tema) {
        document.body.classList.toggle("dark", tema === "dark");
        updateThemeIcon();
    }

    let savedTheme = "light";

    try {
        savedTheme = localStorage.getItem("adminTheme") || "light";
    } catch (error) {
        savedTheme = "light";
    }

    aturTema(savedTheme);

    themeButton.addEventListener("click", function () {
        const temaBaru = document.body.classList.contains("dark")
            ? "light"
            : "dark";

        aturTema(temaBaru);

        try {
            localStorage.setItem("adminTheme", temaBaru);
        } catch (error) {
            // Tema tetap berubah jika penyimpanan tidak tersedia.
        }
    });
</script>

</body>
</html>