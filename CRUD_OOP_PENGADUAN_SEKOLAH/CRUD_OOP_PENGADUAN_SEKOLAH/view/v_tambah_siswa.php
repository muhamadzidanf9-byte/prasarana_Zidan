<?php
session_start();

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: v_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tambah Data Siswa</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    background: #f4f7fb;
}

.container {
    width: 90%;
    max-width: 550px;
    margin: 50px auto;
}

.box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

h2 {
    margin-top: 0;
    color: #0F1035;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
    color: #333;
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

.buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

.buttons a,
.buttons button {
    flex: 1;
    padding: 12px;
    border-radius: 7px;
    text-align: center;
    font-size: 14px;
}

.kembali {
    background: #ddd;
    color: #333;
    text-decoration: none;
}

.simpan {
    background: #365486;
    color: white;
    border: none;
    cursor: pointer;
}

</style>

</head>

<body>

<div class="container">

<div class="box">

<h2>Tambah Data Siswa</h2>

<form action="../asset/aksi_tambah.php" method="POST">

    <div class="form-group">

        <label>Nama Siswa</label>

        <input
            type="text"
            name="nama"
            placeholder="Masukkan nama siswa"
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

    <div class="buttons">

        <a
            href="v_kelola_siswa.php"
            class="kembali"
        >
            Kembali
        </a>

        <button
            type="submit"
            name="tambah"
            class="simpan"
        >
            Tambah Siswa
        </button>

    </div>

</form>

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