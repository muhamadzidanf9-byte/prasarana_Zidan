<?php
session_start();
require_once "../model/m_koneksi.php";

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'siswa'
) {
    header("Location: v_login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? '';
$kelas = $_SESSION['kelas'] ?? '';

try {
    $koneksi = new Koneksi();
    $db = $koneksi->getConnection();

    $stmt = $db->prepare("
        SELECT id, nama_kategori
        FROM kategori
        ORDER BY nama_kategori ASC
    ");

    $stmt->execute();
    $kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Gagal mengambil kategori: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengaduan</title>

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
            transition: background .2s;
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

        .menu a.logout {
            background: #b83232;
        }

        .menu a.logout:hover {
            background: #d64545;
        }

        /* CONTENT */
        .content {
            min-width: 0;
            margin-left: 250px;
            padding: 35px;
            min-height: 100vh;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .top h1 {
            color: #0f1035;
            font-size: 28px;
        }

        .theme-btn {
            border: 1px solid #cbd5e1;
            background: white;
            color: #365486;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 17px;
        }

        /* CARD */
        .card {
            width: 100%;
            max-width: 850px;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .student-info {
            background: #eef4ff;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .student-info p {
            margin: 5px 0;
            color: #444;
            overflow-wrap: anywhere;
        }

        /* FORM */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: .3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #365486;
            box-shadow: 0 0 0 3px rgba(54,84,134,0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        input[type="file"] {
            padding: 11px;
            cursor: pointer;
        }

        .hint {
            font-size: 12px;
            color: #777;
            margin-top: 6px;
            line-height: 1.5;
        }

        /* PREVIEW FOTO */
        .preview-box {
            display: none;
            margin-top: 15px;
        }

        .preview-box p {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .preview-box img {
            width: 250px;
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        /* BUTTON */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #365486;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: .3s;
        }

        .btn-submit i {
            margin-right: 8px;
        }

        .btn-submit:hover {
            background: #0f1035;
        }

        /* DARK MODE */
        body.dark {
            background: #111827;
            color: #e5e7eb;
        }

        body.dark .top h1 {
            color: #e5e7eb;
        }

        body.dark .card {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark label {
            color: #e5e7eb;
        }

        body.dark input,
        body.dark select,
        body.dark textarea {
            background: #374151;
            color: white;
            border-color: #4b5563;
        }

        body.dark input::placeholder,
        body.dark textarea::placeholder {
            color: #aaa;
        }

        body.dark .student-info {
            background: #374151;
        }

        body.dark .student-info p {
            color: #ddd;
        }

        body.dark .hint {
            color: #bbb;
        }

        body.dark .theme-btn {
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

            .menu a {
                min-width: 0;
                margin: 0;
                padding: 11px 9px;
                font-size: 12px;
            }

            .content {
                width: 100%;
                margin-left: 0;
                padding: 20px 14px;
            }

            .top h1 {
                font-size: 21px;
            }

            .card {
                padding: 20px;
            }
        }

        @media (max-width: 360px) {
            .menu a {
                gap: 7px;
                padding: 10px 7px;
                font-size: 11px;
            }

            .card {
                padding: 17px 12px;
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

            <a href="v_buat_pengaduan.php" class="active">
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

            <a href="../asset/aksi_logout.php"
               class="logout"
               onclick="return confirm('Yakin ingin logout?')">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>

        </nav>
    </aside>

    <!-- CONTENT -->
    <main class="content">

        <div class="top">
            <h1>Buat Pengaduan</h1>

            <button
                type="button"
                class="theme-btn"
                onclick="toggleTheme()"
                id="themeButton"
                title="Ganti Tema"
                aria-label="Ganti Tema">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <!-- FORM CARD -->
        <section class="card">

            <div class="student-info">
                <p>
                    <strong>Nama:</strong>
                    <?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <p>
                    <strong>Kelas:</strong>
                    <?= htmlspecialchars($kelas, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>

            <form
                action="../asset/aksi_buat_pengaduan.php"
                method="POST"
                enctype="multipart/form-data">

                <div class="form-group">
                    <label for="judul">Judul Pengaduan</label>
                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        placeholder="Contoh: Kursi di kelas rusak"
                        required>
                </div>

                <div class="form-group">
                    <label for="kategori_id">Kategori</label>

                    <select name="kategori_id" id="kategori_id" required>
                        <option value="">-- Pilih Kategori --</option>

                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= (int) $k['id']; ?>">
                                <?= htmlspecialchars(
                                    $k['nama_kategori'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label for="isi">Isi Pengaduan</label>
                    <textarea
                        name="isi"
                        id="isi"
                        placeholder="Jelaskan masalah sarana atau prasarana yang ingin dilaporkan..."
                        required></textarea>
                </div>

                <div class="form-group">
                    <label for="foto">Foto Bukti</label>

                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept=".jpg,.jpeg,.png">

                    <div class="hint">
                        Pilih foto sebagai bukti pengaduan.
                        Format JPG, JPEG, atau PNG. Maksimal 5 MB.
                    </div>

                    <div class="preview-box" id="previewBox">
                        <p>Preview Foto:</p>
                        <img id="previewFoto" src="" alt="Preview Foto">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim Pengaduan
                </button>

            </form>
        </section>
    </main>

    <!-- JAVASCRIPT -->
    <script>
        function toggleTheme() {
            document.body.classList.toggle("dark");

            const button = document.getElementById("themeButton");
            const icon = button.querySelector("i");
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

        window.addEventListener("DOMContentLoaded", function () {
            const button = document.getElementById("themeButton");
            const icon = button.querySelector("i");
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

        /* PREVIEW FOTO */
        const foto = document.getElementById("foto");
        const previewBox = document.getElementById("previewBox");
        const previewFoto = document.getElementById("previewFoto");

        foto.addEventListener("change", function () {
            const file = this.files[0];

            if (!file) {
                previewBox.style.display = "none";
                previewFoto.src = "";
                return;
            }

            const tipeDiizinkan = ["image/jpeg", "image/png"];

            if (!tipeDiizinkan.includes(file.type)) {
                alert("Format foto harus JPG, JPEG, atau PNG!");
                this.value = "";
                previewBox.style.display = "none";
                previewFoto.src = "";
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert("Ukuran foto maksimal 5 MB!");
                this.value = "";
                previewBox.style.display = "none";
                previewFoto.src = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                previewFoto.src = e.target.result;
                previewBox.style.display = "block";
            };

            reader.readAsDataURL(file);
        });
    </script>

</body>
</html>