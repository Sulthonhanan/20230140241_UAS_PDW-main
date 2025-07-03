<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dasbor'; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            background: #f4f6fb;
            margin: 0;
        }
        .classic-sidebar {
            background: #f8f9fa;
            border-right: 1px solid #d1d5db;
            width: 250px;
            min-width: 220px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .classic-sidebar-header {
            padding: 28px 0 18px 0;
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            background: #fff;
        }
        .classic-sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: bold;
            color: #212529;
            margin-bottom: 4px;
        }
        .classic-sidebar-header p {
            font-size: 1rem;
            color: #6c757d;
        }
        .classic-nav {
            flex: 1;
            padding: 24px 0;
        }
        .classic-nav ul {
            list-style: none;
            padding: 0 0 0 0;
            margin: 0;
        }
        .classic-nav li {
            margin-bottom: 8px;
        }
        .classic-link-nav {
            display: flex;
            align-items: center;
            padding: 12px 28px;
            border-radius: 6px;
            color: #343a40;
            text-decoration: none;
            font-size: 1.05rem;
            transition: background 0.18s, color 0.18s;
        }
        .classic-link-nav.active, .classic-link-nav:hover {
            background: #e9ecef;
            color: #0d6efd;
            font-weight: bold;
        }
        .classic-link-nav svg {
            margin-right: 12px;
            color: #adb5bd;
        }
        .classic-link-nav.active svg, .classic-link-nav:hover svg {
            color: #0d6efd;
        }
        .classic-logout-btn {
            margin: 24px 28px 28px 28px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 0;
            width: 100%;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .classic-logout-btn:hover {
            background: #c0392b;
        }
        .classic-main {
            flex: 1;
            padding: 36px 36px 36px 36px;
            min-height: 100vh;
        }
        @media (max-width: 900px) {
            .classic-main { padding: 18px 6vw; }
            .classic-sidebar { min-width: 0; width: 100vw; }
        }
    </style>
</head>
<body>
<div style="display:flex; min-height:100vh;">
    <aside class="classic-sidebar">
        <div class="classic-sidebar-header">
            <h3>Panel Mahasiswa</h3>
            <p><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Mahasiswa'); ?></p>
        </div>
        <nav class="classic-nav">
            <ul>
                <?php
                    $activeClass = 'classic-link-nav active';
                    $inactiveClass = 'classic-link-nav';
                ?>
                <li>
                    <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
                        Dasbor
                    </a>
                </li>
                <li>
                    <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Praktikum Saya
                    </a>
                </li>
                <li>
                    <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        Cari Praktikum
                    </a>
                </li>
                <li>
                    <a href="my_courses.php" class="<?php echo ($activePage == 'my_reports') ? $activeClass : $inactiveClass; ?>">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 2v4M16 2v4M4 10h16"/></svg>
                        Laporan Saya
                    </a>
                </li>
            </ul>
        </nav>
        <a href="../logout.php" class="classic-logout-btn">Keluar</a>
    </aside>
    <main class="classic-main">
        <header style="margin-bottom:32px;">
            <h1 style="font-size:2rem; font-weight:bold; color:#232526;"><?php echo $pageTitle ?? 'Dasbor'; ?></h1>