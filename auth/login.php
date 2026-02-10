<?php
session_start();
include '../config.php'; 

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/index.php");
        exit();
    } else if ($_SESSION['role'] == 'siswa') {
        header("Location: ../siswa/index.php");
        exit();
    }
}

if (isset($_POST['login_admin'])) {
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = mysqli_real_escape_string($conn, $_POST['pass']);

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE Username='$user' AND password='$pass'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $_SESSION['username'] = $row['Username'];
        $_SESSION['role'] = 'admin';
        
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | E-Aspirasi</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(circle at 0% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 35%),
                              radial-gradient(circle at 100% 100%, rgba(192, 132, 252, 0.15) 0%, transparent 35%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            perspective: 1000px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.7);
            transition: transform 0.3s ease;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.3);
        }

        .header h2 {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .header p {
            color: var(--text-slate);
            font-size: 14px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
            margin-left: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-slate);
            font-size: 20px;
            pointer-events: none;
            transition: 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: var(--text-dark);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .form-control:focus + .input-icon {
            color: var(--primary);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-slate);
            font-size: 20px;
            padding: 5px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .toggle-password:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .btn-admin {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(139, 92, 246, 0.4);
        }

        .btn-admin:active {
            transform: translateY(0);
        }

        .error-msg {
            background: #fff1f2;
            color: #e11d48;
            padding: 14px;
            border-radius: 14px;
            font-size: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #ffe4e6;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .links {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
        }

        .links p {
            color: var(--text-slate);
            margin-bottom: 12px;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .links a:hover {
            color: var(--primary-dark);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-slate) !important;
            font-weight: 600 !important;
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .back-link:hover {
            background: #f1f5f9;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
                border-radius: 24px;
            }
            .header h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <div class="header">    
                <h2>Login Admin</h2>
                <p>Kelola data dan tanggapi aspirasi siswa dengan bijak.</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-msg">
                    <i class="ph-bold ph-warning-circle" style="font-size: 20px;"></i> 
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="user" class="form-control" placeholder="masukkan username" required autofocus>
                        <i class="ph-bold ph-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="pass" id="passInput" class="form-control" placeholder="masukkan password" required>
                        <i class="ph-bold ph-lock input-icon"></i>
                        <i class="ph ph-eye toggle-password" id="toggleIcon" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" name="login_admin" class="btn-admin">
                    <span>Masuk ke Dashboard</span>
                    <i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>

            <div class="links">
                <p>Butuh akses admin baru? <a href="register_admin.php">Daftar Akun</a></p>
                <a href="../index.php" class="back-link">
                    <i class="ph-bold ph-caret-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passInput');
            const icon = document.getElementById('toggleIcon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('ph-eye', 'ph-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('ph-eye-slash', 'ph-eye');
            }
        }
    </script>
</body>
</html>