<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password wajib diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    $message = "Peran pengguna tidak valid.";
                }

            } else {
                $message = "Password salah.";
            }
        } else {
            $message = "Akun dengan email ini tidak ditemukan.";
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
    <title>Login Panel Praktikum</title>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #74ebd5, #9face6);
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .classic-login-container {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 400px;
        padding: 40px 30px;
        animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .classic-login-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        text-align: center;
        margin-bottom: 24px;
    }

    .classic-login-label {
        font-size: 0.95rem;
        color: #555;
        margin-bottom: 6px;
        font-weight: 500;
        display: block;
    }

    .classic-login-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #ccc;
        border-radius: 10px;
        font-size: 0.95rem;
        margin-bottom: 20px;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }

    .classic-login-input:focus {
        border-color: #6a89cc;
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
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        text-align: center;
        font-weight: 500;
        font-size: 0.95rem;
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

    @media (max-width: 480px) {
        .classic-login-container {
            padding: 30px 20px;
        }
    }
</style>

</head>
<body>
    <div class="classic-login-container">
        <div class="classic-login-title">Login Panel Praktikum</div>
        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<div class="classic-message success">Registrasi berhasil! Silakan login.</div>';
            }
            if (!empty($message)) {
                echo '<div class="classic-message error">' . htmlspecialchars($message) . '</div>';
            }
        ?>
        <form action="login.php" method="post" autocomplete="off">
            <div>
                <label for="email" class="classic-login-label">Email</label>
                <input type="email" id="email" name="email" class="classic-login-input" required>
            </div>
            <div>
                <label for="password" class="classic-login-label">Password</label>
                <input type="password" id="password" name="password" class="classic-login-input" required>
            </div>
            <button type="submit" class="classic-btn">Masuk</button>
        </form>
        <div class="classic-login-footer">
            Belum punya akun? <a href="register.php" class="classic-link">Daftar di sini</a>
        </div>
    </div>
</body>
</html>