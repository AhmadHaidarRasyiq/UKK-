<?php
include '../config.php';

if (isset($_POST['register'])) {
    $nis = mysqli_real_escape_string($conn, $_POST['nis']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $password = md5($_POST['password']); 

    $cek = mysqli_query($conn, "SELECT * FROM Siswa WHERE nis='$nis'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('NIS sudah terdaftar! Silakan login.'); window.location='login.php';</script>";
    } else {
        $query = mysqli_query($conn, "INSERT INTO Siswa (nis, nama, kelas, password) VALUES ('$nis', '$nama', '$kelas', '$password')");
        if ($query) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal mendaftar, coba lagi.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa | E-Aspirasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root { --primary: #4f46e5; --primary-dark: #4338ca; --bg-body: #f1f5f9; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; }
        * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { margin: 0; background-color: var(--bg-body); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        
        .register-card { background: white; width: 100%; max-width: 420px; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; color: var(--text-main); font-weight: 800; font-size: 24px; }
        .header p { margin: 8px 0 0; color: var(--text-muted); font-size: 14px; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #334155; }
        
        .input-wrapper { position: relative; }
        .input-wrapper .icon-left { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px; }
        
        .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px; cursor: pointer; transition: 0.2s; }
        .toggle-password:hover { color: var(--primary); }

        input { width: 100%; padding: 12px 40px 12px 42px; border: 1px solid var(--border); border-radius: 10px; font-size: 14px; outline: none; transition: 0.2s; background: #f8fafc; color: var(--text-main); }
        input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        input:focus + .icon-left { color: var(--primary); }

        .btn-register { width: 100%; background: var(--primary); color: white; padding: 14px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; margin-top: 10px; transition: 0.2s; }
        .btn-register:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .footer { text-align: center; margin-top: 25px; font-size: 13px; color: var(--text-muted); }
        .footer a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="header">
        <h2>Daftar Akun</h2>
        <p>Bergabung untuk menyampaikan aspirasi</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <div class="input-wrapper">
                <i class="ph-bold ph-user icon-left"></i>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
        </div>

        <div class="form-group">
            <label>Nomor Induk Siswa (NIS)</label>
            <div class="input-wrapper">
                <i class="ph-bold ph-identification-card icon-left"></i>
                <input type="number" name="nis" placeholder="Contoh: 12345" required>
            </div>
        </div>

        <div class="form-group">
            <label>Kelas</label>
            <div class="input-wrapper">
                <i class="ph-bold ph-student icon-left"></i>
                <input type="text" name="kelas" placeholder="Contoh: XII RPL 1" required>
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="ph-bold ph-lock-key icon-left"></i>
                <input type="password" name="password" id="passInput" placeholder="Buat password aman" required>
                <i class="ph-bold ph-eye toggle-password" onclick="togglePassword()"></i>
            </div>
        </div>

        <button type="submit" name="register" class="btn-register">Daftar Sekarang</button>
    </form>

    <div class="footer">Sudah punya akun? <a href="../index.php">Login disini</a></div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('passInput');
        const icon = document.querySelector('.toggle-password');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('ph-eye');
            icon.classList.add('ph-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('ph-eye-slash');
            icon.classList.add('ph-eye');
        }
    }
</script>

</body>
</html>