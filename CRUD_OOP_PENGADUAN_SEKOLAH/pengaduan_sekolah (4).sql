-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Nov 2026 pada 04.23
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pengaduan_sekolah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Ruang Kelas'),
(2, 'Toilet'),
(4, 'Perpustakaan'),
(5, 'Komputer'),
(6, 'Meja dan Kursi'),
(7, 'Fasilitas Olahraga'),
(8, 'Listrik'),
(9, 'Kebersihan'),
(10, 'Lainnya'),
(11, 'lapangan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `foto` varchar(500) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `status` enum('Menunggu','Diproses','Selesai') DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `user_id`, `kategori_id`, `judul`, `isi`, `foto`, `tanggal`, `status`) VALUES
(5, 15, 9, 'toilet siswa kotor', 'toilet dekat masjid kotor dan bau', NULL, '2026-10-15 07:14:22', 'Diproses'),
(6, 15, 6, 'meja patah', 'meja patah di kelas 12 rpl 4', 'pengaduan_6ad0200ec0b21.png', '2026-10-15 07:36:30', 'Selesai'),
(7, 15, 1, 'wifi lemot', 'wifi di kelas 12 lemot', NULL, '2026-10-21 23:19:30', 'Selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tanggapan`
--

CREATE TABLE `tanggapan` (
  `id` int(11) NOT NULL,
  `pengaduan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `isi_tanggapan` text NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `tanggapan`
--

INSERT INTO `tanggapan` (`id`, `pengaduan_id`, `user_id`, `isi_tanggapan`, `tanggal`) VALUES
(3, 5, 2, 'baik akan saya tanggapan', '2026-10-15 07:15:08'),
(4, 7, 2, 'sudah', '2026-10-21 23:21:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa') NOT NULL,
  `kelas` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `kelas`) VALUES
(1, 'zidan', 'zidan', '$2y$10$1Nv1vvtQhe6qcoSpNzy0wuxlR.qrUAp11n1dbDeJiPASi2ppwAeI2', 'siswa', 'XII RPL 4'),
(2, 'admin', 'admin', '$2y$10$TVAYyGwpQS6HZmM88HhCAOtG14KEORW66lwfm0ZKiy3uKDAc4IWnm', 'admin', NULL),
(3, 'iman', 'iman', '$2y$10$h3kmf83VL7mPHYT4ig3ATecsmhZhXGng4zArkIHR1ER9odlCthecq', 'siswa', 'XII RPL 4'),
(7, 'ilham', 'ilham', '$2y$10$NgcpbVAFqwMXJlwrx77jS.P3EpJ4Lz8AMp.GxpInMvfdl.EMx9yJa', 'siswa', 'XII RPL 4'),
(10, 'shodik', 'shodik', '$2y$10$0OFk4QSr546q0pxHZlqxl.22MMhlwI0y5f.fHglPD.6sllMYC2Uuy', 'siswa', 'XII RPL 4'),
(12, 'sasuke', 'sasuke', '$2y$10$1Un0x7TCFoQ3FokKMJ97KedYFGfo.CLkyJOK4Y.4CIKx43ryyU37a', 'siswa', 'XII RPL 4'),
(15, 'asep', 'asep', '$2y$10$Be7L3IsG7Y6QZL1y4gojluDyYra.lbtSPhjUxCEL8PqZ9Fmzvz8IS', 'siswa', 'XII anjaii'),
(16, 'budi', 'budi', '$2y$10$lr9P/hBVy5Guyiop/dHu/.Tsnm.gpgDUMhjb74cAvWxdhBJfM0bWC', 'siswa', 'XII RPL 4');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `tanggapan`
--
ALTER TABLE `tanggapan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengaduan_id` (`pengaduan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `tanggapan`
--
ALTER TABLE `tanggapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pengaduan_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tanggapan`
--
ALTER TABLE `tanggapan`
  ADD CONSTRAINT `tanggapan_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tanggapan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
