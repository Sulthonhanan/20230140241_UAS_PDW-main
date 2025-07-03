<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$id_mahasiswa = $_SESSION['user_id'];

$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi
        FROM mata_praktikum mp
        JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum
        WHERE pp.id_mahasiswa = ?
        ORDER BY mp.nama_praktikum ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$praktikum_diikuti = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum_diikuti[] = $row;
    }
}
$stmt->close();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f2f4f8;
    margin: 0;
}

.classic-section {
    background: #ffffff;
    border-radius: 20px;
    padding: 36px 28px;
    margin: 40px auto;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    max-width: 1000px;
    transition: all 0.3s ease;
}

.classic-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 28px;
}

.classic-list-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.classic-card {
    background: linear-gradient(145deg, #ffffff, #f9f9f9);
    border-radius: 16px;
    padding: 24px 22px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s ease;
}

.classic-card:hover {
    transform: translateY(-6px);
}

.classic-card-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #6c5ce7;
    margin-bottom: 10px;
}

.classic-card-desc {
    color: #555;
    font-size: 0.97rem;
    line-height: 1.5;
    margin-bottom: 20px;
    min-height: 60px;
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
    text-align: center;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s ease;
}

.classic-btn:hover {
    background-color: #5847d0;
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
    .classic-section {
        padding: 28px 18px;
    }
}
</style>


<div class="classic-section">
    <div class="classic-title">Praktikum yang Anda Ikuti</div>

    <?php if (!empty($praktikum_diikuti)): ?>
        <div class="classic-list-grid">
            <?php foreach ($praktikum_diikuti as $praktikum): ?>
                <div class="classic-card">
                    <div>
                        <div class="classic-card-title"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></div>
                        <div class="classic-card-desc">
                            <?php echo nl2br(htmlspecialchars($praktikum['deskripsi'] ?? 'Belum ada deskripsi.')); ?>
                        </div>
                    </div>
                    <a href="course_detail.php?id=<?php echo $praktikum['id']; ?>" class="classic-btn" style="margin-top:12px;">
                        Lihat Detail &amp; Tugas
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background:#fff3cd; border:1px solid #ffeeba; color:#856404; border-radius:6px; padding:16px 18px; margin-top:18px;">
            <strong>Informasi:</strong> Anda belum mengikuti praktikum apapun. Silakan <a href="courses.php" class="classic-link">cari praktikum</a> untuk mendaftar.
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>
