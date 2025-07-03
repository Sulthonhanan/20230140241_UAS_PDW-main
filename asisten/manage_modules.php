<?php
$pageTitle = 'Kelola Modul';
$activePage = 'manage_courses';
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_praktikum = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$message = '';
$message_type = '';

if ($id_praktikum <= 0) {
    echo "<div class='classic-section'><p style='color:#b02a37;'>ID Praktikum tidak valid.</p> <a href='manage_courses.php' class='classic-link'>&larr; Kembali ke Kelola Praktikum</a></div>";
    require_once 'templates/footer.php';
    exit();
}

// Get lab course name for title
$stmt_course_name = $conn->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = ?");
$stmt_course_name->bind_param("i", $id_praktikum);
$stmt_course_name->execute();
$course_data = $stmt_course_name->get_result()->fetch_assoc();
if (!$course_data) {
    echo "<div class='classic-section'><p style='color:#b02a37;'>Praktikum tidak ditemukan.</p> <a href='manage_courses.php' class='classic-link'>&larr; Kembali ke Kelola Praktikum</a></div>";
    require_once 'templates/footer.php';
    exit();
}
$pageTitle = 'Modul: ' . htmlspecialchars($course_data['nama_praktikum']);
$stmt_course_name->close();

// Handle Module Actions (Add, Edit, Delete)
$upload_dir_materi = '../uploads/materi/';
if (!is_dir($upload_dir_materi)) {
    mkdir($upload_dir_materi, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tambah Modul
    if (isset($_POST['tambah_modul'])) {
        $nama_modul = trim($_POST['nama_modul']);
        $nama_file_materi = null;
        $path_file_materi = null;

        if (!empty($nama_modul)) {
            if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['file_materi']['tmp_name'];
                $file_name = basename($_FILES['file_materi']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];

                if (in_array($file_ext, $allowed_ext)) {
                    $new_file_name = "materi_" . $id_praktikum . "_" . time() . "_" . $file_name;
                    $dest_path = $upload_dir_materi . $new_file_name;
                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        $nama_file_materi = $file_name;
                        $path_file_materi = 'uploads/materi/' . $new_file_name;
                    } else {
                        $message = "Gagal memindahkan file materi.";
                        $message_type = 'error';
                    }
                } else {
                    $message = "Format file materi tidak diizinkan.";
                    $message_type = 'error';
                }
            } elseif ($_FILES['file_materi']['error'] != UPLOAD_ERR_NO_FILE) {
                $message = "Terjadi kesalahan saat mengunggah file materi: kode " . $_FILES['file_materi']['error'];
                $message_type = 'error';
            }

            if (empty($message)) {
                $stmt = $conn->prepare("INSERT INTO modul (id_praktikum, nama_modul, nama_file_materi, path_file_materi) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $id_praktikum, $nama_modul, $nama_file_materi, $path_file_materi);
                if ($stmt->execute()) {
                    $message = "Modul berhasil ditambahkan.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menambahkan modul: " . $stmt->error;
                    $message_type = 'error';
                    if ($path_file_materi && file_exists('../'.$path_file_materi)) unlink('../'.$path_file_materi);
                }
                $stmt->close();
            }
        } else {
            $message = "Nama modul tidak boleh kosong.";
            $message_type = 'error';
        }
    }
    // Edit Modul
    elseif (isset($_POST['edit_modul'])) {
        $id_modul = (int)$_POST['id_modul_edit'];
        $nama_modul = trim($_POST['nama_modul_edit']);
        $path_file_materi_lama = $_POST['path_file_materi_lama_edit'];
        $nama_file_materi_baru = null;
        $path_file_materi_baru = $path_file_materi_lama;

        if (!empty($nama_modul) && $id_modul > 0) {
            if (isset($_FILES['file_materi_edit']) && $_FILES['file_materi_edit']['error'] == UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['file_materi_edit']['tmp_name'];
                $file_name = basename($_FILES['file_materi_edit']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];

                if (in_array($file_ext, $allowed_ext)) {
                    $new_file_name = "materi_" . $id_praktikum . "_" . time() . "_" . $file_name;
                    $dest_path_new = $upload_dir_materi . $new_file_name;
                    if (move_uploaded_file($file_tmp_path, $dest_path_new)) {
                        if ($path_file_materi_lama && file_exists('../'.$path_file_materi_lama) && $path_file_materi_lama != ('uploads/materi/' . $new_file_name)) {
                            unlink('../'.$path_file_materi_lama);
                        }
                        $nama_file_materi_baru = $file_name;
                        $path_file_materi_baru = 'uploads/materi/' . $new_file_name;
                    } else {
                        $message = "Gagal memindahkan file materi baru.";
                        $message_type = 'error';
                    }
                } else {
                    $message = "Format file materi baru tidak diizinkan.";
                    $message_type = 'error';
                }
            } elseif (isset($_POST['hapus_file_materi_edit_checkbox']) && $_POST['hapus_file_materi_edit_checkbox'] == '1') {
                if ($path_file_materi_lama && file_exists('../'.$path_file_materi_lama)) {
                    unlink('../'.$path_file_materi_lama);
                }
                $nama_file_materi_baru = null;
                $path_file_materi_baru = null;
            } elseif ($_FILES['file_materi_edit']['error'] != UPLOAD_ERR_NO_FILE) {
                $message = "Terjadi kesalahan saat mengunggah file materi baru: kode " . $_FILES['file_materi_edit']['error'];
                $message_type = 'error';
            }

            if (empty($message)) {
                $stmt = $conn->prepare("UPDATE modul SET nama_modul = ?, nama_file_materi = ?, path_file_materi = ? WHERE id = ? AND id_praktikum = ?");
                $stmt->bind_param("sssii", $nama_modul, $nama_file_materi_baru, $path_file_materi_baru, $id_modul, $id_praktikum);
                if ($stmt->execute()) {
                    $message = "Modul berhasil diperbarui.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal memperbarui modul: " . $stmt->error;
                    $message_type = 'error';
                    if ($path_file_materi_baru != $path_file_materi_lama && $path_file_materi_baru && file_exists('../'.$path_file_materi_baru) ) {
                        unlink('../'.$path_file_materi_baru);
                    }
                }
                $stmt->close();
            }
        } else {
            $message = "Nama modul tidak boleh kosong dan ID harus valid.";
            $message_type = 'error';
        }
    }
    // Hapus Modul
    elseif (isset($_POST['hapus_modul'])) {
        $id_modul = (int)$_POST['id_modul_hapus'];
        $path_file_materi_hapus = $_POST['path_file_materi_hapus'];

        if ($id_modul > 0) {
            $stmt = $conn->prepare("DELETE FROM modul WHERE id = ? AND id_praktikum = ?");
            $stmt->bind_param("ii", $id_modul, $id_praktikum);
            if ($stmt->execute()) {
                if ($path_file_materi_hapus && file_exists('../'.$path_file_materi_hapus)) {
                    unlink('../'.$path_file_materi_hapus);
                }
                $message = "Modul berhasil dihapus.";
                $message_type = 'success';
            } else {
                $message = "Gagal menghapus modul: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get all modules for this lab course
$modul_list = [];
$stmt_modul = $conn->prepare("SELECT id, nama_modul, nama_file_materi, path_file_materi FROM modul WHERE id_praktikum = ? ORDER BY id ASC");
$stmt_modul->bind_param("i", $id_praktikum);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
if ($result_modul && $result_modul->num_rows > 0) {
    while ($row = $result_modul->fetch_assoc()) {
        $modul_list[] = $row;
    }
}
$stmt_modul->close();
?>

<style>
/* === Modern Section Layout === */
.module-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 32px;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #e0e0e0;
}

/* === Typography & Titles === */
.module-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 24px;
}
.module-subtitle {
    font-size: 1.2rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
}

/* === Form Elements === */
.module-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}
.module-input, .module-textarea, .module-file {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 1rem;
    margin-bottom: 16px;
}
.module-file {
    background-color: #f9fafb;
}

/* === Buttons === */
.module-btn {
    background-color: #3b82f6;
    color: white;
    padding: 10px 20px;
    border: none;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}
.module-btn:hover {
    background-color: #2563eb;
}
.module-btn.secondary {
    background-color: #e5e7eb;
    color: #1f2937;
}
.module-btn.secondary:hover {
    background-color: #d1d5db;
}

/* === Message Alert === */
.module-alert {
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
    text-align: center;
}
.module-alert.success {
    background-color: #d1fae5;
    color: #065f46;
}
.module-alert.error {
    background-color: #fee2e2;
    color: #991b1b;
}

/* === Table Style === */
.module-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}
.module-table th, .module-table td {
    border: 1px solid #e5e7eb;
    padding: 12px;
    text-align: left;
}
.module-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #111827;
}
.module-table tr:nth-child(even) {
    background-color: #f3f4f6;
}

