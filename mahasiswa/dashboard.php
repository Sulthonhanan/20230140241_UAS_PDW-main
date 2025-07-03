<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f0f2f5;
    margin: 0;
}

.classic-section {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 28px;
    margin: 36px auto;
    box-shadow: 0 12px 28px rgba(0,0,0,0.08);
    max-width: 1000px;
    transition: 0.3s ease;
}

.classic-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 12px;
}

.classic-dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-bottom: 28px;
    justify-content: space-between;
}

.classic-dashboard-card {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    padding: 28px 20px;
    flex: 1 1 calc(33% - 16px);
    min-width: 200px;
    text-align: center;
    transition: transform 0.3s;
}

.classic-dashboard-card:hover {
    transform: translateY(-6px);
}

.classic-card-value {
    font-size: 2.6rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.classic-card-label {
    font-size: 1.05rem;
    color: #555;
}

.classic-notif-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.classic-notif-item {
    display: flex;
    align-items: flex-start;
    padding: 14px 0;
    border-bottom: 1px solid #e0e0e0;
    font-size: 0.98rem;
}

.classic-notif-item:last-child {
    border-bottom: none;
}

.classic-notif-icon {
    font-size: 1.4rem;
    margin-right: 14px;
    color: #6c5ce7;
    margin-top: 2px;
}

.classic-link {
    color: #6c5ce7;
    text-decoration: none;
    font-weight: 600;
}

.classic-link:hover {
    text-decoration: underline;
}

@media (max-width: 900px) {
    .classic-section {
        padding: 24px 18px;
        margin: 24px auto;
    }
    .classic-dashboard-cards {
        flex-direction: column;
    }
}
</style>



<div class="classic-section" style="margin-bottom:28px;">
    <div class="classic-title" style="margin-bottom:8px;">
        Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!
    </div>
    <div style="color:#495057; margin-bottom:18px;">
        Tetap semangat menyelesaikan semua modul praktikum Anda.
    </div>

    <div class="classic-dashboard-cards">
        <div class="classic-dashboard-card">
            <div class="classic-card-value" style="color:#0d6efd;">3</div>
            <div class="classic-card-label">Praktikum Diikuti</div>
        </div>
        <div class="classic-dashboard-card">
            <div class="classic-card-value" style="color:#198754;">8</div>
            <div class="classic-card-label">Tugas Selesai</div>
        </div>
        <div class="classic-dashboard-card">
            <div class="classic-card-value" style="color:#ffc107;">4</div>
            <div class="classic-card-label">Tugas Belum Selesai</div>
        </div>
    </div>
</div>

<div class="classic-section">
    <div class="classic-title" style="font-size:1.15rem; margin-bottom:14px;">Notifikasi Terbaru</div>
    <ul class="classic-notif-list">
        <li class="classic-notif-item">
            <span class="classic-notif-icon">🔔</span>
            <div>
                Nilai untuk <a href="#" class="classic-link">Modul 1: HTML &amp; CSS</a> telah diberikan.
            </div>
        </li>
        <li class="classic-notif-item">
            <span class="classic-notif-icon">⏳</span>
            <div>
                Batas waktu pengumpulan laporan untuk <a href="#" class="classic-link">Modul 2: PHP Native</a> adalah besok!
            </div>
        </li>
        <li class="classic-notif-item">
            <span class="classic-notif-icon">✅</span>
            <div>
                Anda berhasil mendaftar pada praktikum <a href="#" class="classic-link">Jaringan Komputer</a>.
            </div>
        </li>
    </ul>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>