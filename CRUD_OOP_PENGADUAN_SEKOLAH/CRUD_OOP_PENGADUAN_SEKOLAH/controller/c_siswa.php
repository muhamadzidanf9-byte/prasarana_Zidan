<?php

session_start();

require_once "../model/m_koneksi.php";
require_once "../model/m_siswa.php";

// Cek login admin
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../view/v_login.php");
    exit;
}

$koneksi = new Koneksi();
$db = $koneksi->getConnection();

$siswa = new Siswa($db);


// ===============================
// TAMBAH SISWA
// ===============================

if (isset($_POST['tambah'])) {

    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $kelas = trim($_POST['kelas']);

    if (
        $nama == "" ||
        $username == "" ||
        $password == "" ||
        $kelas == ""
    ) {

        echo "<script>
                alert('Semua data harus diisi!');
                window.location.href='../view/v_tambah_siswa.php';
              </script>";
        exit;
    }

    // Cek username
    $cek = $db->prepare(
        "SELECT id FROM users WHERE username = :username"
    );

    $cek->execute([
        ':username' => $username
    ]);

    if ($cek->fetch()) {

        echo "<script>
                alert('Username sudah digunakan!');
                window.location.href='../view/v_tambah_siswa.php';
              </script>";
        exit;
    }

    $siswa->tambahSiswa(
        $nama,
        $username,
        $password,
        $kelas
    );

    echo "<script>
            alert('Data siswa berhasil ditambahkan!');
            window.location.href='../view/v_kelola_siswa.php';
          </script>";
    exit;
}


// ===============================
// EDIT SISWA
// ===============================

if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $kelas = trim($_POST['kelas']);

    if (
        $nama == "" ||
        $username == "" ||
        $kelas == ""
    ) {

        echo "<script>
                alert('Nama, username dan kelas harus diisi!');
                window.history.back();
              </script>";
        exit;
    }

    // Cek username milik siswa lain
    $cek = $db->prepare(
        "SELECT id FROM users
         WHERE username = :username
         AND id != :id"
    );

    $cek->execute([
        ':username' => $username,
        ':id' => $id
    ]);

    if ($cek->fetch()) {

        echo "<script>
                alert('Username sudah digunakan siswa lain!');
                window.history.back();
              </script>";
        exit;
    }

    $siswa->editSiswa(
        $id,
        $nama,
        $username,
        $password,
        $kelas
    );

    echo "<script>
            alert('Data siswa berhasil diubah!');
            window.location.href='../view/v_kelola_siswa.php';
          </script>";
    exit;
}


// ===============================
// HAPUS SISWA
// ===============================

if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    try {

        $siswa->hapusSiswa($id);

        echo "<script>
                alert('Data siswa berhasil dihapus!');
                window.location.href='../view/v_kelola_siswa.php';
              </script>";

    } catch (PDOException $e) {

        echo "<script>
                alert('Data siswa tidak dapat dihapus karena masih memiliki data pengaduan atau tanggapan!');
                window.location.href='../view/v_kelola_siswa.php';
              </script>";
    }

    exit;
}

?>