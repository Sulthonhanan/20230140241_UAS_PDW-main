    <?php
    $pageTitle = 'Kelola Laporan Masuk';
    $activePage = 'laporan'; 
    require_once '../config.php';
    require_once 'templates/header.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
        header("Location: ../login.php");
        exit();
    }

    $filter_praktikum_id = isset($_GET['filter_praktikum']) ? (int)$_GET['filter_praktikum'] : null;
    $filter_modul_id = isset($_GET['filter_modul']) ? (int)$_GET['filter_modul'] : null;
    $filter_mahasiswa_id = isset($_GET['filter_mahasiswa']) ? (int)$_GET['filter_mahasiswa'] : null;
    $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : null; 

    $praktikum_list_filter = [];
    $result_praktikum_filter = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC");
    if ($result_praktikum_filter) {
        while ($row = $result_praktikum_filter->fetch_assoc()) {
            $praktikum_list_filter[] = $row;
        }
    }

    $modul_list_filter = [];
    if ($filter_praktikum_id) {
        $stmt_modul_filter = $conn->prepare("SELECT id, nama_modul FROM modul WHERE id_praktikum = ? ORDER BY nama_modul ASC");
        $stmt_modul_filter->bind_param("i", $filter_praktikum_id);
        $stmt_modul_filter->execute();
        $result_modul_filter = $stmt_modul_filter->get_result();
        if ($result_modul_filter) {
            while ($row = $result_modul_filter->fetch_assoc()) {
                $modul_list_filter[] = $row;
            }
        }
        $stmt_modul_filter->close();
    }

    $mahasiswa_list_filter = [];
    $result_mahasiswa_filter = $conn->query(
        "SELECT DISTINCT u.id, u.nama
        FROM users u
        JOIN laporan_praktikum lp ON u.id = lp.id_mahasiswa
        WHERE u.role = 'mahasiswa' ORDER BY u.nama ASC"
    );
    if ($result_mahasiswa_filter) {
        while ($row = $result_mahasiswa_filter->fetch_assoc()) {
            $mahasiswa_list_filter[] = $row;
        }
    }

    $sql = "SELECT lp.id, lp.tanggal_kumpul, lp.nilai, lp.tanggal_dinilai,
                u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
                m.nama_modul,
                mp.nama_praktikum
            FROM laporan_praktikum lp
            JOIN users u ON lp.id_mahasiswa = u.id
            JOIN modul m ON lp.id_modul = m.id
            JOIN mata_praktikum mp ON m.id_praktikum = mp.id
            WHERE 1=1";

    $params = [];
    $types = "";

    if ($filter_praktikum_id) {
        $sql .= " AND mp.id = ?";
        $params[] = $filter_praktikum_id;
        $types .= "i";
    }
    if ($filter_modul_id) {
        $sql .= " AND m.id = ?";
        $params[] = $filter_modul_id;
        $types .= "i";
    }
    if ($filter_mahasiswa_id) {
        $sql .= " AND u.id = ?";
        $params[] = $filter_mahasiswa_id;
        $types .= "i";
    }
    if ($filter_status === 'dinilai') {
        $sql .= " AND lp.nilai IS NOT NULL";
    } elseif ($filter_status === 'belum_dinilai') {
        $sql .= " AND lp.nilai IS NULL";
    }

    $sql .= " ORDER BY lp.tanggal_kumpul DESC";

    $stmt_laporan = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt_laporan->bind_param($types, ...$params);
    }
    $stmt_laporan->execute();
    $result_laporan = $stmt_laporan->get_result();
    $laporan_list = [];
    if ($result_laporan && $result_laporan->num_rows > 0) {
        while ($row = $result_laporan->fetch_assoc()) {
            $laporan_list[] = $row;
        }
    }
    $stmt_laporan->close();
    ?>

   <style>
:root {
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --gray: #f3f4f6;
    --dark: #1f2937;
    --border: #d1d5db;
    --text: #111827;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gray);
    color: var(--text);
    margin: 0;
    padding: 0;
}

.classic-section {
    background: white;
    padding: 32px;
    margin: 40px auto;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    max-width: 1100px;
}

.classic-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--dark);
}

.classic-label {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--dark);
}

.classic-input, .classic-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.95rem;
    margin-bottom: 14px;
    background: #fff;
    transition: border 0.2s ease;
}

.classic-input:focus, .classic-select:focus {
    border-color: var(--primary);
    outline: none;
}

.classic-btn {
    padding: 10px 20px;
    background-color: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.classic-btn:hover {
    background-color: var(--primary-hover);
}

.classic-btn.secondary {
    background-color: #e5e7eb;
    color: #111827;
}

.classic-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 0.95rem;
}

.classic-table th, .classic-table td {
    padding: 12px 16px;
    border: 1px solid var(--border);
    text-align: left;
}

.classic-table th {
    background-color: var(--gray);
    font-weight: 700;
    color: var(--dark);
}

.classic-table tr:nth-child(even) {
    background-color: #f9fafb;
}

