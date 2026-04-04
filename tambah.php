<?php
/**
 * tambah.php — Form tambah siswa baru
 * Satu frame: foto (kanan, besar) + field data (kiri).
 * Mendukung drag-and-drop + preview foto sebelum submit.
 */
require_once 'config.php';
session_start();

// ── Ambil daftar kelas unik ────────────────────────────────────────────────────
$classes = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class ASC")
               ->fetchAll(PDO::FETCH_COLUMN);

// Kelas default jika tabel masih kosong
$defaultClasses = ['X RPL 1','X RPL 2','XI RPL 1','XI RPL 2','XII RPL 1','XII RPL 2'];
if (empty($classes)) $classes = $defaultClasses;

$error = $_SESSION['form_error'] ?? null;
unset($_SESSION['form_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa — BluePulse</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div id="page-spinner">
    <div class="spinner-wrap">
        <div class="spinner-ring"></div>
        <div class="spinner-ring ring2"></div>
    </div>
    <p class="spinner-text">Memuat…</p>
</div>

<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-logo">
            <div class="logo-icon">⚡</div>
            <span>Blue<span>Pulse</span></span>
        </div>
        <nav class="topbar-nav">
            <a href="index.php" class="nav-btn">🏠 Dashboard</a>
            <a href="tambah.php" class="nav-btn active">➕ Tambah Siswa</a>
        </nav>
    </div>
</header>

<main class="page-wrapper">

    <a href="index.php" class="back-link">← Kembali ke Dashboard</a>

    <?php if ($error): ?>
    <div class="alert alert-danger" data-autohide="1"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="page-title">
        <div>
            <h1>Tambah Siswa Baru</h1>
            <p>Isi data lengkap dan upload foto bukti kehadiran (opsional)</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>📋 Form Data Siswa</h2>
        </div>
        <div class="card-body">

            <form method="POST" action="proses_tambah.php"
                  enctype="multipart/form-data"
                  class="show-spinner">

                <!-- ── LAYOUT: Field kiri + Foto kanan ──────────── -->
                <div class="student-form-layout">

                    <!-- Kiri: Field input -->
                    <div class="form-fields">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span style="color:var(--red)">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control"
                                       placeholder="Contoh: Aldi Firmansyah"
                                       required maxlength="100"
                                       value="<?= e($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="nis">NIS <span style="color:var(--text-muted)">(Opsional)</span></label>
                                <input type="text" id="nis" name="nis"
                                       class="form-control"
                                       placeholder="Contoh: 2024001"
                                       maxlength="20"
                                       value="<?= e($_POST['nis'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="class">Kelas <span style="color:var(--red)">*</span></label>
                                <select id="class" name="class" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c) ?>"
                                            <?= (($_POST['class'] ?? '') === $c) ? 'selected' : '' ?>>
                                        <?= e($c) ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="__custom__">➕ Kelas Lain (ketik di bawah)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="attendance">Status Kehadiran <span style="color:var(--red)">*</span></label>
                                <select id="attendance" name="attendance" class="form-select" required>
                                    <option value="Tidak" <?= (($_POST['attendance'] ?? '') === 'Tidak') ? 'selected':'' ?>>❌ Tidak Hadir</option>
                                    <option value="Hadir"  <?= (($_POST['attendance'] ?? 'Hadir') === 'Hadir') ? 'selected':'' ?>>✅ Hadir</option>
                                </select>
                            </div>
                        </div>

                        <!-- Input kelas custom (tampil jika pilih "Kelas Lain") -->
                        <div class="form-group" id="custom-class-wrap" style="display:none">
                            <label for="class_custom">Nama Kelas Baru</label>
                            <input type="text" id="class_custom" name="class_custom"
                                   class="form-control"
                                   placeholder="Contoh: X TKJ 1"
                                   maxlength="50">
                            <span class="hint">Nama kelas baru akan disimpan otomatis ke database.</span>
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat <span style="color:var(--text-muted)">(Opsional)</span></label>
                            <textarea id="address" name="address"
                                      class="form-control"
                                      placeholder="Jl. Merdeka No. 1, Bandung"
                                      maxlength="500"><?= e($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="divider"></div>

                        <!-- Tombol aksi -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                💾 Simpan Data Siswa
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg">Batal</a>
                        </div>
                    </div><!-- /form-fields -->

                    <!-- Kanan: Upload foto (besar) -->
                    <div class="photo-panel">
                        <div class="photo-panel-label">
                            📷 Foto Bukti Kehadiran
                            <span class="badge-opt">Opsional</span>
                        </div>

                        <!-- Drop-zone besar -->
                        <div class="photo-dropzone" id="photo-dropzone">
                            <input type="file"
                                   name="photo"
                                   id="photo-input"
                                   accept="image/*"
                                   capture="environment">

                            <!-- Placeholder (sebelum ada foto) -->
                            <div class="dropzone-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:0.6rem;pointer-events:none">
                                <div class="dropzone-icon">📸</div>
                                <div class="dropzone-text">Klik atau seret foto ke sini</div>
                                <div class="dropzone-sub">
                                    JPG · PNG · WEBP · GIF<br>
                                    Maks. 5 MB
                                </div>
                            </div>

                            <!-- Preview gambar -->
                            <img id="photo-preview-img" src="" alt="Preview Foto">

                            <!-- Tombol hapus preview -->
                            <button type="button" id="photo-remove-btn" class="photo-remove-overlay">
                                ✕ Hapus
                            </button>
                        </div>

                        <!-- Info -->
                        <div class="photo-info-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            Foto akan disimpan sebagai bukti kehadiran siswa.
                            Bisa berupa selfie, foto QR absen, dll.
                        </div>

                        <!-- Tip kamera -->
                        <div style="background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.2);
                                    border-radius:var(--radius-sm);padding:0.85rem;font-size:0.75rem;
                                    color:var(--text-secondary);line-height:1.6">
                            💡 <strong style="color:var(--blue-light)">Tips:</strong><br>
                            Di perangkat mobile, tombol kamera akan muncul otomatis.
                            Untuk PC/laptop, klik dropzone untuk memilih file dari komputer.
                        </div>
                    </div><!-- /photo-panel -->

                </div><!-- /student-form-layout -->
            </form>

        </div>
    </div>

</main>

<script src="script.js"></script>
<script>
// Toggle kelas custom
document.getElementById('class')?.addEventListener('change', function() {
    const wrap  = document.getElementById('custom-class-wrap');
    const input = document.getElementById('class_custom');
    if (this.value === '__custom__') {
        wrap.style.display  = '';
        input.required      = true;
    } else {
        wrap.style.display  = 'none';
        input.required      = false;
    }
});
</script>
</body>
</html>