<?php
/**
 * index.php — Halaman utama BluePulse Student Panel
 * Menampilkan daftar siswa dengan filter kelas & kehadiran,
 * toggle tabel/kartu, statistik ringkas, dan inline attendance toggle.
 */
require_once 'config.php';

// ── Flash message dari session ────────────────────────────────────────────────
session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Ambil parameter filter (GET) ──────────────────────────────────────────────
$filterClass      = trim($_GET['class']      ?? '');
$filterAttendance = trim($_GET['attendance'] ?? '');

// ── Query daftar kelas unik (untuk dropdown filter & form) ───────────────────
$stmtClasses = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class ASC");
$classes     = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

// ── Query siswa dengan filter ─────────────────────────────────────────────────
$where  = [];
$params = [];

if ($filterClass !== '') {
    $where[]            = 'class = :class';
    $params[':class']   = $filterClass;
}
if ($filterAttendance !== '') {
    $where[]                = 'attendance = :attendance';
    $params[':attendance']  = $filterAttendance;
}

$sql = 'SELECT * FROM students';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY class ASC, name ASC';

$stmtStudents = $pdo->prepare($sql);
$stmtStudents->execute($params);
$students = $stmtStudents->fetchAll();

// ── Statistik ─────────────────────────────────────────────────────────────────
$stmtStats = $pdo->query("SELECT
    COUNT(*) AS total,
    SUM(attendance = 'Hadir') AS hadir,
    SUM(attendance = 'Tidak') AS tidak
    FROM students");
$stats = $stmtStats->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BluePulse Student Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>

<!-- ── Loading Spinner ──────────────────────────────────────────── -->
<div id="page-spinner">
    <div class="spinner-wrap">
        <div class="spinner-ring"></div>
        <div class="spinner-ring ring2"></div>
    </div>
    <p class="spinner-text">Memuat BluePulse…</p>
</div>

<!-- ── Lightbox ─────────────────────────────────────────────────── -->
<div id="lightbox">
    <button id="lightbox-close">✕ Tutup</button>
    <img id="lightbox-img" src="" alt="Foto Siswa">
</div>

<!-- ── Topbar ───────────────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-logo">
            <div class="logo-icon">⚡</div>
            <span>Blue<span>Pulse</span></span>
            <span class="brand-sub" style="color:var(--text-muted);font-weight:400;font-size:0.8rem">Student Panel</span>
        </div>
        <nav class="topbar-nav">
            <a href="index.php" class="nav-btn active">🏠 Dashboard</a>
            <a href="tambah.php" class="nav-btn">➕ Tambah Siswa</a>
        </nav>
    </div>
</header>

<!-- ── Main ─────────────────────────────────────────────────────── -->
<main class="page-wrapper">

    <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" data-autohide="1">
        <?= e($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Judul halaman -->
    <div class="page-title">
        <div>
            <h1>Data Siswa</h1>
            <p>Manajemen kehadiran dan data seluruh siswa</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">➕ Tambah Siswa</a>
    </div>

    <!-- Statistik -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue">👥</div>
            <div>
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div>
                <div class="stat-value" style="color:var(--green)"><?= $stats['hadir'] ?></div>
                <div class="stat-label">Hadir Hari Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">❌</div>
            <div>
                <div class="stat-value" style="color:var(--red)"><?= $stats['tidak'] ?></div>
                <div class="stat-label">Tidak Hadir</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">📚</div>
            <div>
                <div class="stat-value"><?= count($classes) ?></div>
                <div class="stat-label">Kelas Aktif</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="index.php" class="filter-bar show-spinner">
        <div class="filter-group">
            <label>Filter Kelas</label>
            <select name="class" class="form-select auto-submit">
                <option value="">Semua Kelas</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= e($c) ?>" <?= $filterClass === $c ? 'selected' : '' ?>>
                    <?= e($c) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Status Kehadiran</label>
            <select name="attendance" class="form-select auto-submit">
                <option value="">Semua Status</option>
                <option value="Hadir"  <?= $filterAttendance === 'Hadir'  ? 'selected' : '' ?>>✅ Hadir</option>
                <option value="Tidak"  <?= $filterAttendance === 'Tidak'  ? 'selected' : '' ?>>❌ Tidak Hadir</option>
            </select>
        </div>
        <div class="filter-group" style="flex:0;min-width:auto">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
        </div>
        <?php if ($filterClass || $filterAttendance): ?>
        <div class="filter-group" style="flex:0;min-width:auto">
            <label>&nbsp;</label>
            <a href="index.php" class="btn btn-secondary">✕ Reset</a>
        </div>
        <?php endif; ?>
    </form>

    <!-- Card wrapper: header + content -->
    <div class="card">
        <div class="card-header">
            <h2>
                Daftar Siswa
                <span style="color:var(--text-muted);font-weight:400;font-size:0.8rem;margin-left:0.5rem">
                    (<?= count($students) ?> data)
                </span>
            </h2>
            <div class="view-toggle">
                <button id="btn-view-table" class="btn btn-secondary btn-sm active" title="Mode Tabel">
                    ☰ Tabel
                </button>
                <button id="btn-view-cards" class="btn btn-secondary btn-sm" title="Mode Kartu">
                    ⊞ Kartu
                </button>
            </div>
        </div>

        <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>Tidak ada data ditemukan</h3>
            <p>Coba ubah filter atau <a href="tambah.php">tambah siswa baru</a>.</p>
        </div>

        <?php else: ?>

        <!-- ── VIEW: TABEL ──────────────────────────────────────── -->
        <div id="view-table">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="td-photo">Foto</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Kehadiran</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $i => $s): ?>
                        <tr>
                            <td style="color:var(--text-muted)"><?= $i + 1 ?></td>

                            <!-- Foto thumbnail -->
                            <td class="td-photo">
                                <?php if ($s['photo']): ?>
                                <div class="thumb-frame"
                                     data-lightbox="<?= e(UPLOAD_URL . $s['photo']) ?>"
                                     title="Klik untuk perbesar">
                                    <img src="<?= e(UPLOAD_URL . $s['photo']) ?>"
                                         alt="Foto <?= e($s['name']) ?>">
                                </div>
                                <?php else: ?>
                                <div class="thumb-frame"><span class="no-photo">📷</span></div>
                                <?php endif; ?>
                            </td>

                            <td style="color:var(--text-muted)"><?= e($s['nis'] ?: '—') ?></td>
                            <td style="font-weight:600"><?= e($s['name']) ?></td>
                            <td>
                                <span style="background:rgba(59,130,246,0.12);color:var(--blue-light);
                                             border:1px solid rgba(59,130,246,0.25);border-radius:20px;
                                             padding:0.2rem 0.65rem;font-size:0.72rem;font-weight:600;">
                                    <?= e($s['class']) ?>
                                </span>
                            </td>

                            <!-- Inline toggle kehadiran -->
                            <td>
                                <form method="POST" action="proses_edit.php" class="attendance-form">
                                    <input type="hidden" name="id"         value="<?= (int)$s['id'] ?>">
                                    <input type="hidden" name="inline"     value="1">
                                    <input type="hidden" name="name"       value="<?= e($s['name']) ?>">
                                    <input type="hidden" name="class"      value="<?= e($s['class']) ?>">
                                    <input type="hidden" name="nis"        value="<?= e($s['nis']) ?>">
                                    <input type="hidden" name="address"    value="<?= e($s['address']) ?>">
                                    <select name="attendance" class="attendance-select"
                                            style="color:<?= $s['attendance']==='Hadir' ? 'var(--green)' : 'var(--red)' ?>">
                                        <option value="Hadir" <?= $s['attendance']==='Hadir' ? 'selected':'' ?>>✅ Hadir</option>
                                        <option value="Tidak" <?= $s['attendance']==='Tidak' ? 'selected':'' ?>>❌ Tidak</option>
                                    </select>
                                </form>
                            </td>

                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary)">
                                <?= e($s['address'] ?: '—') ?>
                            </td>

                            <td class="actions">
                                <a href="edit.php?id=<?= (int)$s['id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                                <a href="hapus.php?id=<?= (int)$s['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Hapus siswa <?= e($s['name']) ?>? Tindakan ini tidak dapat dibatalkan.">
                                   🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div><!-- /view-table -->

        <!-- ── VIEW: KARTU ──────────────────────────────────────── -->
        <div id="view-cards" style="display:none;padding:1.5rem;">
            <div class="cards-grid">
            <?php foreach ($students as $s): ?>
                <div class="student-card">
                    <div class="card-photo">
                        <?php if ($s['photo']): ?>
                        <img src="<?= e(UPLOAD_URL . $s['photo']) ?>"
                             alt="Foto <?= e($s['name']) ?>"
                             data-lightbox="<?= e(UPLOAD_URL . $s['photo']) ?>"
                             style="cursor:zoom-in">
                        <?php else: ?>
                        <span class="no-photo-big">📷</span>
                        <?php endif; ?>
                        <div class="card-status">
                            <span class="badge <?= $s['attendance']==='Hadir' ? 'badge-hadir':'badge-tidak' ?>">
                                <span class="badge-dot"></span>
                                <?= e($s['attendance']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-info">
                        <div class="card-name"><?= e($s['name']) ?></div>
                        <div class="card-class">📚 <?= e($s['class']) ?></div>
                        <div class="card-nis">🪪 NIS: <?= e($s['nis'] ?: '—') ?></div>
                        <div class="card-actions">
                            <a href="edit.php?id=<?= (int)$s['id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                            <a href="hapus.php?id=<?= (int)$s['id'] ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Hapus siswa <?= e($s['name']) ?>?">🗑️</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div><!-- /view-cards -->

        <?php endif; ?>
    </div><!-- /card -->

</main>

<!-- ── Footer ────────────────────────────────────────────────────── -->
<footer style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:0.75rem;border-top:1px solid var(--border)">
    ⚡ BluePulse Student Panel &nbsp;·&nbsp; <?= date('Y') ?>
</footer>

<script src="script.js"></script>
</body>
</html>