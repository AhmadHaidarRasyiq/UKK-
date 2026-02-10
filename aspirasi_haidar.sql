-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Feb 2026 pada 10.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aspirasi_haidar`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `Username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`Username`, `password`) VALUES
('admin', 'admin123'),
('admin1', 'admin11'),
('admin2', '1234'),
('admin3', 'admin'),
('adminn', '1234'),
('Haidar', 'haidar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int(5) NOT NULL,
  `status` enum('Menunggu','Proses','Selesai') DEFAULT 'Menunggu',
  `id_kategori` int(5) DEFAULT NULL,
  `feedback` varchar(255) DEFAULT NULL,
  `id_pelaporan` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `status`, `id_kategori`, `feedback`, `id_pelaporan`) VALUES
(26, 'Selesai', 1, 'sea', 30),
(27, 'Menunggu', 1, '-', 31),
(30, 'Selesai', 1, 'bagus', 34),
(31, 'Proses', 1, 'OTW', 35),
(34, 'Proses', 2, '-ya ya', 38),
(35, 'Menunggu', 3, '-', 39);

-- --------------------------------------------------------

--
-- Struktur dari tabel `input_aspirasi`
--

CREATE TABLE `input_aspirasi` (
  `id_pelaporan` int(5) NOT NULL,
  `nis` int(10) DEFAULT NULL,
  `id_kategori` int(5) DEFAULT NULL,
  `lokasi` varchar(50) DEFAULT NULL,
  `ket` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tgl_pelaporan` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `input_aspirasi`
--

INSERT INTO `input_aspirasi` (`id_pelaporan`, `nis`, `id_kategori`, `lokasi`, `ket`, `foto`, `tgl_pelaporan`, `nama`) VALUES
(30, 1107, NULL, 'Lab RPL A', 'a', '1770344151_component not defined.png', '2026-02-05 20:15:51', ''),
(31, 1107, NULL, 'Lab RPL A', 'TES', '1770345077_abidin1.png', '2026-02-05 20:31:17', ''),
(34, 3117, NULL, 'Lab RPL A', 'haha', '1770349531_images.jpg', '2026-02-05 21:45:31', ''),
(35, 3117, NULL, 'Lab RPL B', 'wrbr', '1770358372_images (1).jpg', '2026-02-06 00:12:52', ''),
(38, 3117, NULL, 'Lab RPL B', 'yoi', '1770550182_images.jpg', '2026-02-08 05:29:42', ''),
(39, 3117, NULL, 'C 2.1', 'Belum piket', '1770618148_Diagram Tanpa Judul.drawio (1).png', '2026-02-09 00:22:28', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(5) NOT NULL,
  `ket_kategori` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `ket_kategori`) VALUES
(1, 'Sarana'),
(2, 'Prasarana'),
(3, 'Kendala');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `nis` int(10) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `kelas` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`nis`, `password`, `nama`, `kelas`) VALUES
(101, 'haidar123', 'DAMA', NULL),
(1107, 'haidar', 'Ahmad Haidar Rasyiq', NULL),
(1234, '1234', 'Farrel', 'X'),
(3117, '3117', 'Arda Guler', 'XII PPLG 2'),
(3212, 'Haidarr', 'Haidarr', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Username`);

--
-- Indeks untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_pelaporan` (`id_pelaporan`);

--
-- Indeks untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `nis` (`nis`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`),
  ADD KEY `nis` (`nis`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  MODIFY `id_pelaporan` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD CONSTRAINT `aspirasi_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`),
  ADD CONSTRAINT `aspirasi_ibfk_2` FOREIGN KEY (`id_pelaporan`) REFERENCES `input_aspirasi` (`id_pelaporan`);

--
-- Ketidakleluasaan untuk tabel `input_aspirasi`
--
ALTER TABLE `input_aspirasi`
  ADD CONSTRAINT `input_aspirasi_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`),
  ADD CONSTRAINT `input_aspirasi_ibfk_3` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE SET NULL ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
