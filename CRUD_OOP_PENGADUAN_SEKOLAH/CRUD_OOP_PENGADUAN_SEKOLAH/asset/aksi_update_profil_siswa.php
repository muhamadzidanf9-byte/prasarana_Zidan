<?php
session_start();

require_once __DIR__ . '/../model/m_koneksi.php';

function kembaliDenganPesan($pesan, $jenis = 'error')
{
    $_SESSION['pesan_profil_siswa'] = $pesan;
    $_SESSION['jenis_pesan_profil_siswa'] = $jenis;

    header('Location: ../view/v_profil_siswa.php');
    exit;
}

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'siswa'
) {
    header('Location: ../view/v_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/v_profil_siswa.php');
    exit;
}

$id = $_SESSION['id'] ?? 0;

$nama = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

if ($nama === '' || $username === '') {
    kembaliDenganPesan('Nama dan username wajib diisi.');
}

if (strlen($nama) > 100 || strlen($username) > 50) {
    kembaliDenganPesan('Nama maksimal 100 karakter dan username maksimal 50 karakter.');
}

if (preg_match('/\s/', $username)) {
    kembaliDenganPesan('Username tidak boleh mengandung spasi.');
}

if ($password !== $konfirmasiPassword) {
    kembaliDenganPesan('Konfirmasi password tidak cocok.');
}

try {
    $koneksi = new Koneksi();
    $conn = $koneksi->getConnection();

    // Pastikan username belum digunakan akun lain.
    $cek = $conn->prepare("
        SELECT id
        FROM users
        WHERE username = :username AND id != :id
        LIMIT 1
    ");

    $cek->execute([
        'username' => $username,
        'id' => $id
    ]);

    if ($cek->fetch(PDO::FETCH_ASSOC)) {
        kembaliDenganPesan('Username sudah digunakan. Silakan pilih username lain.');
    }

    if ($password !== '') {
        // Password tetap di-hash meskipun panjangnya bebas.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users
            SET nama = :nama,
                username = :username,
                password = :password
            WHERE id = :id AND role = 'siswa'
        ");

        $stmt->execute([
            'nama' => $nama,
            'username' => $username,
            'password' => $passwordHash,
            'id' => $id
        ]);
    } else {
        // Password kosong berarti password lama tetap digunakan.
        $stmt = $conn->prepare("
            UPDATE users
            SET nama = :nama,
                username = :username
            WHERE id = :id AND role = 'siswa'
        ");

        $stmt->execute([
            'nama' => $nama,
            'username' => $username,
            'id' => $id
        ]);
    }

    $_SESSION['nama'] = $nama;
    $_SESSION['username'] = $username;

    kembaliDenganPesan('Profil berhasil diperbarui.', 'success');

} catch (PDOException $e) {
    error_log('Gagal memperbarui profil siswa: ' . $e->getMessage());
    kembaliDenganPesan('Profil gagal diperbarui. Silakan coba lagi.');
}
?>