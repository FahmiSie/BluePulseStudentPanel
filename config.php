<?php
/**
 * config.php — Koneksi database BluePulse Student Panel
 * Menggunakan PDO dengan error handling dan charset UTF-8.
 * Ubah DB_HOST, DB_USER, DB_PASS sesuai environment Anda.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'bluepulse_db');
define('DB_USER', 'root');       // Ganti dengan user database Anda
define('DB_PASS', '');           // Ganti dengan password database Anda
define('DB_CHARSET', 'utf8mb4');

// Direktori upload foto (relatif terhadap root proyek)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ── Buat koneksi PDO ──────────────────────────────────────────────────────────
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lempar exception saat error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil fetch berupa array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // gunakan prepared statement native
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke user di production
    http_response_code(500);
    die('<div style="font-family:monospace;color:red;padding:2rem;">
        Koneksi database gagal. Silakan periksa konfigurasi config.php.<br>
        <small>(' . htmlspecialchars($e->getMessage()) . ')</small>
    </div>');
}

// ── Buat folder uploads jika belum ada ───────────────────────────────────────
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ── Helper: escape output HTML ────────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ── Helper: proses upload foto ────────────────────────────────────────────────
/**
 * Memproses file upload foto.
 * @param array  $file       $_FILES['photo']
 * @param string $oldPhoto   nama file lama (untuk dihapus jika ada)
 * @return string|null       nama file baru, atau null jika gagal/tidak ada upload
 */
function processPhotoUpload(array $file, string $oldPhoto = ''): ?string
{
    // Tidak ada file baru dipilih
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal. Kode error: ' . $file['error']);
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Ukuran file melebihi batas 5 MB.');
    }

    // Validasi MIME type yang sebenarnya (bukan hanya ekstensi)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        throw new RuntimeException('Tipe file tidak diizinkan. Gunakan JPG, PNG, WEBP, atau GIF.');
    }

    // Buat nama file unik
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('foto_', true) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Gagal memindahkan file upload.');
    }

    // Hapus foto lama jika ada
    if ($oldPhoto && file_exists(UPLOAD_DIR . $oldPhoto)) {
        @unlink(UPLOAD_DIR . $oldPhoto);
    }

    return $fileName;
}