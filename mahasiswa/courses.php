<?php
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

$sql = "SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result = $conn->query($sql);
$mata_praktikum_list = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mata_praktikum_list[] = $row;
    }
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar_praktikum'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php?redirect=courses.php");
        exit();
    }

    $id_praktikum = $_POST['id_praktikum'];
    $id_mahasiswa = $_SESSION['user_id'];

    $cek_sql = "SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?";
    $cek_stmt = $conn->prepare($cek_sql);
    $cek_stmt->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $cek_stmt->execute();
    $cek_result = $cek_stmt->get_result();

    if ($cek_result->num_rows > 0) {
        $message = "Anda sudah terdaftar pada praktikum ini.";
        $message_type = 'error';
    } else {
        $daftar_sql = "INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_praktikum) VALUES (?, ?)";
        $daftar_stmt = $conn->prepare($daftar_sql);
        $daftar_stmt->bind_param("ii", $id_mahasiswa, $id_praktikum);
        if ($daftar_stmt->execute()) {
            $message = "Berhasil mendaftar ke praktikum!";
            $message_type = 'success';
        } else {
            $message = "Gagal mendaftar ke praktikum. Silakan coba lagi. Error: " . $daftar_stmt->error;
            $message_type = 'error';
        }
        $daftar_stmt->close();
    }
    $cek_stmt->close();
}
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

.classic-message {
    padding: 14px 20px;
    border-radius: 10px;
    font-weight: 500;
    font-size: 0.96rem;
    margin-bottom: 24px;
    text-align: center;
    border-left: 6px solid;
}

.classic-message.success {
    background: #d1e7dd;
    color: #0f5132;
    border-color: #198754;
}

.classic-message.error {
    background: #f8d7da;
    color: #842029;
    border-color: #dc3545;
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

.classic-btn.disabled,
.classic-btn[disabled] {
    background: #ced4da;
    color: #fff;
    cursor: not-allowed;
}

.classic-link {
    color: #6c5ce7;
    text-decoration: none;
    font-weight: 600;
    display: block;
    margin-top: 10px;
    text-align: center;
    font-size: 0.94rem;
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
    <div class="classic-title">Cari &amp; Daftar Praktikum</div>

    <?php if (!empty($message)): ?>
        <div class="classic-message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mata_praktikum_list)): ?>
        <div class="classic-list-grid">
            <?php foreach ($mata_praktikum_list as $praktikum): ?>
                <div class="classic-card">
                    <div>
                        <div class="classic-card-title"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></div>
                        <div class="classic-card-desc">
                            <?php echo nl2br(htmlspecialchars($praktikum['deskripsi'] ?? 'Belum ada deskripsi.')); ?>
                        </div>
                    </div>
                    <div>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'mahasiswa'): ?>
                            <?php
                            $id_praktikum_current = $praktikum['id'];
                            $id_mahasiswa_current = $_SESSION['user_id'];
                            $stmt_check_enroll = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?");
                            $stmt_check_enroll->bind_param("ii", $id_mahasiswa_current, $id_praktikum_current);
                            $stmt_check_enroll->execute();
                            $is_enrolled = $stmt_check_enroll->get_result()->num_rows > 0;
                            $stmt_check_enroll->close();
                            ?>
                            <?php if ($is_enrolled): ?>
                                <button class="classic-btn disabled" disabled>Sudah Terdaftar</button>
                                <a href="course_detail.php?id=<?php echo $praktikum['id']; ?>" class="classic-link" style="display:block; margin-top:8px;">Lihat Detail</a>
                            <?php else: ?>
                                <form action="courses.php" method="post" style="margin-bottom:0;">
                                    <input type="hidden" name="id_praktikum" value="<?php echo $praktikum['id']; ?>">
                                    <button type="submit" name="daftar_praktikum" class="classic-btn">Daftar Praktikum</button>
                                </form>
                            <?php endif; ?>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="../login.php?redirect=mahasiswa/courses.php" class="classic-btn" style="background:#adb5bd; color:#212529;">Login untuk Mendaftar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background:#fff3cd; border:1px solid #ffeeba; color:#856404; border-radius:6px; padding:16px 18px; margin-top:18px;">
            <strong>Informasi:</strong> Belum ada praktikum yang tersedia saat ini.
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>
