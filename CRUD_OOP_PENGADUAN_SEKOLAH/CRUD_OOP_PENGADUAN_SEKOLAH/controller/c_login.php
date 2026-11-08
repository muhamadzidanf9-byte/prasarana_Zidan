<?php

session_start();

require_once "../model/m_koneksi.php";

$koneksi = new Koneksi();
$db = $koneksi->getConnection();

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {

        echo "<script>
                alert('Username dan password harus diisi!');
                window.location.href='../view/v_login.php';
              </script>";
        exit;
    }

    $stmt = $db->prepare(
        "SELECT * FROM users WHERE username = :username"
    );

    $stmt->execute([
        ':username' => $username
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // Cek password yang sudah di-hash
        $passwordBenar = password_verify(
            $password,
            $user['password']
        );

        // Untuk password lama yang masih berupa teks biasa
        if (!$passwordBenar && $password === $user['password']) {

            $passwordBaru = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $update = $db->prepare(
                "UPDATE users 
                 SET password = :password 
                 WHERE id = :id"
            );

            $update->execute([
                ':password' => $passwordBaru,
                ':id' => $user['id']
            ]);

            $passwordBenar = true;
        }

        if ($passwordBenar) {

            session_regenerate_id(true);

            $_SESSION['login'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['kelas'] = $user['kelas'];

            if ($user['role'] == 'admin') {

                header("Location: ../view/v_dashboard_admin.php");
                exit;

            } elseif ($user['role'] == 'siswa') {

                header("Location: ../view/v_dashboard_siswa.php");
                exit;

            } else {

                session_destroy();

                echo "<script>
                        alert('Role pengguna tidak valid!');
                        window.location.href='../view/v_login.php';
                      </script>";
                exit;
            }

        } else {

            echo "<script>
                    alert('Username atau password salah!');
                    window.location.href='../view/v_login.php';
                  </script>";
            exit;
        }

    } else {

        echo "<script>
                alert('Username atau password salah!');
                window.location.href='../view/v_login.php';
              </script>";
        exit;
    }
}
?>