/* === Action Buttons === */
.module-action {
    background: none;
    border: none;
    color: #3b82f6;
    font-weight: 500;
    cursor: pointer;
    margin-right: 10px;
}
.module-action.delete {
    color: #ef4444;
}
.module-action:hover {
    text-decoration: underline;
}

/* === Modal Styling === */
.modal-bg {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.25);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-bg.active {
    display: flex;
}
.modal-box {
    background-color: white;
    padding: 28px;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    width: 90%;
    max-width: 500px;
}

</style>

<div class="classic-section">
    <div style="margin-bottom:18px;">
        <a href="manage_courses.php" class="classic-link">&larr; Kembali ke Kelola Praktikum</a>
    </div>
    <div class="classic-title">Kelola Modul Praktikum: <?php echo htmlspecialchars($course_data['nama_praktikum']); ?></div>

    <?php if (!empty($message)): ?>
        <div class="classic-message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah Modul -->
    <form action="manage_modules.php?course_id=<?php echo $id_praktikum; ?>" method="post" enctype="multipart/form-data" style="margin-bottom:32px;">
        <div class="classic-label">Nama Modul <span style="color:#dc3545">*</span></div>
        <input type="text" name="nama_modul" class="classic-input" required>
        <div class="classic-label">File Materi (Opsional)</div>
        <input type="file" name="file_materi" class="classic-input">
        <div style="font-size:0.95rem; color:#6c757d; margin-bottom:12px;">Format: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR.</div>
        <button type="submit" name="tambah_modul" class="classic-btn">Tambah Modul</button>
    </form>

    <!-- Daftar Modul -->
    <div class="classic-title" style="font-size:1.1rem; margin-bottom:12px;">Daftar Modul</div>
    <?php if (!empty($modul_list)): ?>
        <table class="classic-table">
            <thead>
                <tr>
                    <th>Nama Modul</th>
                    <th>File Materi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modul_list as $modul): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($modul['nama_modul']); ?></td>
                        <td>
                            <?php if ($modul['nama_file_materi'] && $modul['path_file_materi']): ?>
                                <a href="../<?php echo htmlspecialchars($modul['path_file_materi']); ?>" download="<?php echo htmlspecialchars($modul['nama_file_materi']); ?>" class="classic-link">
                                    <?php echo htmlspecialchars($modul['nama_file_materi']); ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#adb5bd;">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="classic-action-btn" onclick="openEditModulModal(
                                <?php echo $modul['id']; ?>,
                                '<?php echo htmlspecialchars(addslashes($modul['nama_modul'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($modul['nama_file_materi'] ?? '')); ?>',
                                '<?php echo htmlspecialchars(addslashes($modul['path_file_materi'] ?? '')); ?>'
                            )">Edit</button>
                            <form action="manage_modules.php?course_id=<?php echo $id_praktikum; ?>" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus modul ini? Semua laporan terkait juga akan dihapus jika cascade diaktifkan.');">
                                <input type="hidden" name="id_modul_hapus" value="<?php echo $modul['id']; ?>">
                                <input type="hidden" name="path_file_materi_hapus" value="<?php echo htmlspecialchars($modul['path_file_materi'] ?? ''); ?>">
                                <button type="submit" name="hapus_modul" class="classic-action-btn delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="color:#6c757d;">Belum ada modul yang ditambahkan untuk praktikum ini.</div>
    <?php endif; ?>
