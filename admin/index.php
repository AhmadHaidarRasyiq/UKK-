<?php
session_start();
include '../config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php"); 
    exit();
}

if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
   
    $cek = mysqli_query($conn, "SELECT id_pelaporan FROM Aspirasi WHERE id_aspirasi = '$id_hapus'");
    $data = mysqli_fetch_assoc($cek);
    
    if($data) {
        $id_p = $data['id_pelaporan'];
        
        $foto = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM Input_Aspirasi WHERE id_pelaporan='$id_p'"));
        if($foto['foto'] && file_exists('../assets/img/laporan/'.$foto['foto'])) {
            unlink('../assets/img/laporan/'.$foto['foto']);
        }
        

        mysqli_query($conn, "DELETE FROM Aspirasi WHERE id_aspirasi = '$id_hapus'");
        mysqli_query($conn, "DELETE FROM Input_Aspirasi WHERE id_pelaporan = '$id_p'");
    }
    header("Location: index.php?status=deleted"); 
    exit();
}

$filter_tgl      = $_GET['tgl'] ?? '';
$filter_bulan    = $_GET['bulan'] ?? ''; 
$filter_kategori = $_GET['kategori'] ?? '';
$filter_siswa    = $_GET['siswa'] ?? '';
$search_query    = $_GET['q'] ?? ''; 

$where_clauses = [];


if (!empty($filter_tgl))      $where_clauses[] = "DATE(i.tgl_pelaporan) = '$filter_tgl'";
if (!empty($filter_bulan))    $where_clauses[] = "DATE_FORMAT(i.tgl_pelaporan, '%Y-%m') = '$filter_bulan'";
if (!empty($filter_kategori)) $where_clauses[] = "a.id_kategori = '$filter_kategori'";
if (!empty($filter_siswa))    $where_clauses[] = "i.nis = '$filter_siswa'";

