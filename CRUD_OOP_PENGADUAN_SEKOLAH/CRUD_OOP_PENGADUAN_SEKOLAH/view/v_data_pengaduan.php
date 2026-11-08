<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . "/../model/m_koneksi.php";

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$sql = "
    SELECT
        p.id,
        p.judul,
        p.isi,
        p.foto,
        p.tanggal,
        p.status,
        u.nama,
        u.kelas,
        k.nama_kategori
    FROM pengaduan p
    INNER JOIN users u ON p.user_id = u.id
    INNER JOIN kategori k ON p.kategori_id = k.id
    ORDER BY p.tanggal DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengaduan - Admin</title>

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

        .theme-button {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border: none;
            border-radius: 50%;
            background: #365486;
            color: white;
            cursor: pointer;
            font-size: 17px;
            transition: background .3s;
        }

        .theme-button:hover {
            background: #2d4773;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-header i {
            color: #365486;
            font-size: 23px;
        }

        .card-header h2 {
            font-size: 22px;
            color: #0f1035;
        }

        /* PENCARIAN */
        .search-container {
            margin-bottom: 12px;
        }

        .search-box {
            position: relative;
            width: 100%;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-box input {
            width: 100%;
            padding: 13px 15px 13px 43px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            outline: none;
            font-size: 14px;
            background: white;
            color: #222;
        }

        .search-box input:focus {
            border-color: #365486;
            box-shadow: 0 0 0 3px rgba(54, 84, 134, .12);
        }

        .search-info {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 15px;
        }

        /* TABEL */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
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
            padding: 14px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        tr:hover td {
            background: #f8faff;
        }

        .isi {
            max-width: 300px;
            min-width: 180px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        /* FOTO */
        .foto {
            width: 70px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .foto-link {
            display: inline-block;
        }

        .tidak-ada {
            color: #999;
        }

        /* STATUS */
        .status-select {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: white;
            color: #333;
            font-size: 13px;
            cursor: pointer;
            outline: none;
            min-width: 115px;
        }

        .status-select:focus {
            border-color: #365486;
        }

        /* HAPUS */
        .btn-hapus {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 12px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            transition: .3s;
            white-space: nowrap;
        }

        .btn-hapus:hover {
            background: #b02a37;
        }

        /* DATA KOSONG */
        .empty {
            text-align: center;
            padding: 45px 20px;
            color: #777;
        }

        .empty i {
            font-size: 45px;
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

        body.dark .card {
            background: #222;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
        }

        body.dark .card-header h2 {
            color: white;
        }

        body.dark .card-header i,
        body.dark .empty i {
            color: #7fc7d9;
        }

        body.dark td {
            border-color: #444;
            color: #eee;
        }

        body.dark tr:hover td {
            background: #2a2a2a;
        }

        body.dark .tidak-ada {
            color: #aaa;
        }

        body.dark .empty h3 {
            color: white;
        }

        body.dark .search-box input {
            background: #333;
            color: white;
            border-color: #555;
        }

        body.dark .search-info {
            color: #ccc;
        }

        body.dark .status-select {
            background: #333;
            color: white;
            border-color: #555;
        }

        body.dark .status-select:focus {
            border-color: #7fc7d9;
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

            .card {
                padding: 16px;
                border-radius: 12px;
            }

            .card-header h2 {
                font-size: 18px;
            }

            .card-header i {
                font-size: 20px;
            }

            .theme-button {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 380px) {
            .menu {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 15px 10px;
            }

            .card {
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
            <a href="v_data_pengaduan.php" class="active">
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
        <h1>Data Pengaduan</h1>

        <button type="button"
                class="theme-button"
                id="themeButton"
                title="Ganti Tema"
                aria-label="Ganti tema">
            <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-file-lines"></i>
            <h2>Data Pengaduan Siswa</h2>
        </div>

        <?php if (count($pengaduan) > 0): ?>

            <div class="search-container">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="cariPengaduan"
                        placeholder="Cari nama siswa, kelas, kategori, judul, status..."
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="search-info" id="infoPencarian">
                Menampilkan <?= count($pengaduan); ?> data pengaduan
            </div>

            <div class="table-container" id="containerTabel">
                <table id="tabelPengaduan">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Isi Pengaduan</th>
                            <th>Foto</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no = 1; ?>

                    <?php foreach ($pengaduan as $row): ?>
                        <tr class="baris-pengaduan">
                            <td class="nomor"><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['kelas'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_kategori'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $row['judul'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </strong>
                            </td>

                            <td class="isi">
                                <?= nl2br(htmlspecialchars(
                                    $row['isi'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                )); ?>
                            </td>

                            <td>
                                <?php if (!empty($row['foto'])): ?>
                                    <?php
                                    $namaFoto = basename($row['foto']);
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
                                        class="foto-link"
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
                                    <span class="tidak-ada">Tidak ada</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    date(
                                        'd-m-Y H:i',
                                        strtotime($row['tanggal'])
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <form
                                    action="../asset/aksi_update_status.php"
                                    method="POST"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $row['id']; ?>"
                                    >

                                    <select
                                        name="status"
                                        class="status-select"
                                        aria-label="Ubah status pengaduan"
                                        onchange="this.form.submit()"
                                    >
                                        <option value="Menunggu"
                                            <?= $row['status'] === 'Menunggu'
                                                ? 'selected' : ''; ?>>
                                            Menunggu
                                        </option>

                                        <option value="Diproses"
                                            <?= $row['status'] === 'Diproses'
                                                ? 'selected' : ''; ?>>
                                            Diproses
                                        </option>

                                        <option value="Selesai"
                                            <?= $row['status'] === 'Selesai'
                                                ? 'selected' : ''; ?>>
                                            Selesai
                                        </option>
                                    </select>
                                </form>
                            </td>

                            <td>
                                <a
                                    href="../asset/aksi_hapus_pengaduan.php?id=<?= (int) $row['id']; ?>"
                                    class="btn-hapus"
                                    onclick="return confirm('Yakin ingin menghapus pengaduan ini? Data tanggapan juga akan dihapus.');"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="dataTidakDitemukan"
                 class="empty"
                 style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Data Tidak Ditemukan</h3>
                <p>Coba gunakan kata kunci pencarian yang berbeda.</p>
            </div>

        <?php else: ?>

            <div class="empty">
                <i class="fa-regular fa-file-lines"></i>
                <h3>Belum Ada Pengaduan</h3>
                <p>Belum ada data pengaduan dari siswa.</p>
            </div>

        <?php endif; ?>
    </div>
</main>

<script>
    // FITUR PENCARIAN
    const inputCari = document.getElementById("cariPengaduan");
    const barisPengaduan = document.querySelectorAll(".baris-pengaduan");
    const infoPencarian = document.getElementById("infoPencarian");
    const containerTabel = document.getElementById("containerTabel");
    const dataTidakDitemukan = document.getElementById("dataTidakDitemukan");

    if (inputCari) {
        inputCari.addEventListener("input", function () {
            const kataKunci = this.value.toLowerCase().trim();
            let jumlahTampil = 0;

            barisPengaduan.forEach(function (baris) {
                const teksBaris = baris.textContent.toLowerCase();
                const cocok = teksBaris.includes(kataKunci);

                baris.style.display = cocok ? "" : "none";

                if (cocok) {
                    jumlahTampil++;
                    baris.querySelector(".nomor").textContent = jumlahTampil;
                }
            });

            infoPencarian.textContent =
                "Menampilkan " + jumlahTampil +
                " dari " + barisPengaduan.length +
                " data pengaduan";

            containerTabel.style.display =
                jumlahTampil > 0 ? "" : "none";

            dataTidakDitemukan.style.display =
                jumlahTampil === 0 ? "" : "none";
        });
    }

    // DARK MODE
    const themeButton = document.getElementById("themeButton");
    const themeIcon = document.getElementById("themeIcon");

    function updateThemeIcon() {
        themeIcon.className = document.body.classList.contains("dark")
            ? "fa-solid fa-sun"
            : "fa-solid fa-moon";
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
            // Tema tetap berubah meskipun penyimpanan tidak tersedia.
        }
    });
</script>

</body>
</html>