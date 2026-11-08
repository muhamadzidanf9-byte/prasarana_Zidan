<?php

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once "../model/m_koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: v_tanggapan.php");
    exit;
}

$id = $_GET['id'];

$koneksi = new Koneksi();
$conn = $koneksi->getConnection();

$query = "
    SELECT
        pengaduan.id,
        pengaduan.judul,
        pengaduan.isi,
        pengaduan.foto,
        pengaduan.tanggal,
        pengaduan.status,
        users.nama,
        users.kelas,
        kategori.nama_kategori
    FROM pengaduan
    INNER JOIN users
        ON pengaduan.user_id = users.id
    INNER JOIN kategori
        ON pengaduan.kategori_id = kategori.id
    WHERE pengaduan.id = :id
";

$stmt = $conn->prepare($query);
$stmt->execute([':id' => $id]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "<script>
            alert('Data pengaduan tidak ditemukan!');
            window.location='v_tanggapan.php';
          </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Berikan Tanggapan</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        h1 {
            color: #0f1035;
            margin-bottom: 25px;
        }

        .data {
            margin-bottom: 18px;
        }

        .data label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #555;
        }

        .data p {
            background: #f5f6f8;
            padding: 12px;
            border-radius: 7px;
        }

        .foto {
            width: 180px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            resize: vertical;
            font-size: 14px;
        }

        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            padding: 11px 18px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-kirim {
            background: #365486;
            color: white;
        }

        .btn-kembali {
            background: #6c757d;
            color: white;
        }

    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <h1>Berikan Tanggapan</h1>


            <div class="data">

                <label>Nama Siswa</label>

                <p>
                    <?= htmlspecialchars($data['nama']); ?>
                </p>

            </div>


            <div class="data">

                <label>Kelas</label>

                <p>
                    <?= htmlspecialchars($data['kelas']); ?>
                </p>

            </div>


            <div class="data">

                <label>Kategori</label>

                <p>
                    <?= htmlspecialchars($data['nama_kategori']); ?>
                </p>

            </div>


            <div class="data">

                <label>Judul Pengaduan</label>

                <p>
                    <?= htmlspecialchars($data['judul']); ?>
                </p>

            </div>


            <div class="data">

                <label>Isi Pengaduan</label>

                <p>
                    <?= nl2br(htmlspecialchars($data['isi'])); ?>
                </p>

            </div>


            <div class="data">

                <label>Foto Pengaduan</label>

                <?php if (!empty($data['foto'])): ?>

                    <img
                        src="../upload/pengaduan/<?= htmlspecialchars($data['foto']); ?>"
                        class="foto"
                        onclick="window.open(this.src, '_blank')"
                    >

                <?php else: ?>

                    <p>Tidak ada foto.</p>

                <?php endif; ?>

            </div>


            <div class="data">

                <label>Status</label>

                <p>
                    <?= htmlspecialchars($data['status']); ?>
                </p>

            </div>


            <form action="../asset/aksi_tanggapan.php" method="POST">

                <input
                    type="hidden"
                    name="pengaduan_id"
                    value="<?= $data['id']; ?>"
                >


                <div class="data">

                    <label>Tanggapan Admin</label>

                    <textarea
                        name="isi_tanggapan"
                        placeholder="Tulis tanggapan untuk siswa..."
                        required
                    ></textarea>

                </div>


                <div class="buttons">

                    <button
                        type="submit"
                        name="kirim"
                        class="btn btn-kirim"
                    >
                        💬 Kirim Tanggapan
                    </button>


                    <a
                        href="v_tanggapan.php"
                        class="btn btn-kembali"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>