    <?php
    session_start();

    $_SESSION = array();
    session_destroy();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Keluar - Panel Praktikum</title>
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
    }

    .classic-logout-container {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 90%;
        padding: 40px 30px;
        text-align: center;
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .classic-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 20px;
    }

    .classic-message {
        padding: 16px;
        border-radius: 12px;
        background: #dff9fb;
        color: #130f40;
        border: 1px solid #c7ecee;
        font-size: 1rem;
        margin-bottom: 24px;
        line-height: 1.5;
    }

    .classic-btn {
        background: #6c5ce7;
        color: #fff;
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s ease;
        display: inline-block;
    }

    .classic-btn:hover {
        background-color: #5a4fcf;
    }
</style>

    </head>
    <body>
        <div class="classic-logout-container">
            <div class="classic-title">Anda telah keluar</div>
            <div class="classic-message">
                Anda berhasil keluar dari Panel Praktikum.<br>
                Sampai jumpa kembali!
            </div>
            <a href="login.php" class="classic-btn">Kembali ke Login</a>
        </div>
    </body>
    </html>