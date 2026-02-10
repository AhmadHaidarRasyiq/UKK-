<?php
session_start();
include '../config.php';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: ../admin/index.php");
    exit();
}

$error = "";
$success = false; 

if (isset($_POST['reg_admin'])) {
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = $_POST['pass']; 
    $confirm_pass = $_POST['confirm_pass']; 

    if ($pass !== $confirm_pass) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM admin WHERE username='$user'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $query = "INSERT INTO admin (username, password) VALUES ('$user', '$pass')";
            
            if (mysqli_query($conn, $query)) {
                $success = true; 
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin | E-Aspirasi</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #6d28d9;
            --primary-light: #ddd6fe;
            --text-dark: #1e1b4b;
            --text-slate: #64748b;
            --bg: #f5f3ff;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(circle at 100% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 35%),
                              radial-gradient(circle at 0% 100%, rgba(192, 132, 252, 0.15) 0%, transparent 35%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 25px;
        }

        .container { width: 100%; max-width: 420px; }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .logo-icon {
            width: 55px; height: 55px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; color: white; font-size: 28px;
            box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.3);
        }

        h2 { color: var(--text-dark); font-weight: 800; font-size: 24px; letter-spacing: -0.5px; margin-bottom: 8px; }
        .header p { color: var(--text-slate); font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; padding-left: 4px; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-slate); font-size: 20px; pointer-events: none; transition: 0.3s; }

        input {
            width: 100%; padding: 13px 16px 13px 48px;
            border: 2px solid #f1f5f9; border-radius: 14px;
            font-size: 15px; outline: none; transition: all 0.3s ease;
            background: #f8fafc; font-family: inherit; color: var(--text-dark);
        }
        
        input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
        input:focus + .input-icon { color: var(--primary); }

        .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-slate); font-size: 20px; padding: 4px; transition: 0.3s; }

        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; border: none; border-radius: 14px;
            font-weight: 700; cursor: pointer; font-size: 15px;
            transition: all 0.3s ease; margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(139, 92, 246, 0.2);
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -5px rgba(139, 92, 246, 0.4); }

        .error {
            background: #fff1f2; color: #e11d48; padding: 12px;
            border-radius: 12px; font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            border: 1px solid #ffe4e6; animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-slate); }
        .login-link a { color: var(--primary); text-decoration: none; font-weight: 700; }

        @media (max-width: 480px) {
            .card { padding: 30px 20px; border-radius: 24px; }
            h2 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h2>Registrasi Admin</h2>
                <p>Buat akses baru untuk pengelola sistem</p>
            </div>
            
            <?php if($error): ?>
                <div class="error">
                    <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i> 
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="user" placeholder="Buat username" value="<?= isset($_POST['user']) ? htmlspecialchars($_POST['user']) : '' ?>" required>
                        <i class="ph-bold ph-user-circle input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="pass" class="password-field" placeholder="Buat password" required>
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <i class="ph ph-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_pass" class="password-field" placeholder="Ulangi password" required>
                        <i class="ph-bold ph-shield-check input-icon"></i>
                        <i class="ph ph-eye toggle-password"></i>
                    </div>
                </div>

                <button type="submit" name="reg_admin" class="btn-submit">
                    Daftar Akun Admin
                </button>
            </form>

            <div class="login-link">
                Sudah punya akses? <a href="login.php">Masuk sekarang</a>
            </div>
        </div>
    </div>

    <script>
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('ph-eye');
                this.classList.toggle('ph-eye-slash');
            });
        });

        <?php if($success): ?>
            Swal.fire({
                title: 'Registrasi Berhasil!',
                text: 'Akun admin Anda telah terdaftar. Silakan login.',
                icon: 'success',
                confirmButtonColor: '#8b5cf6',
                confirmButtonText: 'Ke Halaman Login',
                background: '#ffffff',
                customClass: {
                    popup: 'swal2-border-radius'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'login.php';
                }
            });
        <?php endif; ?>
    </script>
    <style>
        .swal2-border-radius { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</body>
</html>