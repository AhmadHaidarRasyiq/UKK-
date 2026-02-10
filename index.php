<?php
session_start();
include 'config.php';

$nama_tampil = "";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $nama_tampil = !empty($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
    } else {
        $nama_tampil = !empty($_SESSION['nama']) ? $_SESSION['nama'] : 'Siswa';
    }
}

$error = "";
$success = "";
$search_result = null;

if (isset($_GET['cari_kode'])) {
    $kode_unik = mysqli_real_escape_string($conn, $_GET['cari_kode']);
    
    $query_cek = "SELECT a.status, a.feedback, i.tgl_pelaporan, k.ket_kategori 
                  FROM Aspirasi a 
                  JOIN Input_Aspirasi i ON a.id_pelaporan = i.id_pelaporan 
                  LEFT JOIN Kategori k ON a.id_kategori = k.id_kategori
                  WHERE a.id_pelaporan = '$kode_unik' 
                  ORDER BY a.id_aspirasi DESC 
                  LIMIT 1"; 
    
    $res_cek = mysqli_query($conn, $query_cek);
    
    if (mysqli_num_rows($res_cek) > 0) {
        $search_result = mysqli_fetch_assoc($res_cek);
    } else {
        $error = "Laporan dengan ID #$kode_unik tidak ditemukan.";
    }
}