.classic-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.classic-link:hover {
    text-decoration: underline;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.status-dinilai {
    background: #d1fae5;
    color: var(--success);
}

.status-belum {
    background: #fef3c7;
    color: var(--warning);
}

.email-secondary {
    font-size: 0.85rem;
    color: #6b7280;
}

.action-btn {
    padding: 6px 12px;
    font-size: 0.85rem;
    border: none;
    border-radius: 4px;
    background: var(--primary);
    color: white;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}

.action-btn:hover {
    background: var(--primary-hover);
}
</style>


    <div class="classic-section">
        <div class="classic-title">Kelola Laporan Masuk</div>

        <!-- Filter Form -->
        <form action="submitted_reports.php" method="get" style="margin-bottom:28px;">
            <div style="display:flex; flex-wrap:wrap; gap:18px;">
                <div style="flex:1; min-width:180px;">
                    <div class="classic-label">Praktikum:</div>
                    <select name="filter_praktikum" id="filter_praktikum" class="classic-select" onchange="this.form.submit()">
                        <option value="">Semua Praktikum</option>
                        <?php foreach ($praktikum_list_filter as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum_id == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:180px;">
                    <div class="classic-label">Modul:</div>
                    <select name="filter_modul" id="filter_modul" class="classic-select" <?php if (!$filter_praktikum_id && empty($modul_list_filter)) echo 'disabled'; ?>>
                        <option value="">Semua Modul</option>
                        <?php foreach ($modul_list_filter as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo ($filter_modul_id == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['nama_modul']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:180px;">
                    <div class="classic-label">Mahasiswa:</div>
                    <select name="filter_mahasiswa" id="filter_mahasiswa" class="classic-select">
                        <option value="">Semua Mahasiswa</option>
                        <?php foreach ($mahasiswa_list_filter as $mahasiswa): ?>
                            <option value="<?php echo $mahasiswa['id']; ?>" <?php echo ($filter_mahasiswa_id == $mahasiswa['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:180px;">
                    <div class="classic-label">Status Penilaian:</div>
                    <select name="filter_status" id="filter_status" class="classic-select">
                        <option value="">Semua Status</option>
                        <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
                        <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:18px; text-align:right;">
                <a href="submitted_reports.php" class="classic-btn" style="background:#adb5bd; color:#212529; margin-right:8px;">Reset Filter</a>
                <button type="submit" class="classic-btn">Filter</button>
            </div>
        </form>

        <!-- Daftar Laporan -->
        <div class="classic-title" style="font-size:1.1rem; margin-bottom:12px;">Daftar Laporan Masuk</div>
        <?php if (!empty($laporan_list)): ?>
            <div style="overflow-x:auto;">
                <table class="classic-table">
                    <thead>
                        <tr>
                            <th>Praktikum</th>
                            <th>Modul</th>
                            <th>Mahasiswa</th>
                            <th>Tanggal Kumpul</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_list as $laporan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></td>
                                <td><?php echo htmlspecialchars($laporan['nama_modul']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?><br>
                                    <span style="font-size:0.95rem; color:#6c757d;"><?php echo htmlspecialchars($laporan['email_mahasiswa']); ?></span>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></td>
                                <td>
                                    <?php if ($laporan['nilai'] !== null): ?>
                                        <span style="background:#d1e7dd; color:#0f5132; border-radius:4px; padding:2px 8px; font-size:0.98rem; font-weight:500;">
                                            Sudah Dinilai (<?php echo htmlspecialchars($laporan['nilai']); ?>)
                                        </span>
                                        <br><span style="font-size:0.92rem; color:#6c757d;">Tanggal: <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_dinilai'])); ?></span>
                                    <?php else: ?>
                                        <span style="background:#fff3cd; color:#856404; border-radius:4px; padding:2px 8px; font-size:0.98rem; font-weight:500;">
                                            Belum Dinilai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="grade_report.php?id=<?php echo $laporan['id']; ?>" class="classic-link">
                                        <?php echo ($laporan['nilai'] !== null) ? 'Lihat/Edit Nilai' : 'Nilai Laporan'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="color:#6c757d;">Tidak ada laporan yang sesuai filter, atau belum ada laporan yang masuk.</div>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('filter_praktikum').addEventListener('change', function() {
        var praktikumId = this.value;
        var modulSelect = document.getElementById('filter_modul');
        var currentModulValue = modulSelect.value;

        modulSelect.innerHTML = '<option value="">Memuat modul...</option>'; 
        modulSelect.disabled = true;

        if (praktikumId) {
            fetch('ajax_get_modules.php?praktikum_id=' + praktikumId) 
                .then(response => response.json())
                .then(data => {
                    modulSelect.innerHTML = '<option value="">Semua Modul</option>';  
                    if (data.length > 0) {
                        data.forEach(function(modul) {
                            var option = document.createElement('option');
                            option.value = modul.id;
                            option.textContent = modul.nama_modul;
                            if (modul.id == currentModulValue) {
                                option.selected = true;
                            }
                            modulSelect.appendChild(option);
                        });
                        modulSelect.disabled = false;
                    } else {
                        modulSelect.innerHTML = '<option value="">Pilih Praktikum Terlebih Dahulu</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching modules:', error);
                    modulSelect.innerHTML = '<option value="">Gagal memuat modul</option>';
                });
        } else {
            modulSelect.innerHTML = '<option value="">Pilih Praktikum Terlebih Dahulu</option>';
        }
    });
    </script>

    <?php
    $conn->close();
    require_once 'templates/footer.php';
    ?>
