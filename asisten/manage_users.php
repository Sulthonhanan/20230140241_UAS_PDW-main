<?php
$pageTitle = 'Kelola Pengguna';
$activePage = 'manage_users';
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['tambah_pengguna'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        if (empty($nama) || empty($email) || empty($password) || empty($role)) {
            $message = "Semua kolom (Nama, Email, Password, Peran) wajib diisi.";
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Format email tidak valid.";
            $message_type = 'error';
        } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
            $message = "Peran tidak valid.";
            $message_type = 'error';
        } else {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $message = "Email sudah terdaftar. Gunakan email lain.";
                $message_type = 'error';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt_insert = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);
                if ($stmt_insert->execute()) {
                    $message = "Pengguna baru berhasil ditambahkan.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menambahkan pengguna: " . $stmt_insert->error;
                    $message_type = 'error';
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
        }
    }

    elseif (isset($_POST['edit_pengguna'])) {
        $id_user = (int)$_POST['id_user_edit'];
        $nama = trim($_POST['nama_edit']);
        $email = trim($_POST['email_edit']);
        $password = trim($_POST['password_edit']);
        $role = trim($_POST['role_edit']);

        if (empty($nama) || empty($email) || empty($role) || $id_user <= 0) {
            $message = "Nama, Email, Peran, dan ID Pengguna valid wajib diisi.";
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Format email tidak valid.";
            $message_type = 'error';
        } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
            $message = "Peran tidak valid.";
            $message_type = 'error';
        } else {
            $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check_email->bind_param("si", $email, $id_user);
            $stmt_check_email->execute();
            if ($stmt_check_email->get_result()->num_rows > 0) {
                $message = "Email sudah digunakan oleh pengguna lain.";
                $message_type = 'error';
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt_update = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt_update->bind_param("ssssi", $nama, $email, $hashed_password, $role, $id_user);
                } else {
                    $stmt_update = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
                    $stmt_update->bind_param("sssi", $nama, $email, $role, $id_user);
                }

                if ($stmt_update->execute()) {
                    $message = "Data pengguna berhasil diperbarui.";
                    $message_type = 'success';
                    if ($id_user == $_SESSION['user_id'] && $_SESSION['nama'] != $nama) {
                        $_SESSION['nama'] = $nama;
                        echo "<script>document.querySelector('aside .text-gray-400').textContent = '".htmlspecialchars(addslashes($nama))."';</script>";
                    }
                } else {
                    $message = "Gagal memperbarui data pengguna: " . $stmt_update->error;
                    $message_type = 'error';
                }
                $stmt_update->close();
            }
            $stmt_check_email->close();
        }
    }
    elseif (isset($_POST['hapus_pengguna'])) {
        $id_user_hapus = (int)$_POST['id_user_hapus'];

        if ($id_user_hapus > 0) {
            if ($id_user_hapus == $_SESSION['user_id']) {
                $message = "Anda tidak dapat menghapus akun Anda sendiri.";
                $message_type = 'error';
            } else {
                $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt_delete->bind_param("i", $id_user_hapus);
                if ($stmt_delete->execute()) {
                    $message = "Pengguna berhasil dihapus.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menghapus pengguna: " . $stmt_delete->error . ". Pastikan tidak ada data terkait yang mencegah penghapusan.";
                    $message_type = 'error';
                }
                $stmt_delete->close();
            }
        }
    }
}

$users_list = [];
$result_users = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY nama ASC");
if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users_list[] = $row;
    }
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: #f8fafc;
    margin: 0;
    padding: 0;
    color: #1f2937;
}

.classic-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 36px 28px;
    margin: 40px auto;
    max-width: 920px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
}

.classic-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 28px;
}

.classic-label {
    font-size: 0.95rem;
    color: #374151;
    margin-bottom: 6px;
    font-weight: 500;
}

.classic-input,
.classic-textarea,
.classic-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    background-color: #f9fafb;
    margin-bottom: 16px;
    transition: border-color 0.2s ease;
}

.classic-input:focus,
.classic-select:focus,
.classic-textarea:focus {
    border-color: #3b82f6;
    outline: none;
    background-color: #fff;
}

.classic-btn {
    background-color: #2563eb;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.25s ease;
}

.classic-btn:hover {
    background-color: #1d4ed8;
}

.classic-message {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    text-align: center;
    font-weight: 500;
    font-size: 0.95rem;
}

