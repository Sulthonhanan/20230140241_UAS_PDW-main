<?php
// Konfigurasi koneksi database untuk Panel Praktikum
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'pengumpulantugas');

// Membuat koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("<div style='
        background:#fff3cd;
        border:1px solidrgb(244, 23, 23);
        color:#856404;
        border-radius:6px;
        padding:18px 24px;
        margin:32px auto;
        max-width:600px;
        font-family:Segoe UI,Arial,sans-serif;
        font-size:1.1rem;
        text-align:center;
    '>
        <strong>Koneksi ke database gagal:</strong><br>
        " . htmlspecialchars($conn->connect_error) . "
    </div>");
}
?>