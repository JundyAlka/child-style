<?php
session_start();
require_once 'config.php';

// Proses login
if (isset($_POST['login-submit'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['login-email']);
    $password = $_POST['login-password'];

    // Query untuk mencari user
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    // Verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        // Login berhasil
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        echo "<script>
            alert('Login berhasil!');
            window.location.href = 'index.php';
        </script>";
        exit();
    } else {
        // Login gagal
        $login_error = "Email atau password salah!";
    }
}

// Proses registrasi
if (isset($_POST['register-submit'])) {
    $name = mysqli_real_escape_string($koneksi, $_POST['register-name']);
    $email = mysqli_real_escape_string($koneksi, $_POST['register-email']);
    $password = password_hash($_POST['register-password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $register_error = "Email sudah terdaftar. Silakan gunakan email lain.";
    } else {
        // Query insert user baru
        $query = "INSERT INTO users (username, email, password, full_name) 
                  VALUES ('$name', '$email', '$password', '$name')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>
                alert('Registrasi berhasil! Silakan login.');
                window.location.href = 'index-auth.php';
            </script>";
            exit();
        } else {
            $register_error = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}

// Proses lupa password
if (isset($_POST['forgot-password-submit'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['forgot-email']);
    
    // Cek apakah email terdaftar
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Simpan OTP ke database (asumsi ada kolom 'otp' di tabel users)
        $update_otp = mysqli_query($koneksi, "UPDATE users SET otp = '$otp' WHERE email = '$email'");
        
        if ($update_otp) {
            // Kirim email dengan OTP (gunakan fungsi mail() atau library PHPMailer)
            $to = $email;
            $subject = "Reset Password OTP";
            $message = "Kode OTP Anda adalah: " . $otp;
            $headers = "From: noreply@example.com";
            
            if (mail($to, $subject, $message, $headers)) {
                echo "<script>
                    alert('OTP telah dikirim ke email Anda.');
                    document.getElementById('forgot-password-section').classList.add('hidden');
                    document.getElementById('otp-section').classList.remove('hidden');
                </script>";
            } else {
                $forgot_password_error = "Gagal mengirim OTP. Silakan coba lagi.";
            }
        } else {
            $forgot_password_error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    } else {
        $forgot_password_error = "Email tidak terdaftar.";
    }
}

// Proses verifikasi OTP
if (isset($_POST['verify-otp-submit'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['otp-email']);
    $otp = mysqli_real_escape_string($koneksi, $_POST['otp-code']);
    
    $query = "SELECT * FROM users WHERE email='$email' AND otp='$otp'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        // OTP valid, izinkan user untuk reset password
        $_SESSION['reset_email'] = $email;
        echo "<script>
            alert('OTP valid. Silakan reset password Anda.');
            document.getElementById('otp-section').classList.add('hidden');
            document.getElementById('reset-password-section').classList.remove('hidden');
        </script>";
    } else {
        $otp_error = "OTP tidak valid. Silakan coba lagi.";
    }
}

// Proses reset password
if (isset($_POST['reset-password-submit'])) {
    $email = $_SESSION['reset_email'];
    $new_password = password_hash($_POST['new-password'], PASSWORD_DEFAULT);
    
    $update_password = mysqli_query($koneksi, "UPDATE users SET password = '$new_password', otp = NULL WHERE email = '$email'");
    
    if ($update_password) {
        unset($_SESSION['reset_email']);
        echo "<script>
            alert('Password berhasil direset. Silakan login dengan password baru Anda.');
            window.location.href = 'index-auth.php';
        </script>";
        exit();
    } else {
        $reset_password_error = "Gagal mereset password. Silakan coba lagi.";
    }
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiddie Korner - <?php echo ucfirst($current_page); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Comic Neue', cursive;
            background: linear-gradient(to bottom, #ffe4e6, #e6e6fa);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }
        .container {
            background-color: white;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
            color: #ff69b4;
            font-size: 2rem;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #9370db;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ff69b4;
            border-radius: 25px;
            font-size: 1rem;
            font-family: 'Comic Neue', cursive;
        }
        .form-group button {
            width: 100%;
            padding: 12px;
            background-color: #ff69b4;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1rem;
            font-weight: bold;
            font-family: 'Comic Neue', cursive;
        }
        .form-group button:hover {
            background-color: #ff1493;
        }
        .form-switch {
            text-align: center;
            margin-top: 15px;
        }
        .form-switch a {
            color: #9370db;
            text-decoration: none;
            font-weight: bold;
        }
        .error-message {
            color: #ff4500;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($current_page == 'login'): ?>
            <h2 class="form-title">Login</h2>
            <?php if(isset($login_error)): ?>
                <div class="error-message"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form id="login-form" method="POST">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="login-email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="login-password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="login-submit">Login</button>
                </div>
            </form>
            <div class="form-switch">
                <a href="?page=forgot_password">Lupa password?</a>
            </div>
            <div class="form-switch">
                Belum punya akun? <a href="?page=register">Daftar sekarang</a>
            </div>

        <?php elseif ($current_page == 'register'): ?>
            <h2 class="form-title">Registrasi</h2>
            <?php if(isset($register_error)): ?>
                <div class="error-message"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <form id="register-form" method="POST">
                <div class="form-group">
                    <label for="register-name">Nama Lengkap</label>
                    <input type="text" id="register-name" name="register-name" required>
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="register-email" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="register-password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="register-submit">Daftar</button>
                </div>
            </form>
            <div class="form-switch">
                Sudah punya akun? <a href="?page=login">Login</a>
            </div>

        <?php elseif ($current_page == 'forgot_password'): ?>
            <h2 class="form-title">Lupa Password</h2>
            <?php if(isset($forgot_password_error)): ?>
                <div class="error-message"><?php echo $forgot_password_error; ?></div>
            <?php endif; ?>
            <form id="forgot-password-form" method="POST">
                <div class="form-group">
                    <label for="forgot-email">Email</label>
                    <input type="email" id="forgot-email" name="forgot-email" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="forgot-password-submit">Kirim OTP</button>
                </div>
            </form>
            <div class="form-switch">
                <a href="?page=login">Kembali ke Login</a>
            </div>

        <?php elseif ($current_page == 'otp'): ?>
            <h2 class="form-title">Verifikasi OTP</h2>
            <?php if(isset($otp_error)): ?>
                <div class="error-message"><?php echo $otp_error; ?></div>
            <?php endif; ?>
            <form id="otp-form" method="POST">
                <div class="form-group">
                    <label for="otp-email">Email</label>
                    <input type="email" id="otp-email" name="otp-email" required>
                </div>
                <div class="form-group">
                    <label for="otp-code">Kode OTP</label>
                    <input type="text" id="otp-code" name="otp-code" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="verify-otp-submit">Verifikasi OTP</button>
                </div>
            </form>

        <?php elseif ($current_page == 'reset_password'): ?>
            <h2 class="form-title">Reset Password</h2>
            <?php if(isset($reset_password_error)): ?>
                <div class="error-message"><?php echo $reset_password_error; ?></div>
            <?php endif; ?>
            <form id="reset-password-form" method="POST">
                <div class="form-group">
                    <label for="new-password">Password Baru</label>
                    <input type="password" id="new-password" name="new-password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-new-password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm-new-password" name="confirm-new-password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="reset-password-submit">Reset Password</button>
                </div>
            </form>

        <?php endif; ?>
    </div>
</body>
</html>