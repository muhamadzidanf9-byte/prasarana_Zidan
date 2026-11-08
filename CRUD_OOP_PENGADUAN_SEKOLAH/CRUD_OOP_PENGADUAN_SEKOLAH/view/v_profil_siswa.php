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

$id = $_SESSION['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT id, nama, username, kelas
    FROM users
    WHERE id = :id AND role = 'siswa'
    LIMIT 1
");
$stmt->execute(['id' => $id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    session_destroy();
    header("Location: v_login.php");
    exit;
}

$_SESSION['nama'] = $siswa['nama'];
$_SESSION['username'] = $siswa['username'];
$_SESSION['kelas'] = $siswa['kelas'];

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pesan = $_SESSION['pesan_profil_siswa'] ?? '';
$jenisPesan = $_SESSION['jenis_pesan_profil_siswa'] ?? 'success';

unset(
    $_SESSION['pesan_profil_siswa'],
    $_SESSION['jenis_pesan_profil_siswa']
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa</title>

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

        .menu a[href="../asset/aksi_logout.php"] {
            background: #b83232;
            color: white;
        }

        .menu a[href="../asset/aksi_logout.php"]:hover {
            background: #d64545;
            color: white;
        }

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

        .profile-card {
            width: 100%;
            max-width: 800px;
            padding: 28px;
            border-radius: 14px;
            background: white;
            box-shadow: 0 4px 18px rgba(15,16,53,.06);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 25px;
            padding-bottom: 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: #dce8f8;
            color: #365486;
            font-size: 25px;
        }

        .profile-header h2 {
            margin: 0 0 7px;
            font-size: 20px;
            overflow-wrap: anywhere;
        }

        .profile-header p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .section-title {
            margin: 0 0 20px;
            font-size: 17px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            min-width: 0;
            padding: 12px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            background: white;
            color: #1e293b;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #365486;
            box-shadow: 0 0 0 3px rgba(54,84,134,.12);
        }

        .form-group input[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: default;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 13px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 16px;
        }

        .hint {
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-simpan {
            background: #365486;
            color: white;
        }

        .btn-simpan:hover {
            background: #263e69;
        }

        .btn-batal {
            margin-left: 7px;
            border: 1px solid #cbd5e1;
            background: transparent;
            color: #365486;
        }

        .btn-batal:hover {
            background: #eef2f7;
        }

        .alert {
            margin-bottom: 20px;
            padding: 13px 15px;
            border-radius: 8px;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .hidden {
            display: none !important;
        }

        /* DARK MODE */

        body.dark {
            background: #111827;
            color: #e5e7eb;
        }

        body.dark .topbar p,
        body.dark .profile-header p,
        body.dark .hint {
            color: #9ca3af;
        }

        body.dark .profile-card {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark .profile-header {
            border-color: #374151;
        }

        body.dark .avatar {
            background: #263e69;
            color: #e5e7eb;
        }

        body.dark .form-group input {
            background: #111827;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark .form-group input[readonly] {
            background: #374151;
            color: #d1d5db;
        }

        body.dark .theme-button {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark .btn-batal {
            color: #e5e7eb;
            border-color: #6b7280;
        }

        body.dark .btn-batal:hover {
            background: #374151;
        }

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

            .main {
                width: 100%;
                margin-left: 0;
                padding: 20px 14px;
            }

            .topbar h1 {
                font-size: 21px;
            }

            .topbar p {
                line-height: 1.5;
            }

            .profile-card {
                padding: 20px 16px;
            }

            .profile-header {
                gap: 12px;
            }

            .avatar {
                width: 55px;
                height: 55px;
            }

            .btn {
                margin: 0 5px 8px 0;
            }
        }

        @media (max-width: 360px) {
            .menu a {
                gap: 7px;
                padding: 10px 7px;
                font-size: 11px;
            }

            .profile-card {
                padding: 17px 12px;
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

            <a href="v_tanggapan_siswa.php">
                <i class="fa-solid fa-comments"></i>
                <span>Tanggapan</span>
            </a>

            <a href="v_profil_siswa.php" class="active">
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

    <main class="main">
        <div class="topbar">
            <div>
                <h1>Profil Siswa</h1>
                <p>Lihat dan kelola informasi akun kamu.</p>
            </div>

            <button type="button" class="theme-button" id="tombolTema"
                    aria-label="Ganti tema">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>

        <section class="profile-card">
            <div class="profile-header">
                <div class="avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h2><?= e($siswa['nama']); ?></h2>
                    <p>
                        Siswa ·
                        <?= e($siswa['kelas'] ?: 'Kelas belum diatur'); ?>
                    </p>
                </div>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert <?= e($jenisPesan === 'error' ? 'error' : 'success'); ?>">
                    <?= e($pesan); ?>
                </div>
            <?php endif; ?>

            <div id="tampilanProfil">
                <h3 class="section-title">Informasi Akun</h3>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="<?= e($siswa['nama']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?= e($siswa['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text"
                           value="<?= e($siswa['kelas'] ?: 'Belum diatur'); ?>"
                           readonly>
                </div>

                <button type="button" class="btn btn-simpan" id="tombolEdit">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Profil
                </button>
            </div>

            <div id="formEditProfil" class="hidden">
                <h3 class="section-title">Edit Informasi Akun</h3>

                <form action="../asset/aksi_update_profil_siswa.php"
                      method="POST"
                      autocomplete="off">

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text"
                               id="nama"
                               name="nama"
                               value="<?= e($siswa['nama']); ?>"
                               maxlength="100"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               value="<?= e($siswa['username']); ?>"
                               maxlength="50"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text"
                               id="kelas"
                               value="<?= e($siswa['kelas'] ?: 'Belum diatur'); ?>"
                               readonly>
                        <p class="hint">
                            Perubahan kelas hanya dapat dilakukan oleh admin.
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru (opsional)</label>
                        <div class="password-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   autocomplete="new-password"
                                   placeholder="Kosongkan jika tidak ingin mengganti">
                            <button type="button"
                                    class="toggle-password"
                                    data-target="password"
                                    aria-label="Tampilkan password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p class="hint">
                            Kosongkan jika tidak ingin mengubah password.
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="konfirmasi_password">
                            Konfirmasi Password Baru
                        </label>
                        <div class="password-wrapper">
                            <input type="password"
                                   id="konfirmasi_password"
                                   name="konfirmasi_password"
                                   autocomplete="new-password"
                                   placeholder="Ulangi password baru">
                            <button type="button"
                                    class="toggle-password"
                                    data-target="konfirmasi_password"
                                    aria-label="Tampilkan konfirmasi password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-simpan">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Perubahan
                    </button>

                    <button type="button" class="btn btn-batal" id="tombolBatal">
                        <i class="fa-solid fa-xmark"></i>
                        Batal
                    </button>
                </form>
            </div>
        </section>
    </main>

    <script>
        const tampilanProfil = document.getElementById('tampilanProfil');
        const formEditProfil = document.getElementById('formEditProfil');

        document.getElementById('tombolEdit').addEventListener('click', function () {
            tampilanProfil.classList.add('hidden');
            formEditProfil.classList.remove('hidden');
        });

        document.getElementById('tombolBatal').addEventListener('click', function () {
            if (confirm('Batalkan perubahan profil?')) {
                window.location.reload();
            }
        });

        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                    this.setAttribute('aria-label', 'Sembunyikan password');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                    this.setAttribute('aria-label', 'Tampilkan password');
                }
            });
        });

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