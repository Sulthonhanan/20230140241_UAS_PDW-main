<?php
$pageTitle = 'Dasbor';
$activePage = 'dashboard';

require_once 'templates/header.php'; 
?>

<!-- Gaya modern minimalis -->
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color:rgb(242, 245, 249);
    }
    .dashboard-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 30px;
    }

    @media (min-width: 768px) {
        .dashboard-grid {
            flex-direction: row;
        }
    }

    .dashboard-card {
        flex: 1;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        padding: 20px 24px;
        display: flex;
        align-items: center;
        transition: box-shadow 0.3s ease;
    }

    .dashboard-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .dashboard-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #495057;
        margin-right: 18px;
    }

    .dashboard-info-title {
        font-size: 1rem;
        color: #495057;
        margin-bottom: 4px;
    }

    .dashboard-info-value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #212529;
    }

    .section {
        background: #fff;
        margin-top: 36px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        padding: 28px 24px;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 20px;
    }

    .activity {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }

    .activity-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        font-weight: bold;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        color: #fff;
    }

    .activity-text {
        font-size: 0.97rem;
        color: #343a40;
    }

    .activity-time {
        font-size: 0.87rem;
        color: #6c757d;
    }

    .activity:last-child {
        margin-bottom: 0;
    }
</style>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="dashboard-icon">📚</div>
        <div>
            <div class="dashboard-info-title">Total Modul Diajarkan</div>
            <div class="dashboard-info-value">12</div>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dashboard-icon">📥</div>
        <div>
            <div class="dashboard-info-title">Total Laporan Masuk</div>
            <div class="dashboard-info-value">152</div>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="dashboard-icon">⏳</div>
        <div>
            <div class="dashboard-info-title">Laporan Belum Dinilai</div>
            <div class="dashboard-info-value">18</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Aktivitas Laporan Terbaru</div>

    <div class="activity">
        <div class="activity-avatar" style="background:#0d6efd;">BS</div>
        <div>
            <div class="activity-text"><strong>Budi Santoso</strong> mengumpulkan laporan untuk <strong>Modul 2</strong></div>
            <div class="activity-time">10 menit yang lalu</div>
        </div>
    </div>

    <div class="activity">
        <div class="activity-avatar" style="background:#fd7e14;">CL</div>
        <div>
            <div class="activity-text"><strong>Citra Lestari</strong> mengumpulkan laporan untuk <strong>Modul 2</strong></div>
            <div class="activity-time">45 menit yang lalu</div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
