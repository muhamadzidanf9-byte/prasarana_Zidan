<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: v_login.php");
    exit;
}

require_once __DIR__ . '/../model/m_koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: v_kategori.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$nama = trim($_POST['nama_kategori'] ?? '');

if ($id <= 0 || $nama === '') {
    header("Location: v_kategori.php?pesan=kosong");
    exit;
}

$db = new Koneksi();
$conn = $db->getConnection();

$cek = $conn->prepare(
    "SELECT id FROM kategori
     WHERE nama_kategori = ? AND id != ?"
);
$cek->execute([$nama, $id]);

if ($cek->fetch()) {
    header("Location: v_kategori.php?pesan=duplikat");
    exit;
}

$stmt = $conn->prepare(
    "UPDATE kategori SET nama_kategori = ? WHERE id = ?"
);
$stmt->execute([$nama, $id]);

header("Location: v_kategori.php?pesan=edit");
exit;