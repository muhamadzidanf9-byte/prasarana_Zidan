<?php

session_start();

require_once "../model/m_koneksi.php";


/*
|--------------------------------------------------------------------------
| CEK LOGIN SISWA
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'siswa'
) {

    header("Location: ../view/v_login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CEK METHOD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ../view/v_buat_pengaduan.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['id'] ?? '';

$judul = trim(
    $_POST['judul'] ?? ''
);

$kategori_id = $_POST['kategori_id'] ?? '';

$isi = trim(
    $_POST['isi'] ?? ''
);


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if (
    $user_id == '' ||
    $judul == '' ||
    $kategori_id == '' ||
    $isi == ''
) {

    echo "
    <script>
        alert('Semua data wajib diisi!');
        window.location.href='../view/v_buat_pengaduan.php';
    </script>
    ";

    exit;
}


try {

    $koneksi = new Koneksi();

    $db = $koneksi->getConnection();


    /*
    |--------------------------------------------------------------------------
    | CEK KATEGORI
    |--------------------------------------------------------------------------
    */

    $cekKategori = $db->prepare("
        SELECT id
        FROM kategori
        WHERE id = :id
        LIMIT 1
    ");

    $cekKategori->execute([
        ':id' => $kategori_id
    ]);


    if (!$cekKategori->fetch(PDO::FETCH_ASSOC)) {

        echo "
        <script>
            alert('Kategori tidak ditemukan!');
            window.location.href='../view/v_buat_pengaduan.php';
        </script>
        ";

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | FOTO
    |--------------------------------------------------------------------------
    */

    $foto = null;


    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        /*
        | CEK ERROR
        */

        if (
            $_FILES['foto']['error'] !== UPLOAD_ERR_OK
        ) {

            echo "
            <script>
                alert('Foto gagal diupload!');
                window.location.href='../view/v_buat_pengaduan.php';
            </script>
            ";

            exit;
        }


        /*
        | AMBIL DATA FOTO
        */

        $namaFile =
            $_FILES['foto']['name'];

        $tmpFile =
            $_FILES['foto']['tmp_name'];

        $ukuranFile =
            $_FILES['foto']['size'];


        /*
        | CEK EKSTENSI
        */

        $ekstensi =
            strtolower(
                pathinfo(
                    $namaFile,
                    PATHINFO_EXTENSION
                )
            );


        $ekstensiValid = [
            'jpg',
            'jpeg',
            'png'
        ];


        if (
            !in_array(
                $ekstensi,
                $ekstensiValid
            )
        ) {

            echo "
            <script>
                alert('Format foto harus JPG, JPEG, atau PNG!');
                window.location.href='../view/v_buat_pengaduan.php';
            </script>
            ";

            exit;
        }


        /*
        | MAKSIMAL 5 MB
        */

        if (
            $ukuranFile >
            5 * 1024 * 1024
        ) {

            echo "
            <script>
                alert('Ukuran foto maksimal 5 MB!');
                window.location.href='../view/v_buat_pengaduan.php';
            </script>
            ";

            exit;
        }


        /*
        | CEK TIPE FILE
        */

        $mimeType =
            mime_content_type($tmpFile);


        $mimeValid = [
            'image/jpeg',
            'image/png'
        ];


        if (
            !in_array(
                $mimeType,
                $mimeValid
            )
        ) {

            echo "
            <script>
                alert('File yang dipilih bukan gambar yang valid!');
                window.location.href='../view/v_buat_pengaduan.php';
            </script>
            ";

            exit;
        }


        /*
        | NAMA FILE BARU
        */

        $namaFotoBaru =
            'pengaduan_' .
            uniqid() .
            '.' .
            $ekstensi;


        /*
        | FOLDER UPLOAD
        */

        $folder =
            "../upload/pengaduan/";


        /*
        | BUAT FOLDER JIKA BELUM ADA
        */

        if (!is_dir($folder)) {

            mkdir(
                $folder,
                0777,
                true
            );
        }


        /*
        | PINDAHKAN FOTO
        */

        if (
            !move_uploaded_file(
                $tmpFile,
                $folder . $namaFotoBaru
            )
        ) {

            echo "
            <script>
                alert('Foto gagal disimpan!');
                window.location.href='../view/v_buat_pengaduan.php';
            </script>
            ";

            exit;
        }


        /*
        | SIMPAN NAMA FOTO
        */

        $foto = $namaFotoBaru;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT PENGADUAN
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO pengaduan
        (
            user_id,
            kategori_id,
            judul,
            isi,
            foto,
            tanggal,
            status
        )
        VALUES
        (
            :user_id,
            :kategori_id,
            :judul,
            :isi,
            :foto,
            NOW(),
            'Menunggu'
        )
    ";


    $stmt = $db->prepare($sql);


    $stmt->execute([
        ':user_id' => $user_id,
        ':kategori_id' => $kategori_id,
        ':judul' => $judul,
        ':isi' => $isi,
        ':foto' => $foto
    ]);


    /*
    |--------------------------------------------------------------------------
    | BERHASIL
    |--------------------------------------------------------------------------
    */

    echo "
    <script>

        alert('Pengaduan berhasil dikirim!');

        window.location.href =
        '../view/v_dashboard_siswa.php';

    </script>
    ";

    exit;


} catch (PDOException $e) {

    echo "<h2>Pengaduan gagal dikirim</h2>";

    echo "<p>"
        . htmlspecialchars($e->getMessage())
        . "</p>";

    echo "<br>";

    echo "
        <a href='../view/v_buat_pengaduan.php'>
            Kembali
        </a>
    ";

    exit;
}

?>