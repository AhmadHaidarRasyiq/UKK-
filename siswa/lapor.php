<?php
session_start();
include '../config.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php"); exit();
}

$nis = $_SESSION['nis'];
$nama_siswa = $_SESSION['nama'];

if (isset($_GET['action']) && $_GET['action'] == 'export_excel') {
    $filename = "Histori_Aspirasi_" . $nis . "_" . date('Ymd_His') . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $query_export = mysqli_query($conn, "SELECT a.*, i.lokasi, i.ket, i.tgl_pelaporan, k.ket_kategori, i.id_pelaporan 
                                         FROM Aspirasi a
                                         JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan
                                         JOIN Kategori k ON a.id_kategori = k.id_kategori
                                         WHERE i.nis = '$nis' ORDER BY i.tgl_pelaporan DESC");
    
    echo '<h2>HISTORI ASPIRASI SISWA</h2>';
    echo '<p>Nama: '.$nama_siswa.' | NIS: '.$nis.' | Tanggal: '.date('d-m-Y').'</p>';
    echo '<table border="1">
            <tr style="background:#f5f3ff">
                <th>Kode Laporan</th><th>Tanggal</th><th>Kategori</th><th>Lokasi</th><th>Isi</th><th>Status</th><th>Feedback</th>
            </tr>';
    $no = 1;
    while($row = mysqli_fetch_assoc($query_export)) {
        echo '<tr>
                <td style="text-align:center;">#'.$row['id_pelaporan'].'</td>
                <td>'.date('d/m/Y H:i', strtotime($row['tgl_pelaporan'])).'</td>
                <td>'.$row['ket_kategori'].'</td>
                <td>'.$row['lokasi'].'</td>
                <td>'.$row['ket'].'</td>
                <td>'.$row['status'].'</td>
                <td>'.($row['feedback'] != '-' ? $row['feedback'] : 'Belum ditanggapi').'</td>
              </tr>';
    }
    echo '</table>';
    exit(); 
}

if (isset($_POST['kirim_laporan'])) {
    $id_kat = $_POST['id_kat'];
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $ket    = mysqli_real_escape_string($conn, $_POST['ket']);
    $tgl    = date('Y-m-d H:i:s');
    
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    $foto_baru = "";

    if (!empty($foto)) {
        $foto_baru = time() . '_' . $foto;
        $target_dir = "../assets/img/laporan/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        move_uploaded_file($tmp, $target_dir . $foto_baru);
    }

    $q1 = mysqli_query($conn, "INSERT INTO Input_Aspirasi (nis, lokasi, ket, tgl_pelaporan, foto) VALUES ('$nis', '$lokasi', '$ket', '$tgl', '$foto_baru')");
    
    $id_pelaporan = mysqli_insert_id($conn);
    
    $q2 = mysqli_query($conn, "INSERT INTO Aspirasi (id_pelaporan, status, id_kategori, feedback) VALUES ('$id_pelaporan', 'Menunggu', '$id_kat', '-')");

    if($q1 && $q2) {
        header("Location: $current_page?status=success&code=$id_pelaporan");
    } else {
        header("Location: $current_page?status=error");
    }
    exit();
}

if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    $check = mysqli_query($conn, "SELECT a.status, i.foto FROM Aspirasi a JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan WHERE i.id_pelaporan = '$id_hapus' AND i.nis = '$nis'");
    $data = mysqli_fetch_assoc($check);

    if ($data && $data['status'] == 'Menunggu') {
        if ($data['foto'] != "" && file_exists("../assets/img/laporan/" . $data['foto'])) {
            unlink("../assets/img/laporan/" . $data['foto']);
        }
        mysqli_query($conn, "DELETE FROM Aspirasi WHERE id_pelaporan = '$id_hapus'");
        mysqli_query($conn, "DELETE FROM Input_Aspirasi WHERE id_pelaporan = '$id_hapus'");
        header("Location: $current_page?status=deleted");
        exit();
    }
}

$count_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Input_Aspirasi WHERE nis = '$nis'"))['total'];
$count_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Aspirasi a JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan WHERE i.nis = '$nis' AND a.status = 'Selesai'"))['total'];

$query_histori = "SELECT a.*, i.lokasi, i.ket, i.tgl_pelaporan, i.foto, i.id_pelaporan, k.ket_kategori 
                  FROM Aspirasi a
                  JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan
                  JOIN Kategori k ON a.id_kategori = k.id_kategori
                  WHERE i.nis = '$nis' ORDER BY i.tgl_pelaporan DESC";