.classic-message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.classic-message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #f87171;
}

.classic-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 0.95rem;
}

.classic-table th,
.classic-table td {
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    text-align: left;
}

.classic-table th {
    background: #f3f4f6;
    font-weight: 600;
    color: #1f2937;
}

.classic-table tr:nth-child(even) {
    background: #f9fafb;
}

.classic-action-btn {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    font-size: 0.95rem;
    margin-right: 10px;
    padding: 0;
}

.classic-action-btn.delete {
    color: #dc2626;
}

.classic-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.classic-link:hover {
    text-decoration: underline;
}

.classic-modal-bg {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.25);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}

.classic-modal-bg.active {
    display: flex;
}

.classic-modal {
    background: #ffffff;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    min-width: 340px;
    max-width: 95vw;
}

</style>

<div class="classic-section">
    <div class="classic-title">Kelola Pengguna</div>

    <?php if (!empty($message)): ?>
        <div class="classic-message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah Pengguna -->
    <form action="manage_users.php" method="post" style="margin-bottom:32px;">
        <div class="classic-label">Nama Lengkap <span style="color:#dc3545">*</span></div>
        <input type="text" name="nama" class="classic-input" required>
        <div class="classic-label">Email <span style="color:#dc3545">*</span></div>
        <input type="email" name="email" class="classic-input" required>
        <div class="classic-label">Password <span style="color:#dc3545">*</span></div>
        <input type="password" name="password" class="classic-input" required>
        <div class="classic-label">Peran <span style="color:#dc3545">*</span></div>
        <select name="role" class="classic-select" required>
            <option value="mahasiswa">Mahasiswa</option>
            <option value="asisten">Asisten</option>
        </select>
        <button type="submit" name="tambah_pengguna" class="classic-btn">Tambah Pengguna</button>
    </form>

    <!-- Daftar Pengguna -->
    <div class="classic-title" style="font-size:1.1rem; margin-bottom:12px;">Daftar Pengguna Terdaftar</div>
    <?php if (!empty($users_list)): ?>
        <table class="classic-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_list as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span style="font-weight:600; color:<?php echo $user['role'] == 'asisten' ? '#198754' : '#0d6efd'; ?>">
                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button type="button" class="classic-action-btn" onclick="openEditUserModal(
                                <?php echo $user['id']; ?>,
                                '<?php echo htmlspecialchars(addslashes($user['nama'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($user['email'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($user['role'])); ?>'
                            )">Edit</button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form action="manage_users.php" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini? Semua data terkait juga akan dihapus.');">
                                <input type="hidden" name="id_user_hapus" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="hapus_pengguna" class="classic-action-btn delete">Hapus</button>
                            </form>
                            <?php else: ?>
                                <span style="color:#adb5bd; cursor:not-allowed;">Hapus</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="color:#6c757d;">Belum ada pengguna yang terdaftar.</div>
    <?php endif; ?>
</div>

<!-- Modal Edit Pengguna -->
<div id="editUserModalBg" class="classic-modal-bg">
    <div class="classic-modal">
        <form action="manage_users.php" method="post">
            <input type="hidden" name="id_user_edit" id="edit_id_user">
            <div class="classic-label">Nama Lengkap <span style="color:#dc3545">*</span></div>
            <input type="text" name="nama_edit" id="edit_nama" class="classic-input" required>
            <div class="classic-label">Email <span style="color:#dc3545">*</span></div>
            <input type="email" name="email_edit" id="edit_email" class="classic-input" required>
            <div class="classic-label">Password Baru (Opsional)</div>
            <input type="password" name="password_edit" id="edit_password" class="classic-input" placeholder="Kosongkan jika tidak ingin mengubah password">
            <div class="classic-label">Peran <span style="color:#dc3545">*</span></div>
            <select name="role_edit" id="edit_role" class="classic-select" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
            <div style="margin-top:16px; text-align:right;">
                <button type="submit" name="edit_pengguna" class="classic-btn">Simpan Perubahan</button>
                <button type="button" onclick="closeEditUserModal()" class="classic-btn" style="background:#adb5bd; color:#212529; margin-left:8px;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(id, nama, email, role) {
    document.getElementById('edit_id_user').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_password').value = '';
    document.getElementById('editUserModalBg').classList.add('active');
}
function closeEditUserModal() {
    document.getElementById('editUserModalBg').classList.remove('active');
}
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
