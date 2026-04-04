<?php
/**
 * proses_tambah.php — Proses penyimpanan data siswa baru
 * Menerima POST dari tambah.php, memvalidasi, upload foto, insert ke DB.
 * Setelah selesai redirect ke index.php dengan flash message.
 */
require_once 'config.php';
session_start();

// Hanya terima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit;
}

// ── Validasi & sanitasi input ─────────────────────────────────────────────────
$name       = trim($_POST['name']       ?? '');
$nis        = trim($_POST['nis']        ?? '');
$attendance = trim($_POST['attendance'] ?? '');
$address    = trim($_POST['address']    ?? '');

// Tangani pilihan kelas (termasuk kelas custom)
$classVal   = trim($_POST['class']        ?? '');
$classCustom= trim($_POST['class_custom'] ?? '');
if ($classVal === '__custom__') {
    $classVal = $classCustom;
}

// Validasi wajib
$errors = [];
if ($name === '')       $errors[] = 'Nama lengkap wajib diisi.';
if ($classVal === '')   $errors[] = 'Kelas wajib dipilih/diisi.';
if (!in_array($attendance, ['Hadir','Tidak'], true)) {
    $errors[] = 'Status kehadiran tidak valid.';
}
if (strlen($name) > 100)     $errors[] = 'Nama terlalu panjang (maks. 100 karakter).';
if (strlen($classVal) > 50)  $errors[] = 'Nama kelas terlalu panjang (maks. 50 karakter).';
if (strlen($nis) > 20)       $errors[] = 'NIS terlalu panjang (maks. 20 karakter).';

if ($errors) {
    $_SESSION['form_error'] = implode(' ', $errors);
    header('Location: tambah.php');
    exit;
}

// ── Proses upload foto ─────────────────────────────────────────────────────────
$photoName = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    try {
        $photoName = processPhotoUpload($_FILES['photo']);
    } catch (RuntimeException $e) {
        $_SESSION['form_error'] = 'Upload foto gagal: ' . $e->getMessage();
        header('Location: tambah.php');
        exit;
    }
}

// ── Insert ke database ────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO students (nis, name, class, attendance, address, photo)
        VALUES (:nis, :name, :class, :attendance, :address, :photo)
    ");
    $stmt->execute([
        ':nis'        => $nis        ?: null,
        ':name'       => $name,
        ':class'      => $classVal,
        ':attendance' => $attendance,
        ':address'    => $address    ?: null,
        ':photo'      => $photoName,
    ]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => "✅ Data siswa \"$name\" berhasil ditambahkan.",
    ];
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['form_error'] = 'Terjadi kesalahan database. Silakan coba lagi.';
    // Hapus foto yang sudah terupload jika insert gagal
    if ($photoName && file_exists(UPLOAD_DIR . $photoName)) {
        @unlink(UPLOAD_DIR . $photoName);
    }
    header('Location: tambah.php');
    exit;
}