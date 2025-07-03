    <?php
    $pageTitle = 'Detail Praktikum'; 
    $activePage = 'my_courses'; 
    require_once '../config.php';
    require_once 'templates/header_mahasiswa.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
        header("Location: ../login.php");
        exit();
    }

    $id_mahasiswa = $_SESSION['user_id'];
    $id_praktikum = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id_praktikum <= 0) {
        echo "<div class='classic-section'><p style='color:#b02a37;'>ID Praktikum tidak valid.</p></div>";
        require_once 'templates/footer_mahasiswa.php';
        exit();
    }

    $stmt_check_enrollment = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?");
    $stmt_check_enrollment->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $stmt_check_enrollment->execute();
    $result_check_enrollment = $stmt_check_enrollment->get_result();
    if ($result_check_enrollment->num_rows == 0) {
        echo "<div class='classic-section'><p style='color:#b02a37;'>Anda belum terdaftar pada praktikum ini atau praktikum tidak ditemukan.</p></div>";
        require_once 'templates/footer_mahasiswa.php';
        $stmt_check_enrollment->close();
        $conn->close();
        exit();
    }
    $stmt_check_enrollment->close();

    $stmt_praktikum = $conn->prepare("SELECT nama_praktikum, deskripsi FROM mata_praktikum WHERE id = ?");
    $stmt_praktikum->bind_param("i", $id_praktikum);
    $stmt_praktikum->execute();
    $praktikum = $stmt_praktikum->get_result()->fetch_assoc();
    $stmt_praktikum->close();

    if (!$praktikum) {
        echo "<div class='classic-section'><p style='color:#b02a37;'>Praktikum tidak ditemukan.</p></div>";
        require_once 'templates/footer_mahasiswa.php';
        $conn->close();
        exit();
    }
    $pageTitle = htmlspecialchars($praktikum['nama_praktikum']);  

    $sql_modul = "SELECT m.id as id_modul, m.nama_modul, m.nama_file_materi, m.path_file_materi,
                lp.id as id_laporan, lp.nama_file_laporan, lp.path_file_laporan, lp.nilai, lp.feedback, lp.tanggal_kumpul, lp.tanggal_dinilai
                FROM modul m
                LEFT JOIN laporan_praktikum lp ON m.id = lp.id_modul AND lp.id_mahasiswa = ?
                WHERE m.id_praktikum = ?
                ORDER BY m.id ASC";
    $stmt_modul = $conn->prepare($sql_modul);
    $stmt_modul->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $stmt_modul->execute();
    $result_modul = $stmt_modul->get_result();
    $modul_list = [];
    if ($result_modul->num_rows > 0) {
        while ($row = $result_modul->fetch_assoc()) {
            $modul_list[] = $row;
        }
    }
    $stmt_modul->close();

    $upload_message = '';
    $upload_message_type = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kumpul_laporan'])) {
        $id_modul_laporan = (int)$_POST['id_modul'];

        if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == UPLOAD_ERR_OK) {
            $upload_dir_laporan = '../uploads/laporan/';
            if (!is_dir($upload_dir_laporan)) {
                mkdir($upload_dir_laporan, 0777, true);
            }

            $file_tmp_path = $_FILES['file_laporan']['tmp_name'];
            $file_name = basename($_FILES['file_laporan']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];

            if (in_array($file_ext, $allowed_ext)) {
                $new_file_name = "laporan_" . $id_mahasiswa . "_" . $id_modul_laporan . "_" . time() . "." . $file_ext;
                $dest_path = $upload_dir_laporan . $new_file_name;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $stmt_check_laporan = $conn->prepare("SELECT id, path_file_laporan FROM laporan_praktikum WHERE id_modul = ? AND id_mahasiswa = ?");
                    $stmt_check_laporan->bind_param("ii", $id_modul_laporan, $id_mahasiswa);
                    $stmt_check_laporan->execute();
                    $existing_laporan = $stmt_check_laporan->get_result()->fetch_assoc();
                    $stmt_check_laporan->close();

                    if ($existing_laporan) {
                        if ($existing_laporan['path_file_laporan'] && file_exists($existing_laporan['path_file_laporan'])) {
                            unlink($existing_laporan['path_file_laporan']);
                        }
                        $stmt_update_laporan = $conn->prepare("UPDATE laporan_praktikum SET nama_file_laporan = ?, path_file_laporan = ?, tanggal_kumpul = NOW(), nilai = NULL, feedback = NULL, tanggal_dinilai = NULL WHERE id = ?");
                        $stmt_update_laporan->bind_param("ssi", $file_name, $dest_path, $existing_laporan['id']);
                        if ($stmt_update_laporan->execute()) {
                            $upload_message = "Laporan berhasil diperbarui.";
                            $upload_message_type = 'success';
                        } else {
                            $upload_message = "Gagal memperbarui laporan di database: " . $stmt_update_laporan->error;
                            $upload_message_type = 'error';
                            if (file_exists($dest_path)) unlink($dest_path); 
                        }
                        $stmt_update_laporan->close();
                    } else {
                        $stmt_insert_laporan = $conn->prepare("INSERT INTO laporan_praktikum (id_modul, id_mahasiswa, nama_file_laporan, path_file_laporan, tanggal_kumpul) VALUES (?, ?, ?, ?, NOW())");
                        $stmt_insert_laporan->bind_param("iiss", $id_modul_laporan, $id_mahasiswa, $file_name, $dest_path);
                        if ($stmt_insert_laporan->execute()) {
                            $upload_message = "Laporan berhasil dikumpulkan.";
                            $upload_message_type = 'success';
                        } else {
                            $upload_message = "Gagal menyimpan laporan ke database: " . $stmt_insert_laporan->error;
                            $upload_message_type = 'error';
                            if (file_exists($dest_path)) unlink($dest_path);
                        }
                        $stmt_insert_laporan->close();
                    }

                    $stmt_modul_refresh = $conn->prepare($sql_modul);
                    $stmt_modul_refresh->bind_param("ii", $id_mahasiswa, $id_praktikum);
                    $stmt_modul_refresh->execute();
                    $result_modul_refresh = $stmt_modul_refresh->get_result();
                    $modul_list = [];
                    if ($result_modul_refresh->num_rows > 0) {
                        while ($row = $result_modul_refresh->fetch_assoc()) {
                            $modul_list[] = $row;
                        }
                    }
                    $stmt_modul_refresh->close();

                } else {
                    $upload_message = "Gagal memindahkan file yang diunggah.";
                    $upload_message_type = 'error';
                }
            } else {
                $upload_message = "Format file tidak diizinkan. Hanya PDF, DOC, DOCX, ZIP, RAR yang diperbolehkan.";
                $upload_message_type = 'error';
            }
        } else {
            $upload_message = "Terjadi kesalahan saat mengunggah file atau belum memilih file. Kode error: ".$_FILES['file_laporan']['error'];
            $upload_message_type = 'error';
        }
    }
    echo "<script>document.title = 'Panel Mahasiswa - " . htmlspecialchars($praktikum['nama_praktikum']) . "';</script>";
    ?>

    <div class="container" style="max-width: 960px; margin: 0 auto; padding: 30px 20px;">
    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 8px; color:#2c3e50;">
        <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
    </h1>
    <p style="color: #555; font-size: 1rem; line-height: 1.6;">
        <?php echo nl2br(htmlspecialchars($praktikum['deskripsi'] ?? 'Belum ada deskripsi.')); ?>
    </p>

    <?php if (!empty($upload_message)): ?>
        <div style="margin-top: 20px; padding: 12px 16px; border-radius: 6px;
                    background: <?= $upload_message_type === 'success' ? '#e6ffed' : '#fdecea'; ?>;
                    color: <?= $upload_message_type === 'success' ? '#1e7e34' : '#721c24'; ?>;
                    border: 1px solid <?= $upload_message_type === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>">
            <?php echo htmlspecialchars($upload_message); ?>
        </div>
    <?php endif; ?>

    <h2 style="margin-top: 30px; font-size: 1.3rem; color: #34495e; border-bottom: 1px solid #ddd; padding-bottom: 6px;">
        Daftar Modul Praktikum
    </h2>

    <?php if (!empty($modul_list)): ?>
        <?php foreach ($modul_list as $index => $modul): ?>
            <div style="margin-top: 20px; padding: 20px; border-radius: 10px;
                        background: #fefefe; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="font-weight: 600; font-size: 1.1rem; color: #007BFF;">
                        <?php echo ($index + 1) . ". " . htmlspecialchars($modul['nama_modul']); ?>
                    </div>
                    <?php if ($modul['nama_file_materi'] && $modul['path_file_materi']): ?>
                        <a href="../<?php echo htmlspecialchars($modul['path_file_materi']); ?>" 
                           download="<?php echo htmlspecialchars($modul['nama_file_materi']); ?>"
                           style="background: #d4edda; color: #155724; padding: 6px 12px;
                                  border-radius: 4px; font-size: 0.9rem; text-decoration: none;">
                            Unduh Materi
                        </a>
                    <?php else: ?>
                        <span style="color: #aaa; font-size: 0.9rem;">Materi belum tersedia</span>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 16px;">
                    <strong style="color: #555;">Pengumpulan Laporan:</strong><br>

                    <?php if ($modul['id_laporan']): ?>
                        <p style="margin: 8px 0; color: #444;">
                            Anda telah mengumpulkan: 
                            <a href="../<?php echo htmlspecialchars($modul['path_file_laporan']); ?>"
                               download="<?php echo htmlspecialchars($modul['nama_file_laporan']); ?>"
                               style="color: #007BFF; text-decoration: none;">
                                <?php echo htmlspecialchars($modul['nama_file_laporan']); ?>
                            </a>
                            (<?php echo date('d M Y, H:i', strtotime($modul['tanggal_kumpul'])); ?>)
                        </p>
                        <?php if ($modul['tanggal_dinilai']): ?>
                            <div style="background: #e9f7ef; border-left: 5px solid #28a745;
                                        padding: 10px 15px; border-radius: 6px; color: #155724;">
                                <div><strong>Nilai:</strong> <?php echo htmlspecialchars($modul['nilai']); ?></div>
                                <?php if ($modul['feedback']): ?>
                                    <div style="margin-top: 4px;"><strong>Feedback:</strong> <?php echo nl2br(htmlspecialchars($modul['feedback'])); ?></div>
                                <?php endif; ?>
                                <div style="font-size: 0.85rem; color: #6c757d;">Dinilai pada: <?php echo date('d M Y, H:i', strtotime($modul['tanggal_dinilai'])); ?></div>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 4px; color: #856404;">Laporan Anda menunggu penilaian.</div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form method="post" action="course_detail.php?id=<?php echo $id_praktikum; ?>" enctype="multipart/form-data" style="margin-top: 12px;">
                        <input type="hidden" name="id_modul" value="<?php echo $modul['id_modul']; ?>">
                        <label style="font-weight: 500; margin-bottom: 6px; display: block;">Pilih File Laporan:</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="file" name="file_laporan" required style="flex: 1;">
                            <button type="submit" name="kumpul_laporan"
                                    style="background: #007BFF; color: white; border: none; padding: 8px 16px;
                                           border-radius: 6px; cursor: pointer;">
                                <?php echo $modul['id_laporan'] ? 'Unggah Ulang' : 'Kumpulkan'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="margin-top: 16px; padding: 16px; background: #fff3cd; border-left: 6px solid #ffecb5;
                    border-radius: 6px; color: #856404;">
            Belum ada modul ditambahkan pada praktikum ini.
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="my_courses.php" style="text-decoration: none; color: #007BFF;">&larr; Kembali ke Praktikum Saya</a>
    </div>
</div>
    <?php
    $conn->close();
    require_once 'templates/footer_mahasiswa.php';
    ?>
