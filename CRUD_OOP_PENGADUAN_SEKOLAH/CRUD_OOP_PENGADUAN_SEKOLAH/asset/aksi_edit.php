<?php

session_start();

require_once "../model/m_koneksi.php";


// ==================================================
// CEK LOGIN ADMIN
// ==================================================

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


// ==================================================
// CEK METHOD
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        "Location: ../view/v_kelola_siswa.php"
    );

    exit;
}


// ==================================================
// AMBIL DATA
// ==================================================

$id = $_POST['id'] ?? '';

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


// ==================================================
// VALIDASI
// ==================================================

if (
    $id == '' ||
    $nama == '' ||
    $username == '' ||
    $kelas == ''
) {

    echo "

    <script>

        alert('Data belum lengkap!');

        window.location.href =
        '../view/v_kelola_siswa.php';

    </script>

    ";

    exit;
}


// ==================================================
// DATABASE
// ==================================================

try {

    $koneksi = new Koneksi();

    $db = $koneksi->getConnection();


    // ==================================================
    // CEK USERNAME
    // ==================================================

    $cek = $db->prepare("

        SELECT id

        FROM users

        WHERE username = :username

        AND id != :id

        LIMIT 1

    ");


    $cek->execute([

        ':username' => $username,

        ':id' => $id

    ]);


    if ($cek->fetch(PDO::FETCH_ASSOC)) {

        echo "

        <script>

            alert('Username sudah digunakan!');

            window.location.href =
            '../view/v_edit_siswa.php?id=$id';

        </script>

        ";

        exit;
    }


    // ==================================================
    // JIKA PASSWORD DIUBAH
    // ==================================================

    if ($password != '') {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $sql = "

            UPDATE users

            SET
                nama = :nama,
                username = :username,
                password = :password,
                kelas = :kelas

            WHERE id = :id

            AND role = 'siswa'

        ";


        $stmt = $db->prepare($sql);


        $stmt->execute([

            ':nama' =>
                $nama,

            ':username' =>
                $username,

            ':password' =>
                $passwordHash,

            ':kelas' =>
                $kelas,

            ':id' =>
                $id

        ]);

    }


    // ==================================================
    // JIKA PASSWORD TIDAK DIUBAH
    // ==================================================

    else {

        $sql = "

            UPDATE users

            SET
                nama = :nama,
                username = :username,
                kelas = :kelas

            WHERE id = :id

            AND role = 'siswa'

        ";


        $stmt = $db->prepare($sql);


        $stmt->execute([

            ':nama' =>
                $nama,

            ':username' =>
                $username,

            ':kelas' =>
                $kelas,

            ':id' =>
                $id

        ]);

    }


    // ==================================================
    // BERHASIL
    // ==================================================

    echo "

    <script>

        alert(
            'Data siswa berhasil diubah!'
        );

        window.location.href =
        '../view/v_kelola_siswa.php';

    </script>

    ";

    exit;


} catch (PDOException $e) {

    echo "

    <h2>
        Gagal Mengubah Data Siswa
    </h2>

    <p>"
    . htmlspecialchars(
        $e->getMessage()
    )
    . "</p>

    <a
        href='../view/v_kelola_siswa.php'
    >
        Kembali
    </a>

    ";

    exit;
}

?>