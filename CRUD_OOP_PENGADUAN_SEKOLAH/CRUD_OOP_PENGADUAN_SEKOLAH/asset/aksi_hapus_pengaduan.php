<?php

session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../view/v_login.php");
    exit;
}

require_once "../model/m_koneksi.php";

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: ../view/v_data_pengaduan.php");
    exit;
}

try {

    // Ambil foto pengaduan
    $stmt = $conn->prepare(
        "SELECT foto FROM pengaduan WHERE id = ?"
    );

    $stmt->execute([$id]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo "<script>
                alert('Data pengaduan tidak ditemukan!');
                window.location.href='../view/v_data_pengaduan.php';
              </script>";
        exit;
    }

    // Hapus tanggapan terlebih dahulu
    $stmt = $conn->prepare(
        "DELETE FROM tanggapan WHERE pengaduan_id = ?"
    );

    $stmt->execute([$id]);

    // Hapus pengaduan
    $stmt = $conn->prepare(
        "DELETE FROM pengaduan WHERE id = ?"
    );

    $stmt->execute([$id]);

    // Hapus foto
    if (!empty($data['foto'])) {

        $file = dirname(__DIR__) .
                "/upload/pengaduan/" .
                $data['foto'];

        if (file_exists($file)) {
            unlink($file);
        }
    }

    echo "<script>
            alert('Data pengaduan berhasil dihapus!');
            window.location.href='../view/v_data_pengaduan.php';
          </script>";

} catch (PDOException $e) {

    echo "<script>
            alert('Data pengaduan gagal dihapus!');
            window.location.href='../view/v_data_pengaduan.php';
          </script>";
}

exit;
?>