if (isset($_POST['login_siswa'])) {
    $nis = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);
    
    $query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$nis'");
    
    if (mysqli_num_rows($query_siswa) > 0) {
        $row = mysqli_fetch_assoc($query_siswa);
        if ($pass == $row['password']) {
            $_SESSION['nis'] = $row['nis'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = 'siswa';
            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "NIS tidak terdaftar!";
    }
}

if (isset($_POST['register_siswa'])) {
    $nis_reg  = mysqli_real_escape_string($conn, $_POST['nis_reg']);
    $nama_reg = mysqli_real_escape_string($conn, $_POST['nama_reg']);
    $pass_reg = mysqli_real_escape_string($conn, $_POST['pass_reg']);

    $cek_nis = mysqli_query($conn, "SELECT nis FROM siswa WHERE nis = '$nis_reg'");
    
    if (mysqli_num_rows($cek_nis) > 0) {
        $error = "NIS sudah terdaftar! Silakan login.";
    } else {
        $query_reg = mysqli_query($conn, "INSERT INTO siswa (nis, password, nama) VALUES ('$nis_reg', '$pass_reg', '$nama_reg')");
        if ($query_reg) {
            $success = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Gagal mendaftar, coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Aspirasi | SMK Negeri 12 Malang</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #8b5cf6;
            --primary-light: #ddd6fe;
            --primary-dark: #6d28d9;
            --accent: #c084fc;
            --dark: #1e1b4b;
            --slate: #64748b;
            --bg: #f5f3ff;
            --white: #ffffff;
            --card-border: rgba(232, 226, 240, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: var(--bg);
            color: var(--dark);
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(139, 92, 246, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(192, 132, 252, 0.08) 0%, transparent 40%);
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

        
        nav {
            height: 80px; background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--card-border);
            position: sticky; top: 0; z-index: 1000; display: flex; align-items: center;
        }
        .nav-content { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .logo { font-weight: 800; font-size: 1.4rem; color: var(--dark); display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .logo i { color: var(--primary); font-size: 1.8rem; }
        .logo span { color: var(--primary); }

        .hero {
            min-height: calc(100vh - 80px);
            display: flex; align-items: center; justify-content: space-between;
            gap: 4rem; padding: 4rem 0;
        }
        .hero-content { flex: 1; max-width: 650px; }
        .hero-image { flex: 0 0 420px; width: 420px; }

        @media (max-width: 992px) {
            .hero { flex-direction: column; text-align: center; justify-content: center; gap: 3rem; }
            .hero-content { margin: 0 auto; }
            .hero-image { width: 100%; max-width: 450px; }
            .hero-features { justify-content: center; }
        }

        .hero-content h1 {
            font-size: clamp(2.5rem, 5vw, 3.8rem);
            line-height: 1.15; font-weight: 800; margin-bottom: 1.5rem;
            letter-spacing: -1.5px; color: var(--dark);
        }
        .hero-content h1 span {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-content p { font-size: 1.1rem; color: var(--slate); margin-bottom: 2.5rem; line-height: 1.6; }

        .hero-features { display: flex; gap: 2.5rem; margin-top: 1rem; }
        .hero-features div h3 { font-weight: 800; font-size: 1.4rem; color: var(--primary); margin-bottom: 0.2rem; }
        .hero-features div p { font-size: 0.75rem; font-weight: 700; color: var(--slate); margin: 0; letter-spacing: 0.5px; }

        .login-card {
            background: var(--white);
            padding: 2.5rem; border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.15);
            border: 1px solid var(--card-border);
            position: relative; overflow: hidden;
        }
        .form-group { margin-bottom: 1.2rem; text-align: left; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 700; font-size: 0.8rem; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control {
            width: 100%; padding: 0.9rem 1.2rem; border-radius: 1rem;
            border: 2px solid #f1f0fb; background: #faf9ff;
            transition: all 0.3s ease; font-size: 0.95rem; color: var(--dark);
        }
        .form-control:focus { border-color: var(--primary); background: white; outline: none; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }

        .btn {
            padding: 0.9rem 1.5rem; border-radius: 1rem; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; cursor: pointer; transition: all 0.3s ease;
            text-decoration: none; border: none; font-size: 0.95rem;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; width: 100%; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.25); }
        .btn-outline { background: white; color: var(--primary-dark); border: 2px solid var(--primary-light); }
        .btn-outline:hover { border-color: var(--primary); background: #fdfcff; }

        .search-box {
            position: relative;
            max-width: 450px;
            margin-bottom: 2rem;
        }
        .search-box input {
            width: 100%;
            padding: 1rem 1.2rem;
            padding-right: 4rem; 
            border-radius: 50px;
            border: 2px solid var(--primary-light);
            font-size: 1rem;
            box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.1);
            outline: none;
            transition: 0.3s;
        }
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 10px 30px -5px rgba(139, 92, 246, 0.2);
        }
        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            bottom: 5px;
            border-radius: 50px;
            padding: 0 1.2rem;
            border: none;
            background: var(--primary);
            color: white;
            cursor: pointer;
            transition: 0.2s;
        }
        .search-box button:hover {
            background: var(--primary-dark);
        }

        .procedure { padding: 6rem 0; background: white; border-top: 1px solid var(--card-border); }
        .grid-procedure { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3.5rem; }
        @media (max-width: 768px) { .grid-procedure { grid-template-columns: 1fr; } }

        .card-step {
            padding: 2rem; border-radius: 1.8rem; background: var(--bg);
            border: 1px solid var(--card-border); transition: all 0.3s ease;
            text-align: left; height: 100%;
        }
        .card-step:hover { transform: translateY(-8px); border-color: var(--primary); background: white; box-shadow: 0 20px 40px -10px rgba(139, 92, 246, 0.1); }
        .icon-circle {
            width: 55px; height: 55px; border-radius: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; margin-bottom: 1.2rem;
        }

        .hidden { display: none; opacity: 0; }
        .toggle-link { color: var(--primary); font-weight: 800; cursor: pointer; text-decoration: underline; }
        .text-center-desktop { text-align: center; }
        .mx-auto { margin-left: auto; margin-right: auto; }

        footer { background: var(--dark); color: #a5b4fc; padding: 3rem 0; text-align: center; }
    </style>
</head>
<body>

    <nav>
        <div class="container nav-content">
            <a href="#" class="logo">
                <i class="ph-fill ph-chat-circle-dots"></i> Homepage<span>Aspirasi</span>
            </a>
            
            <?php if(isset($_SESSION['role'])): ?>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="text-align: right;" class="hidden-mobile">
                        <small style="color: var(--slate); font-weight: 600; font-size: 0.75rem;">Halo,</small>
                        <div style="font-weight: 800; font-size: 0.9rem; color: var(--primary-dark);"><?= htmlspecialchars($nama_tampil) ?></div>
                    </div>
                    <button onclick="konfirmasiLogout()" class="btn btn-outline" style="padding: 0.6rem; border-color: #fee2e2; color: #ef4444; width: auto;">
                        <i class="ph-bold ph-sign-out" style="font-size: 1.2rem;"></i>
                    </button>
                </div>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; width: auto;">Login Admin</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <section class="hero">
            <div class="hero-content <?= isset($_SESSION['role']) ? 'text-center-desktop mx-auto' : ''; ?>" data-aos="fade-up">
                
                <?php if(isset($_SESSION['role'])): ?>
                    <div style="display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; color: #16a34a; padding: 6px 16px; border-radius: 100px; margin-bottom: 1.5rem; font-size: 0.8rem; font-weight: 800; border: 1px solid #dcfce7;">
                        <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></span>
                        AKUN AKTIF
                    </div>
                    <h1>Selamat Datang, <br><span><?= htmlspecialchars($nama_tampil) ?></span></h1>
                    <p class="mx-auto">Aspirasi Anda sangat berharga. Mari bersama-sama membangun lingkungan sekolah yang lebih baik.</p>
                    <div style="margin-top: 2rem;">
                         <a href="<?= ($_SESSION['role'] == 'admin') ? 'admin/index.php' : 'siswa/lapor.php' ?>" class="btn btn-primary" style="width: auto; padding: 1rem 2.5rem; font-size: 1.1rem;">
                            <i class="ph-bold ph-paper-plane-right"></i> Ke Dashboard
                        </a>
                    </div>

                <?php else: ?>
                    <div style="display: inline-block; background: #eef2ff; color: var(--primary); padding: 6px 16px; border-radius: 100px; margin-bottom: 1.5rem; font-weight: 800; font-size: 0.8rem; border: 1px solid var(--primary-light);">
                        SMKN 12 MALANG
                    </div>
                    <h1>Suarakan Aspirasi <br> <span>Anda.</span></h1>
                    <p>Sampaikan kritik, saran, atau laporan fasilitas sekolah dengan aman dan transparan.</p>
                    
                    <form action="" method="GET" class="search-box">
                        <input type="text" name="cari_kode" placeholder="Punya ID Laporan? Cek status di sini..." required>
                        <button type="submit"><i class="ph-bold ph-magnifying-glass"></i> Cek</button>
                    </form>
                    
                    <div class="hero-features">
                        <div><h3>Aman</h3><p>TERENKRIPSI</p></div>
                        <div><h3>Cepat</h3><p>TANGGAPAN</p></div>
                        <div><h3>24/7</h3><p>ONLINE</p></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if(!isset($_SESSION['role'])): ?>
            <div class="hero-image" data-aos="fade-left">
                <div class="login-card">
                    <div id="form-login">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-weight: 800; color: var(--dark);">Login Siswa</h2>
                            <p style="color: var(--slate); font-size: 0.9rem;">Masuk menggunakan NIS Anda.</p>
                        </div>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Nomor Induk Siswa</label>
                                <input type="text" name="user" class="form-control" placeholder="masukkan NIS" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <div style="position: relative;">
                                    <input type="password" name="pass" id="passLogin" class="form-control" placeholder="masukkan password" required>
                                    <i class="ph ph-eye" onclick="togglePass('passLogin', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--slate);"></i>
                                </div>
                            </div>
                            <button type="submit" name="login_siswa" class="btn btn-primary">Masuk Sekarang</button>
                        </form>
                        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--slate);">
                            Belum ada akun? <span class="toggle-link" onclick="switchForm('register')">Daftar</span>
                        </p>
                    </div>

                    <div id="form-register" class="hidden">
                        <div style="margin-bottom: 1.5rem;">
                            <h2 style="font-weight: 800;">Registrasi</h2>
                            <p style="color: var(--slate); font-size: 0.9rem;">Buat akun baru untuk melapor.</p>
                        </div>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>NIS</label>
                                <input type="text" name="nis_reg" class="form-control" placeholder="Masukkan NIS" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_reg" class="form-control" placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="pass_reg" id="passReg" class="form-control" placeholder="Buat password" required>
                            </div>
                            <button type="submit" name="register_siswa" class="btn btn-primary">Buat Akun</button>
                        </form>
                        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--slate);">
                            Sudah punya akun? <span class="toggle-link" onclick="switchForm('login')">Login</span>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>  

    <section id="cara" class="procedure">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto;">
                <h2 style="font-size: 2rem; font-weight: 800; color: var(--dark);">Alur Pelaporan</h2>
                <p style="color: var(--slate); margin-top: 0.5rem;">Laporkan masalah Anda dengan mengikuti 3 langkah mudah berikut ini.</p>
            </div>
            
            <div class="grid-procedure">
                <div class="card-step" data-aos="fade-up" data-aos-delay="100">
                    <div class="icon-circle" style="background: #e0e7ff; color: #4338ca;"><i class="ph-bold ph-user-plus"></i></div>
                    <h3 style="margin-bottom: 0.8rem; font-weight: 800; font-size: 1.2rem;">1. Registrasi / Login</h3>
                    <p style="color: var(--slate); font-size: 0.95rem; line-height: 1.6;">Masuk menggunakan NIS yang terdaftar. Jika belum punya akun, silakan daftar terlebih dahulu.</p>
                </div>
                <div class="card-step" data-aos="fade-up" data-aos-delay="200">
                    <div class="icon-circle" style="background: #fce7f3; color: #be185d;"><i class="ph-bold ph-note-pencil"></i></div>
                    <h3 style="margin-bottom: 0.8rem; font-weight: 800; font-size: 1.2rem;">2. Tulis Aspirasi</h3>
                    <p style="color: var(--slate); font-size: 0.95rem; line-height: 1.6;">Klik tombol "Tulis Laporan", lampirkan foto bukti jika ada, dan deskripsikan masalah secara jelas.</p>
                </div>
                <div class="card-step" data-aos="fade-up" data-aos-delay="300">
                    <div class="icon-circle" style="background: #dcfce7; color: #15803d;"><i class="ph-bold ph-shield-check"></i></div>
                    <h3 style="margin-bottom: 0.8rem; font-weight: 800; font-size: 1.2rem;">3. Tindak Lanjut</h3>
                    <p style="color: var(--slate); font-size: 0.95rem; line-height: 1.6;">Simpan <b>ID Laporan</b> Anda untuk mengecek status tindak lanjut tanpa perlu login kembali.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p style="font-weight: 700; color: white; margin-bottom: 8px;">SMK NEGERI 12 MALANG</p>
            <p style="opacity: 0.7;">&copy; Ahmad Haidar Rasyiq - XII PPLG 2.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });

        function konfirmasiLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = "auth/logout.php";
            })
        }

        function togglePass(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.className = "ph ph-eye-slash";
            } else {
                input.type = "password";
                icon.className = "ph ph-eye";
            }
        }

        function switchForm(type) {
            const login = document.getElementById('form-login');
            const regis = document.getElementById('form-register');
            
            if (type === 'register') {
                login.style.opacity = '0';
                setTimeout(() => {
                    login.classList.add('hidden');
                    regis.classList.remove('hidden');
                    setTimeout(() => regis.style.opacity = '1', 50);
                }, 300);
            } else {
                regis.style.opacity = '0';
                setTimeout(() => {
                    regis.classList.add('hidden');
                    login.classList.remove('hidden');
                    setTimeout(() => login.style.opacity = '1', 50);
                }, 300);
            }
        }
    </script>

    <?php if ($error): ?>
        <script>Swal.fire({ title: 'Gagal', text: '<?= $error ?>', icon: 'error', confirmButtonColor: '#8b5cf6' });</script>
    <?php endif; ?>

    <?php if ($success): ?>
        <script>Swal.fire({ title: 'Berhasil', text: '<?= $success ?>', icon: 'success', confirmButtonColor: '#8b5cf6' });</script>
    <?php endif; ?>

    <?php if ($search_result): ?>
        <script>
            Swal.fire({
                title: 'Status Laporan',
                html: `
                    <div style="text-align: left; font-size: 0.95rem;">
                        <p><b>ID Laporan:</b> <?= $kode_unik ?></p>
                        <p><b>Tanggal:</b> <?= date('d M Y', strtotime($search_result['tgl_pelaporan'])) ?></p>
                        <p><b>Kategori:</b> <?= $search_result['ket_kategori'] ?></p>
                        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #eee;">
                        <p><b>Status:</b> 
                            <span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;
                                <?= $search_result['status'] == 'Selesai' ? 'background:#dcfce7; color:#166534;' : ($search_result['status'] == 'Proses' ? 'background:#eff6ff; color:#1d4ed8;' : 'background:#fff7ed; color:#c2410c;') ?>">
                                <?= $search_result['status'] ?>
                            </span>
                        </p>
                        <p style="margin-top: 8px;"><b>Tanggapan Admin:</b><br>
                            <i style="color: #64748b;"><?= ($search_result['feedback'] == '-' || empty($search_result['feedback'])) ? 'Belum ada tanggapan.' : $search_result['feedback'] ?></i>
                        </p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#8b5cf6'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        </script>
    <?php endif; ?>
</body>
</html>