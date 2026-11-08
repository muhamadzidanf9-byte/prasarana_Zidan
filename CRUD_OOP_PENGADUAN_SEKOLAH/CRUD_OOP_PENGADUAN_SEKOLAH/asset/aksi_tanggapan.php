<?php

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../view/v_login.php");
    exit;
}

require_once "../model/m_koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/v_tanggapan.php");
    exit;
}

$pengaduan_id = $_POST['pengaduan_id'] ?? '';
$isi_tanggapan = trim($_POST['isi_tanggapan'] ?? '');

$user_id = $_SESSION['id'];

if (empty($pengaduan_id) || empty($isi_tanggapan)) {

    echo "<script>
            alert('Tanggapan harus diisi!');
            window.history.back();
          </script>";

    exit;
}

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();


// Cek apakah pengaduan ada

$cek = $conn->prepare("
    SELECT id
    FROM pengaduan
    WHERE id = :id
");

$cek->execute([
    ':id' => $pengaduan_id
]);

if (!$cek->fetch()) {

    echo "<script>
            alert('Pengaduan tidak ditemukan!');
            window.location='../view/v_tanggapan.php';
          </script>";

    exit;
}


// Simpan tanggapan

$query = "
    INSERT INTO tanggapan
    (
        pengaduan_id,
        user_id,
        isi_tanggapan,
        tanggal
    )
    VALUES
    (
        :pengaduan_id,
        :user_id,
        :isi_tanggapan,
        NOW()
    )
";

$stmt = $conn->prepare($query);

$stmt->execute([
    ':pengaduan_id' => $pengaduan_id,
    ':user_id' => $user_id,
    ':isi_tanggapan' => $isi_tanggapan
]);


echo "<script>

        alert('Tanggapan berhasil dikirim!');

        window.location='../view/v_tanggapan.php';

      </script>";

exit;

?>