</div>

<!-- Modal Edit Modul -->
<div id="editModulModalBg" class="classic-modal-bg">
    <div class="classic-modal">
        <form action="manage_modules.php?course_id=<?php echo $id_praktikum; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_modul_edit" id="edit_id_modul">
            <input type="hidden" name="path_file_materi_lama_edit" id="edit_path_file_materi_lama">
            <div class="classic-label">Nama Modul <span style="color:#dc3545">*</span></div>
            <input type="text" name="nama_modul_edit" id="edit_nama_modul" class="classic-input" required>
            <div class="classic-label">File Materi Baru (Opsional)</div>
            <input type="file" name="file_materi_edit" id="file_materi_edit" class="classic-input">
            <div style="font-size:0.95rem; color:#6c757d; margin-bottom:8px;">Kosongkan jika tidak ingin mengganti file materi. Format: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR.</div>
            <div id="current_file_display_edit_container" style="margin-bottom:12px;">
                <span style="font-size:0.95rem; color:#6c757d;">File saat ini: <a href="#" id="current_file_link_edit" class="classic-link" target="_blank"></a></span>
                <label for="hapus_file_materi_edit_checkbox" style="margin-left:12px; font-size:0.95rem;">
                    <input type="checkbox" name="hapus_file_materi_edit_checkbox" id="hapus_file_materi_edit_checkbox" value="1">
                    Hapus file materi saat ini
                </label>
            </div>
            <div style="margin-top:16px; text-align:right;">
                <button type="submit" name="edit_modul" class="classic-btn">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModulModal()" class="classic-btn" style="background:#adb5bd; color:#212529; margin-left:8px;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModulModal(id, nama, namaFile, pathFile) {
    document.getElementById('edit_id_modul').value = id;
    document.getElementById('edit_nama_modul').value = nama;
    document.getElementById('edit_path_file_materi_lama').value = pathFile;

    const fileLinkElement = document.getElementById('current_file_link_edit');
    const fileDisplayContainer = document.getElementById('current_file_display_edit_container');
    const deleteCheckbox = document.getElementById('hapus_file_materi_edit_checkbox');

    deleteCheckbox.checked = false;

    if (namaFile && pathFile) {
        fileLinkElement.textContent = namaFile;
        fileLinkElement.href = '../' + pathFile;
        fileDisplayContainer.style.display = '';
    } else {
        fileDisplayContainer.style.display = 'none';
        fileLinkElement.textContent = '';
        fileLinkElement.href = '#';
    }

    document.getElementById('file_materi_edit').value = '';
    document.getElementById('editModulModalBg').classList.add('active');
}
function closeEditModulModal() {
    document.getElementById('editModulModalBg').classList.remove('active');
}
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
