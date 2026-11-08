<?php

session_start();

require_once "../model/m_koneksi.php";


// ========================================
// CEK LOGIN ADMIN
// ========================================

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../view/v_login.php");
    exit;
}


// ========================================
// AMBIL ID SISWA
// ========================================

$id = $_GET['id'] ?? '';


// ========================================
// CEK ID
// ========================================

if ($id == '') {

    echo "
    <script>
        alert('ID siswa tidak ditemukan!');
        window.location.href='../view/v_kelola_siswa.php';
    </script>
    ";

    exit;
}


// ========================================
// PROSES HAPUS
// ========================================

try {

    $koneksi = new Koneksi();

    $db = $koneksi->getConnection();


    // Hanya menghapus user dengan role siswa

    $sql = "
        DELETE FROM users
        WHERE id = :id
        AND role = 'siswa'
    ";


    $stmt = $db->prepare($sql);


    $stmt->execute([
        ':id' => $id
    ]);


    // ====================================
    // CEK DATA TERHAPUS
    // ====================================

    if ($stmt->rowCount() > 0) {

        echo "
        <script>

            alert('Data siswa berhasil dihapus!');

            window.location.href =
            '../view/v_kelola_siswa.php';

        </script>
        ";

    } else {

        echo "
        <script>

            alert('Data siswa tidak ditemukan!');

            window.location.href =
            '../view/v_kelola_siswa.php';

        </script>
        ";

    }

    exit;


} catch (PDOException $e) {

    echo "<h2>Data siswa gagal dihapus</h2>";

    echo "<p>";

    echo htmlspecialchars(
        $e->getMessage()
    );

    echo "</p>";

    echo "
        <a href='../view/v_kelola_siswa.php'>
            Kembali
        </a>
    ";

    exit;
}

?>