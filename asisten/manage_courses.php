<?php
$pageTitle = 'Kelola Praktikum';
$activePage = 'manage_courses';
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tambah_praktikum'])) {
        $nama_praktikum = trim($_POST['nama_praktikum']);
        $deskripsi = trim($_POST['deskripsi']);

        if (!empty($nama_praktikum)) {
            $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_praktikum, $deskripsi);
            if ($stmt->execute()) {
                $message = "Praktikum berhasil ditambahkan.";
                $message_type = 'success';
            } else {
                $message = "Gagal menambahkan praktikum: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Nama praktikum tidak boleh kosong.";
            $message_type = 'error';
        }
    }
    elseif (isset($_POST['edit_praktikum'])) {
        $id_praktikum = (int)$_POST['id_praktikum'];
        $nama_praktikum = trim($_POST['nama_praktikum_edit']);
        $deskripsi = trim($_POST['deskripsi_edit']);

        if (!empty($nama_praktikum) && $id_praktikum > 0) {
            $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $id_praktikum);
            if ($stmt->execute()) {
                $message = "Praktikum berhasil diperbarui.";
                $message_type = 'success';
            } else {
                $message = "Gagal memperbarui praktikum: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Nama praktikum tidak boleh kosong dan ID harus valid.";
            $message_type = 'error';
        }
    }
    elseif (isset($_POST['hapus_praktikum'])) {
        $id_praktikum = (int)$_POST['id_praktikum_hapus'];
        if ($id_praktikum > 0) {
            $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
            $stmt->bind_param("i", $id_praktikum);
            if ($stmt->execute()) {
                $message = "Praktikum berhasil dihapus.";
                $message_type = 'success';
            } else {
                $message = "Gagal menghapus praktikum: " . $stmt->error . ". Pastikan tidak ada modul atau mahasiswa yang terdaftar pada praktikum ini.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

$mata_praktikum_list = [];
$result = $conn->query("SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mata_praktikum_list[] = $row;
    }
}
?>

<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }

    .classic-section {
        background: #ffffff;
        border-radius: 16px;
        padding: 32px;
        margin: 40px auto;
        max-width: 900px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .classic-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 24px;
    }

    .classic-label {
        font-size: 1rem;
        color: #444;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .classic-input,
    .classic-textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d0d7de;
        border-radius: 10px;
        font-size: 1rem;
        background: #f9fafb;
        transition: border 0.2s, box-shadow 0.2s;
        margin-bottom: 16px;
    }

    .classic-input:focus,
    .classic-textarea:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        outline: none;
    }

    .classic-btn {
        background-color: #007bff;
        color: #fff;
        padding: 10px 24px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: background-color 0.2s, transform 0.2s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .classic-btn:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }

    .classic-message {
        padding: 14px 20px;
        border-radius: 10px;
        margin-bottom: 24px;
        font-weight: 500;
        text-align: center;
    }

    .classic-message.success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .classic-message.error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .classic-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        background-color: #fff;
        border-radius: 12px;
        overflow: hidden;
    }

    .classic-table th,
    .classic-table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e1e4e8;
    }

    .classic-table th {
        background-color: #f6f8fa;
        font-weight: 600;
        color: #333;
    }

    .classic-table tr:hover {
        background-color: #f1f5f9;
    }

    .classic-action-btn {
        background: none;
        border: none;
        color: #007bff;
        font-size: 0.95rem;
        cursor: pointer;
        margin-right: 12px;
        transition: color 0.2s;
    }

    .classic-action-btn:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .classic-action-btn.delete {
        color: #dc3545;
    }

    .classic-action-btn.delete:hover {
        color: #a71d2a;
    }

    .classic-action-btn.manage {
        color: #198754;
    }

    .classic-modal-bg {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.25);
        z-index: 999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .classic-modal-bg.active {
        display: flex;
    }

    .classic-modal {
        background: #fff;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 450px;
    }

    .classic-modal .classic-btn {
        margin-top: 12px;
    }

    .classic-link {
        color: #007bff;
        text-decoration: none;
    }

    .classic-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .classic-section {
            padding: 24px 16px;
            margin: 20px 12px;
        }
        .classic-modal {
            padding: 24px;
        }
    }
</style>


<div class="classic-section">
    <div class="classic-title">Kelola Praktikum</div>

    <?php if (!empty($message)): ?>
        <div class="classic-message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah Praktikum -->
    <form action="manage_courses.php" method="post" style="margin-bottom:32px;">
        <div class="classic-label">Nama Praktikum <span style="color:#dc3545">*</span></div>
        <input type="text" name="nama_praktikum" class="classic-input" required>
        <div class="classic-label">Deskripsi</div>
        <textarea name="deskripsi" rows="2" class="classic-textarea"></textarea>
        <button type="submit" name="tambah_praktikum" class="classic-btn">Tambah Praktikum</button>
    </form>

    <!-- Daftar Praktikum -->
    <div class="classic-title" style="font-size:1.1rem; margin-bottom:12px;">Daftar Praktikum</div>
    <?php if (!empty($mata_praktikum_list)): ?>
        <table class="classic-table">
            <thead>
                <tr>
                    <th>Nama Praktikum</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mata_praktikum_list as $praktikum): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'] ?? '-')); ?></td>
                        <td>
                            <button type="button" class="classic-action-btn" onclick="openEditModal(<?php echo $praktikum['id']; ?>, '<?php echo htmlspecialchars(addslashes($praktikum['nama_praktikum'])); ?>', '<?php echo htmlspecialchars(addslashes($praktikum['deskripsi'] ?? '')); ?>')">Edit</button>
                            <form action="manage_courses.php" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus praktikum ini? Semua modul terkait juga akan dihapus.');">
                                <input type="hidden" name="id_praktikum_hapus" value="<?php echo $praktikum['id']; ?>">
                                <button type="submit" name="hapus_praktikum" class="classic-action-btn delete">Hapus</button>
                            </form>
                            <a href="manage_modules.php?course_id=<?php echo $praktikum['id']; ?>" class="classic-action-btn manage">Kelola Modul</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="color:#6c757d;">Belum ada praktikum yang ditambahkan.</div>
    <?php endif; ?>
</div>

<!-- Modal Edit Praktikum -->
<div id="editModalBg" class="classic-modal-bg">
    <div class="classic-modal">
        <form action="manage_courses.php" method="post">
            <input type="hidden" name="id_praktikum" id="edit_id_praktikum">
            <div class="classic-label">Nama Praktikum <span style="color:#dc3545">*</span></div>
            <input type="text" name="nama_praktikum_edit" id="edit_nama_praktikum" class="classic-input" required>
            <div class="classic-label">Deskripsi</div>
            <textarea name="deskripsi_edit" id="edit_deskripsi" rows="2" class="classic-textarea"></textarea>
            <div style="margin-top:16px; text-align:right;">
                <button type="submit" name="edit_praktikum" class="classic-btn">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModal()" class="classic-btn" style="background:#adb5bd; color:#212529; margin-left:8px;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nama, deskripsi) {
    document.getElementById('edit_id_praktikum').value = id;
    document.getElementById('edit_nama_praktikum').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi;
    document.getElementById('editModalBg').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModalBg').classList.remove('active');
}
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
