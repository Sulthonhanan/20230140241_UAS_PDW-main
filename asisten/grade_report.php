<?php
$pageTitle = 'Penilaian Laporan';
$activePage = 'laporan';
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_laporan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

if ($id_laporan <= 0) {
    echo "<div class='classic-section'><p style='color:#b02a37;'>ID Laporan tidak valid.</p> <a href='submitted_reports.php' class='classic-link'>&larr; Kembali ke Daftar Laporan</a></div>";
    require_once 'templates/footer.php';
    exit();
}

// Get report details
$sql_laporan = "SELECT lp.*, u.nama as nama_mahasiswa, u.email as email_mahasiswa,
                       m.nama_modul, mp.nama_praktikum
                FROM laporan_praktikum lp
                JOIN users u ON lp.id_mahasiswa = u.id
                JOIN modul m ON lp.id_modul = m.id
                JOIN mata_praktikum mp ON m.id_praktikum = mp.id
                WHERE lp.id = ?";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->bind_param("i", $id_laporan);
$stmt_laporan->execute();
$laporan = $stmt_laporan->get_result()->fetch_assoc();
$stmt_laporan->close();

if (!$laporan) {
    echo "<div class='classic-section'><p style='color:#b02a37;'>Laporan tidak ditemukan.</p> <a href='submitted_reports.php' class='classic-link'>&larr; Kembali ke Daftar Laporan</a></div>";
    require_once 'templates/footer.php';
    $conn->close();
    exit();
}

$pageTitle = 'Penilaian: ' . htmlspecialchars($laporan['nama_modul']) . ' - ' . htmlspecialchars($laporan['nama_mahasiswa']);
?>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f4f6f8;
    }
    .classic-section {
        background: #ffffff;
        border-radius: 12px;
        padding: 32px 28px;
        margin: 40px auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        max-width: 740px;
    }
    .classic-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }
    .classic-value {
        font-size: 1.05rem;
        font-weight: 500;
        color: #212529;
        margin-bottom: 14px;
    }
    .classic-input, .classic-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 1rem;
        background-color: #fdfdfd;
        transition: border 0.2s;
    }
    .classic-input:focus, .classic-textarea:focus {
        border-color: #80bdff;
        outline: none;
    }
    .classic-textarea {
        resize: vertical;
        min-height: 100px;
    }
    .classic-btn {
        background-color: #0d6efd;
        color: #fff;
        padding: 10px 28px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.2s ease;
    }
    .classic-btn:hover {
        background-color: #0b5ed7;
    }
    .classic-message {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 18px;
        font-weight: 500;
        text-align: center;
    }
    .classic-message.success {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .classic-message.error {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
    .classic-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }
    .classic-link:hover {
        text-decoration: underline;
    }
    a.download-link {
        display: inline-block;
        padding: 6px 14px;
        background: #e7f1ff;
        color: #0d6efd;
        font-weight: 500;
        border-radius: 6px;
        text-decoration: none;
        margin-top: 6px;
    }
    a.download-link:hover {
        background: #d0e3ff;
    }
</style>

<div class="classic-section">
    <div style="margin-bottom: 18px;">
        <a href="submitted_reports.php" class="classic-link">&larr; Kembali ke Daftar Laporan</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="classic-message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 24px;">
        <div class="classic-label">Praktikum</div>
        <div class="classic-value"><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></div>
        <div class="classic-label">Modul</div>
        <div class="classic-value"><?php echo htmlspecialchars($laporan['nama_modul']); ?></div>
        <div class="classic-label">Mahasiswa</div>
        <div class="classic-value"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?> <span style="font-size:0.95rem; color:#6c757d;">(<?php echo htmlspecialchars($laporan['email_mahasiswa']); ?>)</span></div>
        <div class="classic-label">Tanggal Pengumpulan</div>
        <div class="classic-value"><?php echo date('d M Y, H:i:s', strtotime($laporan['tanggal_kumpul'])); ?></div>
        <div class="classic-label">File Laporan</div>
        <?php if ($laporan['nama_file_laporan'] && $laporan['path_file_laporan']): ?>
            <a href="../<?php echo htmlspecialchars($laporan['path_file_laporan']); ?>" download="<?php echo htmlspecialchars($laporan['nama_file_laporan']); ?>" class="classic-link">
                Unduh Laporan (<?php echo htmlspecialchars($laporan['nama_file_laporan']); ?>)
            </a>
        <?php else: ?>
            <div style="color:#b02a37; background:#f8d7da; padding:8px 12px; border-radius:4px; margin-bottom:10px;">File laporan tidak ditemukan atau belum diunggah dengan benar.</div>
        <?php endif; ?>
    </div>

    <form action="grade_report.php?id=<?php echo $id_laporan; ?>" method="post">
        <div style="margin-bottom: 18px;">
            <label for="nilai" class="classic-label">Nilai (0-100)</label>
            <input type="number" name="nilai" id="nilai" min="0" max="100" step="1" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" class="classic-input">
            <?php if ($laporan['tanggal_dinilai']): ?>
                <div style="font-size:0.95rem; color:#6c757d;">Terakhir dinilai: <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_dinilai'])); ?></div>
            <?php endif; ?>
        </div>
        <div style="margin-bottom: 18px;">
            <label for="feedback" class="classic-label">Feedback</label>
            <textarea id="feedback" name="feedback" rows="4" class="classic-textarea"><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
        </div>
        <div style="text-align:right;">
            <button type="submit" name="simpan_nilai" class="classic-btn">Simpan Nilai</button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
