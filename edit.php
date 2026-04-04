<?php
/**
 * edit.php — Form edit data siswa
 * Menampilkan data existing (termasuk foto) dan memungkinkan update.
 * Foto baru akan menggantikan foto lama secara otomatis.
 */
require_once 'config.php';
session_start();

// ── Ambil ID dari query string ────────────────────────────────────────────────
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

// ── Ambil data siswa dari DB ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['flash'] = ['type'=>'danger', 'msg'=>'Data siswa tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

// ── Ambil daftar kelas unik ────────────────────────────────────────────────────
$classes = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class ASC")
               ->fetchAll(PDO::FETCH_COLUMN);

$defaultClasses = ['X RPL 1','X RPL 2','XI RPL 1','XI RPL 2','XII RPL 1','XII RPL 2'];
$allClasses = array_unique(array_merge($defaultClasses, $classes));
sort($allClasses);

$error = $_SESSION['form_error'] ?? null;
unset($_SESSION['form_error']);

// Nilai form: gunakan $_POST jika ada (setelah error validasi), jika tidak pakai data DB
$val = fn($key) => e($_POST[$key] ?? $student[$key] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa — BluePulse</title>
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
            <h1>Edit Data Siswa</h1>
            <p>ID #<?= (int)$student['id'] ?> · <?= e($student['name']) ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>✏️ Form Edit</h2>
            <span style="color:var(--text-muted);font-size:0.78rem">
                Terakhir diperbarui: <?= e($student['updated_at'] ?? $student['created_at']) ?>
            </span>
        </div>
        <div class="card-body">

            <form method="POST" action="proses_edit.php"
                  enctype="multipart/form-data"
                  class="show-spinner">

                <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
                <!-- Flag untuk hapus foto lama tanpa upload baru -->
                <input type="hidden" name="remove_photo" id="photo-remove-flag" value="">

                <div class="student-form-layout">

                    <!-- Kiri: Field -->
                    <div class="form-fields">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span style="color:var(--red)">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control"
                                       required maxlength="100"
                                       value="<?= $val('name') ?>">
                            </div>
                            <div class="form-group">
                                <label for="nis">NIS <span style="color:var(--text-muted)">(Opsional)</span></label>
                                <input type="text" id="nis" name="nis"
                                       class="form-control"
                                       maxlength="20"
                                       value="<?= $val('nis') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="class">Kelas <span style="color:var(--red)">*</span></label>
                                <select id="class" name="class" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($allClasses as $c): ?>
                                    <option value="<?= e($c) ?>"
                                            <?= ($val('class') === e($c)) ? 'selected' : '' ?>>
                                        <?= e($c) ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="__custom__">➕ Kelas Lain</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="attendance">Status Kehadiran</label>
                                <select id="attendance" name="attendance" class="form-select" required>
                                    <option value="Hadir" <?= ($val('attendance') === 'Hadir') ? 'selected':'' ?>>✅ Hadir</option>
                                    <option value="Tidak" <?= ($val('attendance') === 'Tidak') ? 'selected':'' ?>>❌ Tidak Hadir</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="custom-class-wrap" style="display:none">
                            <label>Nama Kelas Baru</label>
                            <input type="text" name="class_custom" id="class_custom"
                                   class="form-control" maxlength="50"
                                   placeholder="Contoh: X TKJ 2">
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea id="address" name="address"
                                      class="form-control"
                                      maxlength="500"><?= $val('address') ?></textarea>
                        </div>

                        <div class="divider"></div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">💾 Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-secondary btn-lg">Batal</a>
                        </div>
                    </div>

                    <!-- Kanan: Foto -->
                    <div class="photo-panel">
                        <div class="photo-panel-label">
                            📷 Foto Bukti Kehadiran
                            <span class="badge-opt">Opsional</span>
                        </div>

                        <?php if ($student['photo'] && file_exists(UPLOAD_DIR . $student['photo'])): ?>
                        <!-- Foto existing -->
                        <div class="photo-existing" id="existing-photo-wrap">
                            <img src="<?= e(UPLOAD_URL . $student['photo']) ?>"
                                 alt="Foto saat ini"
                                 data-lightbox="<?= e(UPLOAD_URL . $student['photo']) ?>"
                                 style="cursor:zoom-in">
                            <div class="photo-existing-label">
                                ✅ Foto tersimpan — upload baru untuk mengganti
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" id="delete-existing-btn"
                                style="width:100%">
                            🗑️ Hapus Foto Ini
                        </button>
                        <div class="divider" style="margin:0.75rem 0"></div>
                        <?php endif; ?>

                        <!-- Drop-zone upload baru -->
                        <div class="photo-dropzone" id="photo-dropzone">
                            <input type="file" name="photo" id="photo-input"
                                   accept="image/*" capture="environment">

                            <div class="dropzone-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:0.6rem;pointer-events:none">
                                <div class="dropzone-icon">📸</div>
                                <div class="dropzone-text">
                                    <?= $student['photo'] ? 'Upload foto pengganti' : 'Klik atau seret foto' ?>
                                </div>
                                <div class="dropzone-sub">JPG · PNG · WEBP · GIF<br>Maks. 5 MB</div>
                            </div>

                            <img id="photo-preview-img" src="" alt="Preview">
                            <button type="button" id="photo-remove-btn" class="photo-remove-overlay">✕ Hapus</button>
                        </div>

                        <div class="photo-info-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            Mengupload foto baru akan menggantikan foto lama.
                        </div>
                    </div><!-- /photo-panel -->

                </div>
            </form>

        </div>
    </div>

    <!-- Lightbox -->
    <div id="lightbox">
        <button id="lightbox-close">✕ Tutup</button>
        <img id="lightbox-img" src="" alt="Foto Siswa">
    </div>

</main>

<script src="script.js"></script>
<script>
// Toggle kelas custom
document.getElementById('class')?.addEventListener('change', function() {
    const wrap  = document.getElementById('custom-class-wrap');
    const input = document.getElementById('class_custom');
    wrap.style.display = this.value === '__custom__' ? '' : 'none';
    input.required     = this.value === '__custom__';
});

// Hapus foto existing
document.getElementById('delete-existing-btn')?.addEventListener('click', function() {
    if (!confirm('Hapus foto yang tersimpan?')) return;
    document.getElementById('photo-remove-flag').value = '1';
    document.getElementById('existing-photo-wrap')?.remove();
    this.remove();
});
</script>
</body>
</html>