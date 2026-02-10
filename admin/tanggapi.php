<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php"); exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php"); exit();
}

$id_aspirasi = mysqli_real_escape_string($conn, $_GET['id']);

if (isset($_POST['simpan_tanggapan'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    $update = mysqli_query($conn, "UPDATE Aspirasi SET status='$status', feedback='$feedback' WHERE id_aspirasi='$id_aspirasi'");
    
    if ($update) {
        echo "<script>
                alert('Tanggapan berhasil disimpan!'); 
                window.location='index.php';
              </script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($conn) . "');</script>";
    }
}

$query = "SELECT a.*, i.lokasi, i.ket, i.tgl_pelaporan, i.foto, i.nis, s.nama as nama_siswa, k.ket_kategori 
          FROM Aspirasi a 
          JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan 
          LEFT JOIN Siswa s ON i.nis = s.nis
          LEFT JOIN Kategori k ON a.id_kategori = k.id_kategori 
          WHERE a.id_aspirasi = '$id_aspirasi'";

$exec = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($exec);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggapi Aspirasi | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root { 
            --primary: #7c3aed; 
            --primary-hover: #6d28d9;
            --bg: #f9fafb; 
            --white: #ffffff; 
            --border: #e5e7eb; 
            --text-dark: #1f2937;
            --text-gray: #6b7280;
        }
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-dark); padding: 40px 20px; margin: 0; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 25px; }
        .card { background: var(--white); border-radius: 24px; border: 1px solid var(--border); padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .badge { padding: 6px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-proses { background: #dbeafe; color: #1e40af; }
        .status-selesai { background: #d1fae5; color: #065f46; }

        .info-label { font-size: 0.7rem; color: var(--text-gray); text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin-bottom: 5px; }
        .info-value { font-weight: 600; margin-bottom: 20px; color: var(--text-dark); }
        
        textarea, select { 
            width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--border); 
            margin-top: 8px; font-family: inherit; background: #fcfcfc; outline: none;
        }
        textarea:focus, select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
        
        .btn-submit { 
            width: 100%; background: var(--primary); color: white; border: none; 
            padding: 16px; border-radius: 14px; font-weight: 700; cursor: pointer; 
            margin-top: 25px; font-size: 1rem; transition: 0.2s;
        }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-2px); }
        
        .img-container { margin-top: 20px; border-radius: 15px; overflow: hidden; border: 1px solid var(--border); }
        
        @media (max-width: 850px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:35px;">
        <div>
            <h2 style="margin:0; font-size:1.8rem;">Tanggapi Aspirasi</h2>
            <p style="margin:5px 0 0 0; color:var(--text-gray);">Kelola status dan berikan umpan balik kepada siswa.</p>
        </div>
        <a href="index.php" style="text-decoration:none; color:var(--text-dark); font-weight:700; display:flex; align-items:center; gap:8px;">
            <i class="ph-bold ph-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="grid">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px;">
                <h3 style="margin:0;">Detail Aduan</h3>
                <?php 
                    $st_class = strtolower($data['status']); 
                    echo "<span class='badge status-$st_class'>{$data['status']}</span>";
                ?>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr;">
                <div>
                    <div class="info-label">ID Pelaporan</div>
                    <div class="info-value">#<?= $data['id_pelaporan'] ?></div>
                </div>
                <div>
                    <div class="info-label">Tanggal Masuk</div>
                    <div class="info-value"><?= date('d M Y, H:i', strtotime($data['tgl_pelaporan'])) ?></div>
                </div>
            </div>
            
            <div class="info-label">Nama Pelapor / NIS</div>
            <div class="info-value"><?= htmlspecialchars($data['nama_siswa'] ?? 'Siswa') ?> (<?= $data['nis'] ?>)</div>
            
            <div class="info-label">Kategori & Lokasi</div>
            <div class="info-value"><?= $data['ket_kategori'] ?> — <span style="color:var(--primary)"><?= $data['lokasi'] ?></span></div>
            
            <div class="info-label">Isi Aspirasi</div>
            <div style="background:#f3f4f6; padding:20px; border-radius:16px; font-size:0.95rem; line-height:1.6; color:#374151; border: 1px solid #e5e7eb;">
                <?= nl2br(htmlspecialchars($data['ket'])) ?>
            </div>

            <?php if(!empty($data['foto'])): ?>
                <div style="margin-top:25px;">
                    <div class="info-label">Lampiran Bukti Foto</div>
                    <div class="img-container">
                        <img src="../assets/img/laporan/<?= $data['foto'] ?>" style="width:100%; display:block; cursor:pointer;" onclick="window.open(this.src)">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="height: fit-content; position: sticky; top: 40px;">
            <h3 style="margin:0 0 25px 0;">Beri Respon</h3>
            <form method="POST">
                <div>
                    <label class="info-label">Update Status Progres</label>
                    <select name="status" required>
                        <option value="Menunggu" <?= $data['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Proses" <?= $data['status'] == 'Proses' ? 'selected' : '' ?>>Proses (Sedang Ditangani)</option>
                        <option value="Selesai" <?= $data['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai (Tuntas)</option>
                    </select>
                </div>

                <div style="margin-top:25px;">
                    <label class="info-label">Pesan Balasan Ke Siswa</label>
                    <textarea name="feedback" rows="8" placeholder="Contoh: Terima kasih, aduan sudah kami sampaikan ke sarana prasarana dan akan segera diperbaiki..." required><?= htmlspecialchars($data['feedback'] != '-' ? $data['feedback'] : '') ?></textarea>
                </div>

                <button type="submit" name="simpan_tanggapan" class="btn-submit">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan & Kirim Update
                </button>
            </form>
            
            <p style="font-size: 0.75rem; color: var(--text-gray); margin-top: 15px; text-align: center;">
                <i class="ph ph-info"></i> Perubahan status akan langsung terlihat di dashboard siswa.
            </p>
        </div>
    </div>
</div>
</body>
</html>