<?php
session_start();
require_once "../model/m_koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');

    if ($nama == '' || $username == '' || $password == '' || $kelas == '') {

        echo "<script>
            alert('Semua data harus diisi!');
        </script>";

    } else {

        try {

            $koneksi = new Koneksi();
            $db = $koneksi->getConnection();

            // Cek username
            $cek = $db->prepare("
                SELECT id
                FROM users
                WHERE username = :username
                LIMIT 1
            ");

            $cek->execute([
                ':username' => $username
            ]);

            if ($cek->fetch(PDO::FETCH_ASSOC)) {

                echo "<script>
                    alert('Username sudah digunakan!');
                </script>";

            } else {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

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
                    ':nama' => $nama,
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':role' => 'siswa',
                    ':kelas' => $kelas
                ]);

                echo "<script>
                    alert('Registrasi berhasil!');
                    window.location.href='v_login.php';
                </script>";

                exit;
            }

        } catch (PDOException $e) {

            echo "<script>
                alert('Registrasi gagal!');
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Registrasi Siswa</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    min-height: 100vh;
    background: #f4f7fb;
    display: flex;
    justify-content: center;
    align-items: center;
}

.register-box {
    width: 430px;
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #0F1035;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #777;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 17px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 7px;
    outline: none;
}

input:focus {
    border-color: #365486;
}

/* PASSWORD */

.password-box {
    position: relative;
}

.password-box input {
    padding-right: 45px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 20px;
    user-select: none;
}

button {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 7px;
    background: #365486;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #2d4775;
}

.login {
    text-align: center;
    margin-top: 20px;
    color: #777;
}

.login a {
    color: #365486;
    text-decoration: none;
    font-weight: bold;
}

</style>

</head>

<body>

<div class="register-box">

    <h2>Registrasi Siswa</h2>

    <p class="subtitle">
        Buat akun untuk mengirim pengaduan
    </p>

    <form method="POST">

        <div class="form-group">

            <label>Nama Siswa</label>

            <input
                type="text"
                name="nama"
                placeholder="Masukkan nama lengkap"
                required
            >

        </div>

        <div class="form-group">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Masukkan username"
                required
            >

        </div>

        <div class="form-group">

            <label>Password</label>

            <div class="password-box">

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

                <span
                    class="toggle-password"
                    onclick="togglePassword('password', this)"
                >👁️</span>

            </div>

        </div>

        <div class="form-group">

            <label>Kelas</label>

            <input
                type="text"
                name="kelas"
                placeholder="Contoh: XI RPL 1"
                required
            >

        </div>

        <button type="submit">
            Registrasi
        </button>

    </form>

    <div class="login">

        Sudah punya akun?
        <a href="v_login.php">Login</a>

    </div>

</div>

<script>

function togglePassword(id, icon) {

    const password = document.getElementById(id);

    if (password.type === "password") {

        password.type = "text";
        icon.textContent = "🙈";

    } else {

        password.type = "password";
        icon.textContent = "👁️";

    }

}

</script>

</body>
</html>