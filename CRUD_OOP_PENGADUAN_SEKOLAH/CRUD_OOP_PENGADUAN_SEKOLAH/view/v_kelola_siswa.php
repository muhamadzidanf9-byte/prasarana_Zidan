<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . '/../model/m_koneksi.php';

$db = new Koneksi();
$conn = $db->getConnection();

$query = $conn->prepare(
    "SELECT id, nama, username, kelas
     FROM users
     WHERE role = 'siswa'
     ORDER BY id DESC"
);
$query->execute();
$siswa = $query->fetchAll(PDO::FETCH_ASSOC);

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa - Pengaduan Sekolah</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
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

        /* SIDEBAR - SAMA DENGAN DASHBOARD ADMIN */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            background: #0f1035;
            padding: 25px 15px;
            z-index: 1000;
        }

        .logo {
            color: white;
            font-size: 21px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            width: 65px;
            height: 65px;
            background: #365486;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .logo-icon i {
            font-size: 30px;
            color: white;
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
            text-decoration: none;
            color: white;
            padding: 14px 18px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: background .2s, color .2s;
            font-size: 15px;
        }

        .menu i {
            width: 20px;
            flex-shrink: 0;
            text-align: center;
            font-size: 17px;
        }

        .menu:hover,
        .menu.active {
            background: #365486;
            color: white;
        }

        /* LOGOUT MERAH */
        .menu.logout {
            margin-top: 25px;
            background: #b83232;
            color: white;
        }

        .menu.logout:hover {
            background: #d64545;
            color: white;
        }

        /* KONTEN UTAMA */
        .main {
            min-width: 0;
            margin-left: 250px;
            padding: 35px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .topbar p {
            color: #64748b;
            font-size: 14px;
        }

        /* TOMBOL TEMA */
        .theme-button {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border: none;
            background: #365486;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: .2s;
        }

        .theme-button:hover {
            background: #7fc7d9;
            color: #0f1035;
        }

        /* KONTEN TABEL */
        .content {
            min-width: 0;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .06);
            transition: background .3s;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .content-header h2 {
            margin: 0;
            font-size: 21px;
        }

        .btn-tambah {
            background: #365486;
            color: white;
            text-decoration: none;
            padding: 11px 15px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            transition: background .2s;
        }

        .btn-tambah:hover {
            background: #263e69;
        }

        /* PENCARIAN */
        .search-box {
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            background: white;
            color: #1e293b;
            font-size: 14px;
        }

        .search-box input:focus {
            border-color: #365486;
            box-shadow: 0 0 0 3px rgba(54, 84, 134, .12);
        }

        .search-info {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* TABEL */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-weight: bold;
        }

        td {
            overflow-wrap: anywhere;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .aksi {
            white-space: nowrap;
        }

        .aksi a {
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
            margin-bottom: 4px;
        }

        .edit {
            color: #2563eb;
        }

        .hapus {
            color: #dc2626;
        }

        .edit:hover,
        .hapus:hover {
            text-decoration: underline;
        }

        .kosong {
            text-align: center;
            color: #64748b;
            padding: 25px;
        }

        /* DARK MODE */
        body.dark {
            background: #111827;
            color: #f1f5f9;
        }

        body.dark .topbar p {
            color: #cbd5e1;
        }

        body.dark .content {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark th {
            background: #374151;
            color: #f1f5f9;
            border-color: #4b5563;
        }

        body.dark td {
            border-color: #374151;
        }

        body.dark tbody tr:hover {
            background: #263449;
        }

        body.dark .search-box input {
            background: #111827;
            color: white;
            border-color: #475569;
        }

        body.dark .search-info,
        body.dark .kosong {
            color: #cbd5e1;
        }

        body.dark .theme-button {
            background: #365486;
            color: white;
        }

        body.dark .btn-tambah {
            background: #365486;
        }

        body.dark .btn-tambah:hover {
            background: #263e69;
        }

        /* RESPONSIVE */
        @media (max-width: 1000px) {
            .main {
                padding: 25px 20px;
            }

            .content {
                padding: 20px;
            }
        }

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
                text-align: left;
                margin-bottom: 20px;
            }

            .logo-icon {
                width: 45px;
                height: 45px;
                flex-shrink: 0;
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
                padding: 12px 10px;
                gap: 8px;
                font-size: 12px;
            }

            .menu i {
                font-size: 15px;
            }

            .menu.logout {
                margin-top: 5px;
            }

            .main {
                width: 100%;
                margin-left: 0;
                padding: 20px 14px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .content {
                padding: 16px;
            }

            .content-header h2 {
                font-size: 18px;
            }

            .btn-tambah {
                padding: 10px 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 360px) {
            .menu {
                gap: 6px;
                padding: 11px 7px;
                font-size: 11px;
            }

            .main {
                padding: 16px 10px;
            }

            .content {
                padding: 12px;
            }

            .topbar h1 {
                font-size: 19px;
            }

            .content-header {
                align-items: flex-start;
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

    <a href="v_dashboard_admin.php" class="menu">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <a href="v_kelola_siswa.php" class="menu active">
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
<main class="main">

    <div class="topbar">
        <div>
            <h1>Kelola Data Siswa</h1>
            <p>Kelola informasi akun siswa yang terdaftar.</p>
        </div>

        <button
            type="button"
            class="theme-button"
            id="themeButton"
            aria-label="Ganti tema"
            title="Ganti Tema"
        >
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>

    <section class="content">

        <div class="content-header">
            <h2>Daftar Data Siswa</h2>

            <a href="v_tambah_siswa.php" class="btn-tambah">
                <i class="fa-solid fa-plus"></i>
                Tambah Siswa
            </a>
        </div>

        <div class="search-box">
            <input
                type="search"
                id="searchSiswa"
                placeholder="Cari nama siswa, username, atau kelas..."
                autocomplete="off"
            >
        </div>

        <div class="search-info" id="searchInfo">
            Menampilkan <?= count($siswa); ?> siswa
        </div>

        <div class="table-wrapper" id="tableWrapper">
            <table id="tabelSiswa">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Username</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="dataSiswa">

                <?php if (count($siswa) > 0): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($siswa as $row): ?>
                        <tr class="baris-siswa">

                            <td class="nomor"><?= $no++; ?></td>

                            <td class="nama-siswa">
                                <?= e($row['nama'] ?? ''); ?>
                            </td>

                            <td class="username-siswa">
                                <?= e($row['username'] ?? ''); ?>
                            </td>

                            <td class="kelas-siswa">
                                <?= e($row['kelas'] ?? ''); ?>
                            </td>

                            <td class="aksi">
                                <a
                                    class="edit"
                                    href="v_edit_siswa.php?id=<?= (int) $row['id']; ?>"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </a>

                                <a
                                    class="hapus"
                                    href="../asset/aksi_hapus.php?id=<?= (int) $row['id']; ?>"
                                    onclick="return confirm('Yakin ingin menghapus data siswa ini?')"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    Hapus
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="kosong">
                            Belum ada data siswa.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <div id="hasilKosong" class="kosong" style="display: none;">
            Data siswa tidak ditemukan.
        </div>

    </section>
</main>

<script>
    /* PENCARIAN SISWA */
    const searchInput = document.getElementById('searchSiswa');
    const rows = document.querySelectorAll('.baris-siswa');
    const searchInfo = document.getElementById('searchInfo');
    const tableWrapper = document.getElementById('tableWrapper');
    const hasilKosong = document.getElementById('hasilKosong');

    function cariSiswa() {
        const keyword = searchInput.value.toLowerCase().trim();
        let jumlah = 0;

        rows.forEach(function(row) {
            const nama = row.querySelector('.nama-siswa')
                .textContent.toLowerCase();

            const username = row.querySelector('.username-siswa')
                .textContent.toLowerCase();

            const kelas = row.querySelector('.kelas-siswa')
                .textContent.toLowerCase();

            const cocok =
                nama.includes(keyword) ||
                username.includes(keyword) ||
                kelas.includes(keyword);

            row.style.display = cocok ? '' : 'none';

            if (cocok) {
                jumlah++;
                row.querySelector('.nomor').textContent = jumlah;
            }
        });

        searchInfo.textContent =
            'Menampilkan ' + jumlah + ' dari ' + rows.length + ' siswa';

        tableWrapper.style.display = jumlah > 0 ? '' : 'none';
        hasilKosong.style.display = jumlah === 0 ? '' : 'none';
    }

    searchInput.addEventListener('input', cariSiswa);

    /* MODE GELAP DAN TERANG */
    const themeButton = document.getElementById('themeButton');

    function aturTema(tema) {
        document.body.classList.toggle('dark', tema === 'dark');

        themeButton.innerHTML = tema === 'dark'
            ? '<i class="fa-solid fa-sun"></i>'
            : '<i class="fa-solid fa-moon"></i>';
    }

    let temaTersimpan = 'light';

    try {
        temaTersimpan = localStorage.getItem('adminTheme') || 'light';
    } catch (error) {
        temaTersimpan = 'light';
    }

    aturTema(temaTersimpan);

    themeButton.addEventListener('click', function() {
        const temaBaru = document.body.classList.contains('dark')
            ? 'light'
            : 'dark';

        aturTema(temaBaru);

        try {
            localStorage.setItem('adminTheme', temaBaru);
        } catch (error) {
            // Tema tetap berubah jika penyimpanan tidak tersedia.
        }
    });
</script>

</body>
</html>