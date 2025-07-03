<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Gunakan email lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Akun Panel Praktikum</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #74ebd5, #acb6e5);
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .classic-login-container {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        max-width: 420px;
        width: 100%;
        padding: 40px 30px;
        animation: slideIn 0.6s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .classic-login-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
    }

    .classic-login-label {
        font-size: 0.95rem;
        color: #34495e;
        margin-bottom: 6px;
        font-weight: 600;
        display: block;
    }

    .classic-login-input,
    .classic-login-select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #ccc;
        border-radius: 12px;
        font-size: 0.95rem;
        margin-bottom: 20px;
        box-sizing: border-box;
        transition: border-color 0.2s ease;
    }

    .classic-login-input:focus,
    .classic-login-select:focus {
        border-color: #6c5ce7;
        outline: none;
    }

    .classic-btn {
        background-color: #6c5ce7;
        color: #fff;
        padding: 12px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        width: 100%;
        transition: background-color 0.3s;
    }

    .classic-btn:hover {
        background-color: #5a4fcf;
    }

    .classic-message {
        padding: 14px;
        border-radius: 12px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 500;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .classic-message.success {
        background: #d1f2eb;
        color: #117864;
        border: 1px solid #a3e4d7;
    }

    .classic-message.error {
        background: #fdecea;
        color: #c0392b;
        border: 1px solid #f5b7b1;
    }

    .classic-login-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 0.95rem;
        color: #555;
    }

    .classic-link {
        color: #6c5ce7;
        text-decoration: none;
        font-weight: 600;
    }

    .classic-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 500px) {
        .classic-login-container {
            padding: 30px 20px;
        }
    }
</style>
    
</head>
<body>
    <div class="classic-login-container">
        <div class="classic-login-title">Registrasi Akun Panel Praktikum</div>
        <?php if (!empty($message)): ?>
            <div class="classic-message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="register.php" method="post" autocomplete="off">
            <div>
                <label for="nama" class="classic-login-label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="classic-login-input" required>
            </div>
            <div>
                <label for="email" class="classic-login-label">Email</label>
                <input type="email" id="email" name="email" class="classic-login-input" required>
            </div>
            <div>
                <label for="password" class="classic-login-label">Password</label>
                <input type="password" id="password" name="password" class="classic-login-input" required>
            </div>
            <div>
                <label for="role" class="classic-login-label">Daftar Sebagai</label>
                <select id="role" name="role" class="classic-login-select" required>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <button type="submit" class="classic-btn">Daftar</button>
        </form>
        <div class="classic-login-footer">
            Sudah punya akun? <a href="login.php" class="classic-link">Login di sini</a>
        </div>
    </div>
</body>
</html>