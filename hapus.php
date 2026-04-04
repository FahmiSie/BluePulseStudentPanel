<?php
/**
 * hapus.php — Proses hapus data siswa
 * Hanya menerima GET dengan ?id=xxx (dikonfirmasi di sisi client via JS).
 * Menghapus record dari DB + file foto jika ada.
 */
require_once 'config.php';
session_start();

// ── Ambil & validasi ID ────────────────────────────────────────────────────────
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'ID tidak valid.'];
    header('Location: index.php');
    exit;
}

// ── Ambil data siswa (perlu nama & foto untuk pesan & cleanup) ───────────────
$stmt = $pdo->prepare("SELECT name, photo FROM students WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Data siswa tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

// ── Hapus record dari database ────────────────────────────────────────────────
try {
    $del = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $del->execute([':id' => $id]);

    // Hapus file foto jika ada
    if ($student['photo'] && file_exists(UPLOAD_DIR . $student['photo'])) {
        @unlink(UPLOAD_DIR . $student['photo']);
    }

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => "🗑️ Data siswa \"{$student['name']}\" berhasil dihapus.",
    ];

} catch (PDOException $e) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'msg'  => 'Gagal menghapus data. Silakan coba lagi.',
    ];
}

header('Location: index.php');
exit;