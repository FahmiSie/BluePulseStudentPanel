<?php
/**
 * proses_edit.php — Proses update data siswa
 * Menerima POST dari edit.php (full edit) maupun inline attendance toggle.
 * Menangani: update field, upload foto baru, hapus foto lama.
 */
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

// ── Cek data siswa existing ───────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Data siswa tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

// ── Mode: inline attendance toggle ────────────────────────────────────────────
// Dikirim dari tabel dengan flag "inline=1"
if (!empty($_POST['inline'])) {
    $attendance = trim($_POST['attendance'] ?? '');
    if (!in_array($attendance, ['Hadir','Tidak'], true)) {
        header('Location: index.php');
        exit;
    }
    $upd = $pdo->prepare("UPDATE students SET attendance = :a WHERE id = :id");
    $upd->execute([':a' => $attendance, ':id' => $id]);
    header('Location: index.php');
    exit;
}

// ── Mode: full edit ────────────────────────────────────────────────────────────
$name        = trim($_POST['name']        ?? '');
$nis         = trim($_POST['nis']         ?? '');
$attendance  = trim($_POST['attendance']  ?? '');
$address     = trim($_POST['address']     ?? '');
$removePhoto = !empty($_POST['remove_photo']);

$classVal    = trim($_POST['class']        ?? '');
$classCustom = trim($_POST['class_custom'] ?? '');
if ($classVal === '__custom__') $classVal = $classCustom;

// Validasi
$errors = [];
if ($name === '')      $errors[] = 'Nama lengkap wajib diisi.';
if ($classVal === '')  $errors[] = 'Kelas wajib dipilih/diisi.';
if (!in_array($attendance, ['Hadir','Tidak'], true)) {
    $errors[] = 'Status kehadiran tidak valid.';
}
if (strlen($name) > 100)    $errors[] = 'Nama terlalu panjang.';
if (strlen($classVal) > 50) $errors[] = 'Nama kelas terlalu panjang.';

if ($errors) {
    $_SESSION['form_error'] = implode(' ', $errors);
    header("Location: edit.php?id=$id");
    exit;
}

// ── Proses foto ───────────────────────────────────────────────────────────────
$currentPhoto = $student['photo'];
$newPhoto     = $currentPhoto; // default: tetap foto lama

// Kasus 1: hapus foto tanpa upload baru
if ($removePhoto && !isset($_FILES['photo']) || ($removePhoto && $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE)) {
    if ($currentPhoto && file_exists(UPLOAD_DIR . $currentPhoto)) {
        @unlink(UPLOAD_DIR . $currentPhoto);
    }
    $newPhoto = null;
}

// Kasus 2: upload foto baru (otomatis hapus foto lama)
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    try {
        $uploaded = processPhotoUpload($_FILES['photo'], $currentPhoto ?? '');
        if ($uploaded) $newPhoto = $uploaded;
    } catch (RuntimeException $e) {
        $_SESSION['form_error'] = 'Upload foto gagal: ' . $e->getMessage();
        header("Location: edit.php?id=$id");
        exit;
    }
}

// ── Update database ───────────────────────────────────────────────────────────
try {
    $upd = $pdo->prepare("
        UPDATE students SET
            nis        = :nis,
            name       = :name,
            class      = :class,
            attendance = :attendance,
            address    = :address,
            photo      = :photo
        WHERE id = :id
    ");
    $upd->execute([
        ':nis'        => $nis       ?: null,
        ':name'       => $name,
        ':class'      => $classVal,
        ':attendance' => $attendance,
        ':address'    => $address   ?: null,
        ':photo'      => $newPhoto,
        ':id'         => $id,
    ]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => "✅ Data siswa \"$name\" berhasil diperbarui.",
    ];
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['form_error'] = 'Terjadi kesalahan database. Silakan coba lagi.';
    header("Location: edit.php?id=$id");
    exit;
}