$result_histori = mysqli_query($conn, $query_histori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Aspirasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { 
            --primary: #8b5cf6; 
            --primary-hover: #7c3aed;
            --primary-soft: #f5f3ff;
            --secondary: #6366f1;
            --dark: #0f172a; 
            --text-gray: #64748b;
            --bg: #fdfdff; 
            --white: #ffffff;
            --border: #e2e8f0;
            --shadow: 0 10px 30px -5px rgba(139, 92, 246, 0.1);
        }

        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0; transition: all 0.3s ease; }
        
        body { background-color: var(--bg); color: var(--dark); line-height: 1.6; }

        .navbar { 
            background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(15px);
            padding: 0 5%; display: flex; justify-content: space-between; align-items: center; 
            border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 1000; height: 80px;
        }
        .logo { font-weight: 800; font-size: 1.2rem; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .logo i { background: var(--primary-soft); padding: 8px; border-radius: 12px; }
        
        .btn-home { 
            background: var(--primary-soft); color: var(--primary); padding: 10px 20px; text-decoration: none; 
            border-radius: 14px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px; 
        }
        .btn-home:hover { background: var(--primary); color: white; transform: translateY(-2px); }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        .welcome-msg { margin-bottom: 30px; }
        .welcome-msg h1 { font-size: 24px; font-weight: 800; }
        .welcome-msg p { color: var(--text-gray); font-size: 14px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 24px; border: 1px solid var(--border); display: flex; align-items: center; gap: 20px; box-shadow: var(--shadow); }
        .stat-card .icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .stat-info span:first-child { font-size: 28px; font-weight: 800; display: block; line-height: 1; }
        .stat-info span:last-child { color: var(--text-gray); font-size: 13px; font-weight: 500; }

        .card { background: white; border-radius: 30px; border: 1px solid var(--border); box-shadow: var(--shadow); margin-bottom: 40px; overflow: hidden; }
        .card-header { padding: 25px 30px; background: #fafaff; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 30px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 13px; font-weight: 700; color: var(--dark); padding-left: 5px; }
        
        input, select, textarea { 
            width: 100%; padding: 14px 18px; border: 1.5px solid var(--border); border-radius: 16px; 
            background: #f8fafc; outline: none; font-size: 14px; 
        }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px var(--primary-soft); }

        .btn-primary { 
            background: var(--primary); color: white; border: none; padding: 16px 32px; 
            border-radius: 16px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; 
        }
        .btn-primary:hover { background: var(--primary-hover); transform: scale(1.02); box-shadow: 0 10px 20px -10px var(--primary); }

        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { text-align: left; padding: 15px 20px; font-size: 11px; color: var(--text-gray); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
        td { padding: 20px; font-size: 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
        
        .status-badge { padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .status-menunggu { background: #fff7ed; color: #c2410c; }
        .status-selesai { background: #f0fdf4; color: #15803d; }

        .action-btn { 
            width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; 
            border-radius: 12px; text-decoration: none; font-size: 18px;
        }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-edit:hover { background: #0369a1; color: white; }
        .btn-delete { background: #fee2e2; color: #dc2626; margin-left: 5px; }
        .btn-delete:hover { background: #dc2626; color: white; }

        .img-preview { 
            width: 45px; height: 45px; object-fit: cover; border-radius: 12px; 
            border: 2px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); cursor: zoom-in;
        }

        .btn-action-outline { 
            padding: 10px 18px; border-radius: 12px; font-size: 13px; text-decoration: none; 
            display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid var(--border); 
            color: var(--dark); font-weight: 700; background: white;
        }
        .btn-action-outline:hover { border-color: var(--primary); color: var(--primary); }

        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
            .navbar { padding: 0 20px; }
            .navbar .logo span { display: none; }
            .stats-grid { grid-template-columns: 1fr; }
        }
        @media print {
            .navbar, .stats-grid, .form-card-area, .btn-primary, .action-btn, .btn-action-outline { display: none !important; }
            .card { box-shadow: none; border: 1px solid #000; }
            .container { width: 100%; max-width: 100%; margin: 0; }
        }
    </style>
</head>
<body>

<?php if (isset($_GET['status'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($_GET['status'] == 'success'): ?>
                const urlParams = new URLSearchParams(window.location.search);
                const code = urlParams.get('code');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Aspirasi Terkirim!',
                    html: `
                        <p style="margin-bottom:10px;">Terima kasih, laporan Anda telah kami terima.</p>
                        <div style="background:#f3f4f6; padding:10px; border-radius:10px; border:1px dashed #8b5cf6;">
                            <span style="font-size:0.9rem; color:#6b7280;">Kode Laporan Anda:</span><br>
                            <span style="font-size:1.5rem; font-weight:800; color:#8b5cf6;">#${code}</span>
                        </div>
                        <p style="margin-top:10px; font-size:0.85rem; color:#ef4444;">*Simpan kode ini untuk cek status tanpa login.</p>
                    `,
                    confirmButtonText: 'Oke, Saya Simpan',
                    confirmButtonColor: '#8b5cf6',
                    borderRadius: '20px'
                });
            <?php elseif ($_GET['status'] == 'deleted'): ?>
                Swal.fire({
                    icon: 'info',
                    title: 'Laporan Dihapus',
                    text: 'Aspirasi Anda telah berhasil dihapus.',
                    showConfirmButton: false,
                    timer: 2000
                });
            <?php elseif ($_GET['status'] == 'error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan sistem saat mengirim laporan.',
                });
            <?php endif; ?>

            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
<?php endif; ?>

<nav class="navbar">
    <div class="logo">
        <i class="ph-fill ph-chat-centered-dots" style="font-size: 24px;"></i> 
        <span>Dashboard <b>Siswa</b></span>
    </div>
    <a href="../index.php" class="btn-home">
        <i class="ph-bold ph-house"></i> <span>Homepage</span>
    </a>
</nav>

<div class="container">
    <div class="welcome-msg">
        <h1>Halo, <?= explode(' ', $nama_siswa)[0] ?>! 👋</h1>
        <p>Sampaikan aspirasi atau keluhanmu untuk sekolah yang lebih baik.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon" style="background: var(--primary-soft); color: var(--primary);">
                <i class="ph-bold ph-file-text"></i>
            </div>
            <div class="stat-info">
                <span><?= $count_total ?></span>
                <span>Total Laporan</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background: #ecfdf5; color: #10b981;">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <div class="stat-info">
                <span><?= $count_selesai ?></span>
                <span>Laporan Selesai</span>
            </div>
        </div>
    </div>

    <div class="card form-card-area">
        <div class="card-header">
            <h3><i class="ph-bold ph-plus-circle" style="color:var(--primary)"></i> Buat Laporan Baru</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="input-group">
                        <label>Kategori Masalah</label>
                        <select name="id_kat" required>
                            <option value="" disabled selected>Pilih Kategori...</option>
                            <?php
                            $kats = mysqli_query($conn, "SELECT * FROM Kategori");
                            while($k = mysqli_fetch_assoc($kats)) echo "<option value='$k[id_kategori]'>$k[ket_kategori]</option>";
                            ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" placeholder="Misal: Kantin, Lab Komputer..." required>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 20px;">
                    <label>Isi Aspirasi / Keluhan</label>
                    <textarea name="ket" rows="5" placeholder="Tuliskan detail aspirasi Anda di sini secara jelas..." required></textarea>
                </div>
                <div class="input-group" style="margin-bottom: 30px;">
                    <label>Lampiran Foto (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" style="padding: 10px; background: transparent; border: 1px dashed var(--primary);">
                </div>
                <button type="submit" name="kirim_laporan" class="btn-primary">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Kirim Laporan Sekarang
                </button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="ph-bold ph-clock-counter-clockwise" style="color:var(--secondary)"></i> Riwayat Laporan</h3>
            <div style="display:flex; gap:10px;">
                <a href="?action=export_excel" class="btn-action-outline">
                    <i class="ph-bold ph-microsoft-excel-logo"></i> <span>Excel</span>
                </a>
                <button onclick="window.print()" class="btn-action-outline">
                    <i class="ph-bold ph-printer"></i> <span>Cetak PDF</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kode & Tanggal</th>
                            <th>Kategori & Lokasi</th>
                            <th>Detail Laporan</th>
                            <th>Status</th>
                            <th>Feedback Admin</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result_histori) > 0): ?>
                            <?php while($h = mysqli_fetch_assoc($result_histori)): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 800; color: var(--primary);">#<?= $h['id_pelaporan'] ?></span><br>
                                    <small style="color: var(--text-gray); font-weight: 600;"><?= date('d M Y', strtotime($h['tgl_pelaporan'])) ?></small>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark);"><?= $h['ket_kategori'] ?></div>
                                    <div style="font-size: 12px; color: var(--text-gray);"><i class="ph ph-map-pin"></i> <?= $h['lokasi'] ?></div>
                                </td>
                                <td>
                                    <div style="max-width: 200px; font-size: 13px; margin-bottom: 8px;"><?= nl2br(htmlspecialchars($h['ket'])) ?></div>
                                    <?php if(!empty($h['foto'])): ?>
                                        <img src="../assets/img/laporan/<?= $h['foto'] ?>" class="img-preview" onclick="window.open(this.src)">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $st = strtolower($h['status']); ?>
                                    <span class="status-badge status-<?= $st ?>">
                                        <i class="ph-bold <?= $st == 'selesai' ? 'ph-check' : 'ph-hourglass' ?>"></i>
                                        <?= $h['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="background: #f8fafc; padding: 10px; border-radius: 12px; font-size: 12px; border-left: 3px solid #cbd5e1;">
                                        <?= ($h['feedback'] == '-' || empty($h['feedback'])) ? '<span style="color:#94a3b8; font-style:italic;">Menunggu tanggapan...</span>' : $h['feedback'] ?>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <?php if($h['status'] == 'Menunggu'): ?>
                                        <a href="edit_lapor.php?id=<?= $h['id_pelaporan'] ?>" class="action-btn btn-edit" title="Edit">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </a>
                                        <a href="?hapus=<?= $h['id_pelaporan'] ?>" class="action-btn btn-delete" onclick="return confirm('Hapus laporan ini?')" title="Hapus">
                                            <i class="ph-bold ph-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <div class="action-btn" style="background:#f1f5f9; color:#cbd5e1;" title="Laporan sudah diproses">
                                            <i class="ph-fill ph-lock"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 60px;">
                                    <i class="ph-bold ph-folder-open" style="font-size: 48px; color: #e2e8f0;"></i>
                                    <p style="margin-top: 10px; color: #94a3b8;">Kamu belum pernah mengirim aspirasi.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>