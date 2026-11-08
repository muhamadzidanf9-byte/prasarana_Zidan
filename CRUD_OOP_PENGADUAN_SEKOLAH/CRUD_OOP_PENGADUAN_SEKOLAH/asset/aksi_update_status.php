<?php

session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../view/v_login.php");
    exit;
}

require_once "../model/m_koneksi.php";

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/v_data_pengaduan.php");
    exit;
}

$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';

$status_valid = [
    'Menunggu',
    'Diproses',
    'Selesai'
];

if (
    empty($id) ||
    !in_array($status, $status_valid)
) {

    echo "<script>

        alert('Data status tidak valid!');

        window.location.href =
        '../view/v_data_pengaduan.php';

    </script>";

    exit;
}

try {

    $stmt = $conn->prepare("
        UPDATE pengaduan
        SET status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $status,
        $id
    ]);

    header(
        "Location: ../view/v_data_pengaduan.php"
    );

    exit;

} catch (PDOException $e) {

    echo "<script>

        alert('Status pengaduan gagal diubah!');

        window.location.href =
        '../view/v_data_pengaduan.php';

    </script>";

    exit;
}

?>