if (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(s.nama LIKE '%$search_query%' OR s.nis LIKE '%$search_query%')";
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$sql_base = "SELECT a.*, i.tgl_pelaporan, i.lokasi, i.ket, i.foto, 
                    s.nama as nama_siswa, s.nis, 
                    k.ket_kategori 
             FROM Aspirasi a 
             JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan 
             LEFT JOIN Siswa s ON i.nis = s.nis 
             LEFT JOIN Kategori k ON a.id_kategori = k.id_kategori 
             $where_sql
             ORDER BY i.tgl_pelaporan DESC";


if (isset($_GET['action']) && $_GET['action'] == 'export_excel') {
    $filename = "Rekap_Aspirasi_" . date('Ymd_His') . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $query_export = mysqli_query($conn, $sql_base);
    ?>
    <h3 style="text-align:center;">REKAPITULASI ASPIRASI SISWA</h3>
    <table border="1">
        <thead>
            <tr style="background-color:#8b5cf6; color:white;">
                <th>No</th><th>Kode Unik</th><th>Tanggal</th><th>NIS</th><th>Nama Siswa</th>
                <th>Kategori</th><th>Lokasi</th><th>Isi Aspirasi</th>
                <th>Status</th><th>Tanggapan Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = mysqli_fetch_assoc($query_export)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>#<?= $row['id_aspirasi'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tgl_pelaporan'])) ?></td>
                <td><?= $row['nis'] ?? '-' ?></td>
                <td><?= $row['nama_siswa'] ?? 'Umum' ?></td>
                <td><?= $row['ket_kategori'] ?></td>
                <td><?= $row['lokasi'] ?></td>
                <td><?= $row['ket'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= ($row['feedback'] != '-' ? $row['feedback'] : 'Belum ditanggapi') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    exit(); 
}

if (isset($_GET['export']) && $_GET['export'] == 'pdf'):
    $query_pdf = mysqli_query($conn, $sql_base);
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Cetak Laporan Aspirasi</title>
        <style>
            body { font-family: sans-serif; font-size: 12px; color: #333; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { border: 1px solid #999; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background-color: #f3f4f6; font-weight: bold; }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <h2>Laporan Data Aspirasi Siswa</h2>
            <p>Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th><th width="8%">ID</th><th width="12%">Tanggal</th>
                    <th width="15%">Pelapor</th><th width="12%">Kategori</th>
                    <th width="22%">Detail</th><th width="10%">Status</th><th width="16%">Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php $n=1; while($row = mysqli_fetch_assoc($query_pdf)): ?>
                <tr>
                    <td align="center"><?= $n++ ?></td>
                    <td><b>#<?= $row['id_aspirasi'] ?></b></td>
                    <td><?= date('d/m/Y', strtotime($row['tgl_pelaporan'])) ?></td>
                    <td><?= $row['nama_siswa'] ?? 'Anonim' ?></td>
                    <td><?= $row['ket_kategori'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['ket'])) ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= ($row['feedback'] != '-' ? $row['feedback'] : '-') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>
    </html>
<?php 
    exit(); 
endif;

$result = mysqli_query($conn, $sql_base);

$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM Aspirasi"))['t'];
$menunggu    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM Aspirasi WHERE status='Menunggu'"))['t'];
$selesai     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM Aspirasi WHERE status='Selesai'"))['t'];

$list_kategori = mysqli_query($conn, "SELECT * FROM Kategori");
$list_siswa    = mysqli_query($conn, "SELECT nis, nama FROM Siswa ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Aspirasi Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root { --primary: #8b5cf6; --bg: #f8fafc; --text: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding-bottom: 50px; }
        
        .navbar { background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10; }
        .logo { color: var(--primary); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .nav-btn { text-decoration: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 6px; transition: .2s; }
        
        .btn-excel { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .btn-pdf { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .btn-home { background: #f1f5f9; color: #475569; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .header-sect { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .header-sect h1 { margin: 0; font-size: 1.8rem; }
        
        /* Stats Cards */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card h3 { margin: 0; font-size: 0.9rem; color: #64748b; text-transform: uppercase; }
        .card .num { font-size: 2.5rem; font-weight: 800; margin: 10px 0 0; color: var(--text); }
        
        /* Search Box (New) */
        .search-container { position: relative; width: 300px; }
        .search-input { width: 100%; padding: 10px 15px; padding-left: 35px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-family: inherit; }
        .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        .filter-box { background: white; padding: 20px; border-radius: 16px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 5px; text-transform: uppercase; }
        .form-control { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-reset { background: #f1f5f9; color: #475569; text-align: center; display: block; padding: 11px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }

        .table-wrap { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        th { text-align: left; padding: 15px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }
        
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .st-menunggu { background: #fff7ed; color: #c2410c; }
        .st-proses { background: #eff6ff; color: #1d4ed8; }
        .st-selesai { background: #f0fdf4; color: #15803d; }
        .kode-unik { background: #f3e8ff; color: #6d28d9; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: bold; font-size: 13px; }
        .action-btns a { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 8px; margin-right: 5px; text-decoration: none; transition: .2s; }
        .btn-reply { background: #f3e8ff; color: #7c3aed; }
        .btn-del { background: #ffe4e6; color: #e11d48; }
        .foto-thumb { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><i class="ph-fill ph-chat-teardrop-text"></i> Dashboard Admin</div>
        <div style="display:flex; gap:10px;">
            <a href="?action=export_excel&q=<?= $search_query ?>&tgl=<?= $filter_tgl ?>&bulan=<?= $filter_bulan ?>&kategori=<?= $filter_kategori ?>&siswa=<?= $filter_siswa ?>" class="nav-btn btn-excel"><i class="ph-bold ph-file-xls"></i> Excel</a>
            <a href="?export=pdf&q=<?= $search_query ?>&tgl=<?= $filter_tgl ?>&bulan=<?= $filter_bulan ?>&kategori=<?= $filter_kategori ?>&siswa=<?= $filter_siswa ?>" target="_blank" class="nav-btn btn-pdf"><i class="ph-bold ph-printer"></i> PDF</a>
            <a href="../index.php" class="nav-btn btn-home"><i class="ph-bold ph-house"></i> Home</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="header-sect">
            <div>
                <h1>Dashboard Overview</h1>
                <p style="margin:5px 0 0; color:#64748b;">Manajemen data laporan dan aspirasi siswa.</p>
            </div>
            
            <form action="" method="GET" class="search-container">
                <input type="hidden" name="tgl" value="<?= $filter_tgl ?>">
                <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                <input type="hidden" name="kategori" value="<?= $filter_kategori ?>">
                <input type="hidden" name="siswa" value="<?= $filter_siswa ?>">
                
                <i class="ph-bold ph-magnifying-glass search-icon"></i>
                <input type="text" name="q" class="search-input" placeholder="Cari nama siswa atau NIS..." value="<?= htmlspecialchars($search_query) ?>">
            </form>
        </div>

        <div class="stats">
            <div class="card" style="border-left: 4px solid #8b5cf6;"><h3>Total Laporan</h3><div class="num"><?= $total_masuk ?></div></div>
            <div class="card" style="border-left: 4px solid #f97316;"><h3>Menunggu</h3><div class="num"><?= $menunggu ?></div></div>
            <div class="card" style="border-left: 4px solid #22c55e;"><h3>Selesai</h3><div class="num"><?= $selesai ?></div></div>
        </div>

        <div class="filter-box">
            <form action="" method="GET">
                <input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>">
                
                <div class="filter-grid">
                    <div class="form-group"><label>Tanggal</label><input type="date" name="tgl" value="<?= $filter_tgl ?>" class="form-control"></div>
                    <div class="form-group"><label>Bulan</label><input type="month" name="bulan" value="<?= $filter_bulan ?>" class="form-control"></div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control">
                            <option value="">Semua</option>
                            <?php mysqli_data_seek($list_kategori, 0); while($k = mysqli_fetch_assoc($list_kategori)): ?>
                                <option value="<?= $k['id_kategori'] ?>" <?= $filter_kategori == $k['id_kategori'] ? 'selected' : '' ?>><?= $k['ket_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <select name="siswa" class="form-control">
                            <option value="">Semua Siswa</option>
                            <?php mysqli_data_seek($list_siswa, 0); while($s = mysqli_fetch_assoc($list_siswa)): ?>
                                <option value="<?= $s['nis'] ?>" <?= $filter_siswa == $s['nis'] ? 'selected' : '' ?>><?= $s['nama'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div><button type="submit" class="btn-submit"><i class="ph-bold ph-funnel"></i> Terapkan Filter</button></div>
                    <?php if(!empty($filter_tgl) || !empty($filter_bulan) || !empty($filter_kategori) || !empty($filter_siswa) || !empty($search_query)): ?>
                        <div><a href="index.php" class="btn-reset">Reset</a></div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th> <th>Pelapor</th>
                        <th>Kategori</th> <th>Isi Aspirasi</th>
                        <th>Foto</th> <th>Status</th> <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php mysqli_data_seek($result, 0); while($d = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="kode-unik">#<?= $d['id_aspirasi'] ?></span></td>
                            <td>
                                <b><?= htmlspecialchars($d['nama_siswa'] ?? 'Umum') ?></b><br>
                                <small style="color:#64748b"><?= date('d/m/Y', strtotime($d['tgl_pelaporan'])) ?></small>
                            </td>
                            <td>
                                <span style="color:var(--primary); font-weight:600;"><?= $d['ket_kategori'] ?></span><br>
                                <small>📍 <?= $d['lokasi'] ?></small>
                            </td>
                            <td style="max-width: 250px; color:#475569;">
                                <?= substr($d['ket'], 0, 60) . (strlen($d['ket']) > 60 ? '...' : '') ?>
                            </td>
                            <td>
                                <?php if($d['foto']): ?>
                                    <a href="../assets/img/laporan/<?= $d['foto'] ?>" target="_blank">
                                        <img src="../assets/img/laporan/<?= $d['foto'] ?>" class="foto-thumb">
                                    </a>
                                <?php else: ?>
                                    <i class="ph-fill ph-image-slash" style="color:#cbd5e1; font-size:20px;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $st = strtolower($d['status']);
                                    $cls = ($st == 'selesai') ? 'st-selesai' : (($st == 'proses') ? 'st-proses' : 'st-menunggu');
                                ?>
                                <span class="status-badge <?= $cls ?>"><?= $d['status'] ?></span>
                            </td>
                            <td class="action-btns">
                                <a href="tanggapi.php?id=<?= $d['id_aspirasi'] ?>" class="btn-reply" title="Tanggapi"><i class="ph-bold ph-chat-centered-text"></i></a>
                                <a href="?hapus=<?= $d['id_aspirasi'] ?>" class="btn-del" onclick="return confirm('Yakin hapus data ini?')" title="Hapus"><i class="ph-bold ph-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>