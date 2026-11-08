<?php

session_start();

$pesan = "";
$jenis = "";

if (isset($_GET['error'])) {

    if ($_GET['error'] == "1") {

        $pesan = "Username atau password salah!";
        $jenis = "error";

    } elseif ($_GET['error'] == "role") {

        $pesan = "Role akun tidak valid!";
        $jenis = "error";
    }
}


if (isset($_GET['success'])) {

    $pesan = "Registrasi berhasil! Silakan login.";
    $jenis = "success";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Pengaduan Sekolah</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        body {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

            background:
                linear-gradient(
                    135deg,
                    #365486,
                    #7FC7D9
                );
        }


        .login-box {

            width: 400px;

            background: white;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.20);
        }


        .title {

            text-align: center;

            color: #0F1035;

            font-size: 27px;

            font-weight: bold;

            margin-bottom: 8px;
        }


        .subtitle {

            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 28px;
        }


        .alert {

            padding: 12px;

            border-radius: 8px;

            text-align: center;

            margin-bottom: 20px;

            font-size: 14px;
        }


        .error {

            background: #ffe5e5;

            color: #c62828;
        }


        .success {

            background: #e5f8e9;

            color: #218838;
        }


        .form-group {

            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #333;

            font-weight: bold;

            font-size: 14px;
        }


        .input-box {

            position: relative;
        }


        .input-box input {

            width: 100%;

            padding:
                13px
                45px
                13px
                15px;

            border: 1px solid #ddd;

            border-radius: 10px;

            outline: none;

            font-size: 14px;

            transition: 0.3s;
        }


        .input-box input:focus {

            border-color: #365486;

            box-shadow:
                0 0 0 3px
                rgba(54, 84, 134, 0.10);
        }


        .toggle-password {

            position: absolute;

            right: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            cursor: pointer;

            font-size: 18px;

            user-select: none;
        }


        .btn-login {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 10px;

            background: #365486;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;
        }


        .btn-login:hover {

            background: #2d4670;
        }


        .register {

            text-align: center;

            margin-top: 23px;

            color: #777;

            font-size: 14px;
        }


        .register a {

            color: #365486;

            font-weight: bold;

            text-decoration: none;
        }


        .register a:hover {

            text-decoration: underline;
        }


        .footer {

            text-align: center;

            color: #aaa;

            font-size: 12px;

            margin-top: 25px;
        }

    </style>

</head>


<body>


<div class="login-box">


    <h1 class="title">
        Pengaduan Sekolah
    </h1>


    <p class="subtitle">
        Silakan masuk ke akun Anda
    </p>


    <?php if ($pesan != "") { ?>

        <div class="alert <?php echo $jenis; ?>">

            <?php echo $pesan; ?>

        </div>

    <?php } ?>


    <form
        action="../controller/c_login.php"
        method="POST"
    >


        <!-- USERNAME -->

        <div class="form-group">

            <label>
                Username
            </label>

            <div class="input-box">

                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    required
                >

            </div>

        </div>


        <!-- PASSWORD -->

        <div class="form-group">

            <label>
                Password
            </label>

            <div class="input-box">

                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Masukkan password"
                    required
                >

                <span
                    class="toggle-password"
                    onclick="togglePassword()"
                    id="eye"
                >
                    👁
                </span>

            </div>

        </div>


        <button
            type="submit"
            name="login"
            class="btn-login"
        >

            Login

        </button>


    </form>


    <div class="register">

        Belum memiliki akun?

        <a href="v_registrasi.php">
            Registrasi
        </a>

    </div>


    <div class="footer">

        Sistem Pengaduan Sarana Prasarana Sekolah

    </div>


</div>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const eye =
        document.getElementById("eye");


    if (password.type === "password") {

        password.type = "text";

        eye.innerHTML = "🙈";

    } else {

        password.type = "password";

        eye.innerHTML = "👁";

    }

}

</script>


</body>

</html>