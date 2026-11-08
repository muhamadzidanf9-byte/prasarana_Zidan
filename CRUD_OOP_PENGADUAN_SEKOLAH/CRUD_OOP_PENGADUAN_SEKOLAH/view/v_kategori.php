<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . '/../model/m_koneksi.php';

$db = new Koneksi();
$conn = $db->getConnection();

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kategori'] ?? '');

    if ($nama !== '') {
        $cek = $conn->prepare(
            "SELECT id FROM kategori WHERE nama_kategori = ?"
        );
        $cek->execute([$nama]);

        if ($cek->fetch()) {
            header("Location: v_kategori.php?pesan=duplikat");
            exit;
        }

        $stmt = $conn->prepare(
            "INSERT INTO kategori (nama_kategori) VALUES (?)"
        );
        $stmt->execute([$nama]);

        header("Location: v_kategori.php?pesan=tambah");
        exit;
    }

    header("Location: v_kategori.php?pesan=kosong");
    exit;
}

// Hapus kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $id = (int) ($_POST['id'] ?? 0);

    try {
        $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: v_kategori.php?pesan=hapus");
        exit;
    } catch (PDOException $e) {
        header("Location: v_kategori.php?pesan=gagal_hapus");
        exit;
    }
}

// Ambil data kategori
$stmt = $conn->query(
    "SELECT id, nama_kategori FROM kategori ORDER BY id DESC"
);
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>

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

        /* KONTEN */
        .main {
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
        .theme-button {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            background: #365486;
            color: white;
            font-size: 17px;
            transition: .3s;
        }

        .theme-button:hover {
            background: #7fc7d9;
            color: #0f1035;
        }

        /* KARTU KONTEN */
        .content {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            transition: background .3s, box-shadow .3s;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .content-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0f1035;
            font-size: 21px;
        }

        .content-header h2 i {
            color: #365486;
        }

        /* FORM TAMBAH */
        .form-tambah {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .form-tambah input {
            flex: 1;
            min-width: 0;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            background: white;
            color: #222;
        }

        .form-tambah input:focus,
        .search:focus {
            border-color: #365486;
            box-shadow: 0 0 0 3px rgba(54, 84, 134, .12);
        }

        button {
            border: none;
            border-radius: 8px;
            padding: 11px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: background .2s;
        }

        .btn-tambah {
            background: #365486;
            color: white;
            white-space: nowrap;
        }

        .btn-tambah:hover {
            background: #2d4773;
        }

        .btn-edit {
            background: #2563eb;
            color: white;
        }

        .btn-edit:hover {
            background: #1d4ed8;
        }

        .btn-hapus {
            background: #b83232;
            color: white;
        }

        .btn-hapus:hover {
            background: #d64545;
        }

        /* PENCARIAN */
        .search-wrapper {
            position: relative;
            margin-bottom: 16px;
        }

        .search-wrapper i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            background: white;
            color: #222;
        }

        /* TABEL */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 450px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #0f1035;
            color: white;
            white-space: nowrap;
        }

        th:first-child {
            border-radius: 9px 0 0 0;
        }

        th:last-child {
            border-radius: 0 9px 0 0;
        }

        tbody tr:hover td {
            background: #f8faff;
        }

        .aksi {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .aksi form {
            margin: 0;
        }

        .aksi button {
            white-space: nowrap;
        }

        /* NOTIFIKASI */
        .pesan {
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            background: #dbeafe;
            color: #1e40af;
            line-height: 1.5;
        }

        .kosong {
            text-align: center;
            padding: 25px;
            color: #64748b;
        }

        .kosong i {
            display: block;
            font-size: 35px;
            color: #365486;
            margin-bottom: 10px;
        }

        .kosong h3 {
            margin-bottom: 7px;
            color: #334155;
        }

        /* DARK MODE */
        body.dark {
            background: #151515;
            color: #eee;
        }

        body.dark .topbar h1,
        body.dark .content-header h2,
        body.dark .kosong h3 {
            color: white;
        }

        body.dark .content {
            background: #222;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
        }

        body.dark .content-header h2 i,
        body.dark .kosong i {
            color: #7fc7d9;
        }

        body.dark th {
            background: #0f1035;
        }

        body.dark td {
            border-color: #444;
            color: #eee;
        }

        body.dark tbody tr:hover td {
            background: #2a2a2a;
        }

        body.dark .form-tambah input,
        body.dark .search {
            background: #333;
            color: white;
            border-color: #555;
        }

        body.dark .search-wrapper i {
            color: #cbd5e1;
        }

        body.dark .theme-button {
            background: #365486;
            color: white;
        }

        body.dark .pesan {
            background: #26364f;
            color: #dbeafe;
        }

        body.dark .kosong {
            color: #ccc;
        }

        /* RESPONSIVE TABLET */
        @media (max-width: 1000px) {
            .sidebar {
                width: 230px;
            }

            .main {
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
                min-height: 48px;
                height: 100%;
                padding: 11px 10px;
                gap: 8px;
                font-size: 13px;
                overflow-wrap: anywhere;
            }

            .menu a i {
                width: 18px;
                font-size: 15px;
            }

            .main {
                margin-left: 0;
                padding: 18px 12px;
            }

            .topbar {
                margin-bottom: 18px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .content {
                padding: 17px;
                border-radius: 12px;
            }

            .content-header h2 {
                font-size: 18px;
            }

            .form-tambah {
                flex-direction: column;
            }

            .form-tambah button {
                width: 100%;
            }
        }

        @media (max-width: 380px) {
            .menu {
                grid-template-columns: 1fr;
            }

            .main {
                padding: 15px 10px;
            }

            .content {
                padding: 13px;
            }

            .topbar h1 {
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
            <a href="v_tanggapan.php">
                <i class="fa-solid fa-comments"></i>
                <span>Tanggapan</span>
            </a>
        </li>

        <li>
            <a href="v_kategori.php" class="active">
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

<!-- KONTEN UTAMA -->
<main class="main">
    <div class="topbar">
        <h1>Kelola Kategori</h1>

        <button
            type="button"
            class="theme-button"
            id="themeButton"
            title="Ganti Tema"
            aria-label="Ganti tema"
        >
            <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
    </div>

    <section class="content">
        <div class="content-header">
            <h2>
                <i class="fa-solid fa-layer-group"></i>
                Data Kategori Pengaduan
            </h2>
        </div>

        <?php
        $pesan = $_GET['pesan'] ?? '';

        $daftarPesan = [
            'tambah' => 'Kategori berhasil ditambahkan.',
            'hapus' => 'Kategori berhasil dihapus.',
            'duplikat' => 'Nama kategori sudah tersedia.',
            'kosong' => 'Nama kategori tidak boleh kosong.',
            'gagal_hapus' => 'Kategori tidak dapat dihapus karena masih digunakan oleh pengaduan.'
        ];
        ?>

        <?php if (isset($daftarPesan[$pesan])): ?>
            <div class="pesan">
                <i class="fa-solid fa-circle-info"></i>
                <?= htmlspecialchars(
                    $daftarPesan[$pesan],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH KATEGORI -->
        <form method="POST" class="form-tambah">
            <input
                type="text"
                name="nama_kategori"
                placeholder="Masukkan nama kategori..."
                maxlength="100"
                required
            >

            <button type="submit" name="tambah" class="btn-tambah">
                <i class="fa-solid fa-plus"></i>
                Tambah Kategori
            </button>
        </form>

        <!-- PENCARIAN -->
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="search"
                id="searchKategori"
                class="search"
                placeholder="Cari kategori..."
                autocomplete="off"
            >
        </div>

        <!-- TABEL KATEGORI -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="dataKategori">
                    <?php if (count($kategori) > 0): ?>
                        <?php $no = 1; ?>

                        <?php foreach ($kategori as $row): ?>
                            <tr class="baris-kategori">
                                <td class="nomor"><?= $no++; ?></td>

                                <td class="nama-kategori">
                                    <?= htmlspecialchars(
                                        $row['nama_kategori'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <div class="aksi">
                                        <button
                                            type="button"
                                            class="btn-edit tombol-edit"
                                            data-id="<?= (int) $row['id']; ?>"
                                            data-nama="<?= htmlspecialchars(
                                                $row['nama_kategori'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $row['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="hapus"
                                                class="btn-hapus"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr id="kategoriAwalKosong">
                            <td colspan="3" class="kosong">
                                <i class="fa-regular fa-folder-open"></i>
                                <h3>Belum Ada Kategori</h3>
                                <p>Tambahkan kategori pengaduan terlebih dahulu.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div id="hasilKosong" class="kosong" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Kategori Tidak Ditemukan</h3>
                <p>Coba gunakan kata kunci pencarian lain.</p>
            </div>
        </div>
    </section>
</main>

<script>
    // PENCARIAN KATEGORI
    const searchKategori = document.getElementById('searchKategori');
    const barisKategori = document.querySelectorAll('.baris-kategori');
    const hasilKosong = document.getElementById('hasilKosong');

    if (searchKategori) {
        searchKategori.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            let jumlah = 0;

            barisKategori.forEach(function (baris) {
                const nama = baris.querySelector('.nama-kategori')
                    .textContent.toLowerCase();

                const cocok = nama.includes(keyword);
                baris.style.display = cocok ? '' : 'none';

                if (cocok) {
                    jumlah++;
                    baris.querySelector('.nomor').textContent = jumlah;
                }
            });

            hasilKosong.style.display =
                barisKategori.length > 0 && jumlah === 0 ? 'block' : 'none';
        });
    }

    // EDIT KATEGORI
    document.querySelectorAll('.tombol-edit').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const id = this.dataset.id;
            const namaLama = this.dataset.nama;

            const namaBaru = prompt(
                'Masukkan nama kategori yang baru:',
                namaLama
            );

            if (namaBaru === null) {
                return;
            }

            if (namaBaru.trim() === '') {
                alert('Nama kategori tidak boleh kosong.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'aksi_edit_kategori.php';

            const fieldId = document.createElement('input');
            fieldId.type = 'hidden';
            fieldId.name = 'id';
            fieldId.value = id;

            const fieldNama = document.createElement('input');
            fieldNama.type = 'hidden';
            fieldNama.name = 'nama_kategori';
            fieldNama.value = namaBaru.trim();

            form.appendChild(fieldId);
            form.appendChild(fieldNama);
            document.body.appendChild(form);
            form.submit();
        });
    });

    // DARK MODE
    const themeButton = document.getElementById('themeButton');
    const themeIcon = document.getElementById('themeIcon');

    function updateThemeIcon() {
        themeIcon.className = document.body.classList.contains('dark')
            ? 'fa-solid fa-sun'
            : 'fa-solid fa-moon';
    }

    function aturTema(tema) {
        document.body.classList.toggle('dark', tema === 'dark');
        updateThemeIcon();
    }

    let temaTersimpan = 'light';

    try {
        temaTersimpan = localStorage.getItem('adminTheme') || 'light';
    } catch (error) {
        temaTersimpan = 'light';
    }

    aturTema(temaTersimpan);

    themeButton.addEventListener('click', function () {
        const temaBaru = document.body.classList.contains('dark')
            ? 'light'
            : 'dark';

        aturTema(temaBaru);

        try {
            localStorage.setItem('adminTheme', temaBaru);
        } catch (error) {
            // Tema tetap berubah meskipun penyimpanan tidak tersedia.
        }
    });
</script>

</body>
</html>