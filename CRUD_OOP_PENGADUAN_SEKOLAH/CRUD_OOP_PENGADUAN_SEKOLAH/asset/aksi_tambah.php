<?php

session_start();


// =====================================================
// KONEKSI DATABASE
// =====================================================

require_once "../model/m_koneksi.php";


// =====================================================
// CEK LOGIN ADMIN
// =====================================================

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {

    header(
        "Location: ../view/v_login.php"
    );

    exit;
}


// =====================================================
// CEK FORM
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        "Location: ../view/v_tambah_siswa.php"
    );

    exit;
}


// =====================================================
// AMBIL DATA FORM
// =====================================================

$nama = trim(
    $_POST['nama'] ?? ''
);

$username = trim(
    $_POST['username'] ?? ''
);

$password = trim(
    $_POST['password'] ?? ''
);

$kelas = trim(
    $_POST['kelas'] ?? ''
);


// =====================================================
// CEK DATA KOSONG
// =====================================================

if (
    $nama == '' ||
    $username == '' ||
    $password == '' ||
    $kelas == ''
) {

    echo "

    <script>

        alert('Semua data harus diisi!');

        window.location.href =
        '../view/v_tambah_siswa.php';

    </script>

    ";

    exit;
}


// =====================================================
// PROSES DATABASE
// =====================================================

try {

    // Membuat koneksi

    $koneksi = new Koneksi();

    $db = $koneksi->getConnection();


    // =================================================
    // CEK USERNAME
    // =================================================

    $cek = $db->prepare("
        SELECT id
        FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $cek->execute([

        ':username' => $username

    ]);


    // Kalau username sudah ada

    if ($cek->fetch(PDO::FETCH_ASSOC)) {

        echo "

        <script>

            alert('Username sudah digunakan!');

            window.location.href =
            '../view/v_tambah_siswa.php';

        </script>

        ";

        exit;
    }


    // =================================================
    // HASH PASSWORD
    // =================================================

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    // =================================================
    // INSERT DATA SISWA
    // =================================================

    $sql = "

        INSERT INTO users

        (
            nama,
            username,
            password,
            role,
            kelas
        )

        VALUES

        (
            :nama,
            :username,
            :password,
            :role,
            :kelas
        )

    ";


    $stmt = $db->prepare($sql);


    $stmt->execute([

        ':nama' =>
            $nama,

        ':username' =>
            $username,

        ':password' =>
            $passwordHash,

        ':role' =>
            'siswa',

        ':kelas' =>
            $kelas

    ]);


    // =================================================
    // BERHASIL
    // =================================================

    echo "

    <script>

        alert(
            'Data siswa berhasil ditambahkan!'
        );

        window.location.href =
        '../view/v_kelola_siswa.php';

    </script>

    ";

    exit;


} catch (PDOException $e) {


    // =================================================
    // ERROR
    // =================================================

    echo "

    <!DOCTYPE html>

    <html>

    <head>

        <title>Error</title>

        <style>

            body {
                font-family: Arial;
                background: #f4f7fb;
                padding: 30px;
            }

            .box {
                background: white;
                max-width: 700px;
                margin: auto;
                padding: 25px;
                border-radius: 10px;
            }

            h2 {
                color: red;
            }

            pre {
                background: #eee;
                padding: 15px;
                overflow-x: auto;
            }

            a {
                display: inline-block;
                margin-top: 15px;
                padding: 10px 15px;
                background: #365486;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }

        </style>

    </head>


    <body>

        <div class='box'>

            <h2>
                Gagal Menambahkan Siswa
            </h2>

            <p>
                Terjadi kesalahan database:
            </p>

            <pre>"
            . htmlspecialchars(
                $e->getMessage()
            )
            . "</pre>

            <a
                href='../view/v_tambah_siswa.php'
            >
                Kembali
            </a>

        </div>

    </body>

    </html>

    ";

    exit;
}

?>