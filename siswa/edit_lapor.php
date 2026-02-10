<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php"); exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$nis = $_SESSION['nis'];

$query = "SELECT i.*, a.status, a.id_kategori 
          FROM Input_Aspirasi i 
          JOIN Aspirasi a ON i.id_pelaporan = a.id_pelaporan 
          WHERE i.id_pelaporan = '$id' AND i.nis = '$nis'";

$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);


if (!$data || $data['status'] != 'Menunggu') {
    header("Location: lapor.php"); exit();
}

if (isset($_POST['update_laporan'])) {
    $id_kat = mysqli_real_escape_string($conn, $_POST['id_kat']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $ket    = mysqli_real_escape_string($conn, $_POST['ket']);
    
    
    $foto_nama = $data['foto']; 
    
    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
        $ekstensi_boleh = array('png', 'jpg', 'jpeg');
        $nama_file_asli = $_FILES['foto']['name'];
        $x = explode('.', $nama_file_asli);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['foto']['tmp_name']; 
        $foto_baru = time() . "_" . $nama_file_asli;
        $target_dir = "../assets/img/laporan/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (in_array($ekstensi, $ekstensi_boleh) === true) {
            if($data['foto'] != "" && file_exists($target_dir . $data['foto'])) {
                unlink($target_dir . $data['foto']);
            }
            
            if (move_uploaded_file($file_tmp, $target_dir . $foto_baru)) {
                $foto_nama = $foto_baru;
            }
        }
    }

    $u1 = mysqli_query($conn, "UPDATE Input_Aspirasi SET lokasi='$lokasi', ket='$ket', foto='$foto_nama' WHERE id_pelaporan='$id'");
    
    $u2 = mysqli_query($conn, "UPDATE Aspirasi SET id_kategori='$id_kat' WHERE id_pelaporan='$id'");

    if ($u1 && $u2) {
        echo "<script>alert('Laporan berhasil diperbarui!'); window.location='lapor.php';</script>";
    } else {
        echo "<script>alert('Gagal update database.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan | Aspirasi Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root { --primary: #8b5cf6; --primary-dark: #7c3aed; --primary-soft: #f5f3ff; --dark: #1e1b4b; --slate-500: #64748b; --border: #e2e8f0; --bg: #f8fafc; }
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: white; width: 100%; max-width: 550px; padding: 40px; border-radius: 30px; box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.1); border: 1px solid rgba(255,255,255,0.7); }
        .header-section { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .icon-box { width: 56px; height: 56px; background: var(--primary-soft); color: var(--primary); border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        h3 { margin: 0; font-weight: 800; color: var(--dark); font-size: 22px; }
        .subtitle { font-size: 14px; color: var(--slate-500); margin-top: 4px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--dark); text-transform: uppercase; letter-spacing: 0.5px; }
        input, select, textarea { width: 100%; padding: 14px 18px; border: 1px solid var(--border); border-radius: 15px; font-size: 14px; outline: none; transition: 0.3s; background: #fcfcfd; color: var(--dark); }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px var(--primary-soft); }
        .file-upload-wrapper { border: 2px dashed var(--border); padding: 15px; border-radius: 15px; display: flex; align-items: center; gap: 15px; background: #f8fafc; transition: 0.3s; }
        .file-upload-wrapper:hover { border-color: var(--primary); background: var(--primary-soft); }
        .preview-img-old { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; }
        .btn-update { width: 100%; background: var(--primary); color: white; border: none; padding: 16px; border-radius: 15px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px; margin-top: 10px; }
        .btn-update:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-back { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: var(--slate-500); font-size: 13px; font-weight: 700; transition: 0.2s; }
        .btn-back:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="card">
    <div class="header-section">
        <div class="icon-box"><i class="ph-fill ph-note-pencil"></i></div>
        <div>
            <h3>Perbarui Laporan</h3>
            <p class="subtitle">Sesuaikan detail aspirasi Anda di bawah ini.</p>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Kategori</label>
            <select name="id_kat" required>
                <?php
                $kats = mysqli_query($conn, "SELECT * FROM Kategori");
                while($k = mysqli_fetch_assoc($kats)):
                    $selected = ($k['id_kategori'] == $data['id_kategori']) ? 'selected' : '';
                    echo "<option value='{$k['id_kategori']}' $selected>{$k['ket_kategori']}</option>";
                endwhile;
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Lokasi Kejadian</label>
            <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi']) ?>" required>
        </div>

        <div class="form-group">
            <label>Detail Laporan</label>
            <textarea name="ket" rows="4" required><?= htmlspecialchars($data['ket']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Bukti Foto (Opsional)</label>
            <div class="file-upload-wrapper">
                <?php if($data['foto'] != ""): ?>
                    <img src="../assets/img/laporan/<?= $data['foto'] ?>" class="preview-img-old" title="Foto Saat Ini">
                <?php else: ?>
                    <div class="icon-box" style="width:40px; height:40px; font-size: 20px; margin:0;"><i class="ph ph-image"></i></div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" style="border:none; padding:0; background:none; margin:0;">
            </div>
            <small style="color: var(--slate-500); font-size: 11px; display: block; margin-top:5px;">*Kosongkan jika tidak ingin mengubah foto.</small>
        </div>

        <button type="submit" name="update_laporan" class="btn-update">
            <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan
        </button>
        <a href="lapor.php" class="btn-back">Batal dan Kembali</a>
    </form>
</div>
</body>
</html>