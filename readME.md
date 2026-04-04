# ⚡ BluePulse Student Panel

Aplikasi **CRUD Data Siswa** berbasis PHP + MariaDB dengan:
- Tema gelap **Demon Slayer** (hitam pekat + biru elektrik)
- Upload **foto bukti kehadiran** terintegrasi dalam form
- Toggle tampilan **Tabel ↔ Kartu**
- Filter kelas & status kehadiran
- Toggle kehadiran **inline** di tabel
- **Lightbox** untuk preview foto ukuran penuh
- Keamanan: prepared statements, validasi MIME type, proteksi upload folder

---

## 📂 Struktur File

```
bluepulse-student-panel/
├── config.php          # Koneksi PDO + helper upload foto
├── index.php           # Dashboard utama: tabel/kartu, filter, statistik
├── tambah.php          # Form tambah siswa + upload foto
├── proses_tambah.php   # Proses simpan siswa baru
├── edit.php            # Form edit siswa + ganti/hapus foto
├── proses_edit.php     # Proses update siswa (full & inline)
├── hapus.php           # Proses hapus siswa + cleanup foto
├── style.css           # Seluruh CSS tema
├── script.js           # Spinner, toggle, preview foto, lightbox
├── .htaccess           # Security headers + proteksi upload
├── bluepulse_db.sql    # SQL dump (CREATE + INSERT data dummy)
├── uploads/            # Folder foto bukti kehadiran (auto-dibuat)
└── README.md
```

---

## 🚀 A. Persiapan Lokal (XAMPP / Laragon)

### 1. Taruh proyek di htdocs
```bash
# XAMPP (Windows)
C:\xampp\htdocs\bluepulse-student-panel\

# Laragon
C:\laragon\www\bluepulse-student-panel\

# macOS / Linux
/var/www/html/bluepulse-student-panel/
```

### 2. Import database
- Buka `http://localhost/phpmyadmin`
- Buat database baru bernama `bluepulse_db` (atau biarkan SQL yang buat)
- Klik **Import** → pilih `bluepulse_db.sql` → **Go**

Atau via terminal:
```bash
mysql -u root -p < bluepulse_db.sql
```

### 3. Sesuaikan config.php
```php
define('DB_USER', 'root');
define('DB_PASS', '');       // password XAMPP biasanya kosong
```

### 4. Uji coba
Buka browser: `http://localhost/bluepulse-student-panel/`

---

## 📤 B. Push ke GitHub

```bash
cd bluepulse-student-panel

# Inisialisasi git
git init
git add .
git commit -m "feat: init BluePulse Student Panel"

# Buat repo baru di github.com, lalu:
git remote add origin https://github.com/username/bluepulse-student-panel.git
git branch -M main
git push -u origin main
```

> ⚠️ Pastikan folder `uploads/` tidak berisi foto asli saat push (tambahkan ke .gitignore):
> ```
> /uploads/*
> !/uploads/.gitkeep
> ```

---

## 🖥️ C. Setup Server Ubuntu

### 1. Update sistem
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Apache + PHP + MariaDB
```bash
sudo apt install -y apache2 php libapache2-mod-php php-mysql php-mbstring php-xml php-fileinfo mariadb-server mariadb-client
```

### 3. Aktifkan ekstensi & modul Apache
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4. Amankan MariaDB
```bash
sudo mysql_secure_installation
# Jawab: Y untuk semua (set password root, hapus anonymous, dll)
```

---

## 📂 D. Clone & Deploy di Ubuntu

### 1. Clone repository
```bash
cd /var/www/html
sudo git clone https://github.com/username/bluepulse-student-panel.git
sudo chown -R www-data:www-data bluepulse-student-panel
sudo chmod -R 755 bluepulse-student-panel

# Folder uploads harus writable oleh web server
sudo chmod 775 bluepulse-student-panel/uploads
sudo chown www-data:www-data bluepulse-student-panel/uploads
```

### 2. Buat virtual host (opsional)
```bash
sudo nano /etc/apache2/sites-available/bluepulse.conf
```

Isi file:
```apache
<VirtualHost *:80>
    ServerName bluepulse.example.com
    DocumentRoot /var/www/html/bluepulse-student-panel

    <Directory /var/www/html/bluepulse-student-panel>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/bluepulse-error.log
    CustomLog ${APACHE_LOG_DIR}/bluepulse-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite bluepulse.conf
sudo systemctl reload apache2
```

---

## 🗃️ E. Setup Database di Ubuntu

### 1. Masuk MariaDB
```bash
sudo mysql -u root -p
```

### 2. Buat user khusus (rekomendasi — jangan pakai root!)
```sql
CREATE USER 'bp_user'@'localhost' IDENTIFIED BY 'GantiPasswordKuat123!';
GRANT ALL PRIVILEGES ON bluepulse_db.* TO 'bp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Import SQL dump
```bash
sudo mysql -u bp_user -p bluepulse_db < /var/www/html/bluepulse-student-panel/bluepulse_db.sql
```
> Jika database belum ada: `mysql -u root -p < bluepulse_db.sql` (SQL sudah include CREATE DATABASE)

---

## ⚙️ F. Sesuaikan config.php di Server

```bash
sudo nano /var/www/html/bluepulse-student-panel/config.php
```

Ubah baris:
```php
define('DB_USER', 'bp_user');
define('DB_PASS', 'GantiPasswordKuat123!');
```

---

## 🔥 G. Firewall & Uji Coba

```bash
# Izinkan HTTP & HTTPS
sudo ufw allow 'Apache Full'
sudo ufw enable
sudo ufw status
```

Buka browser: `http://IP_SERVER/bluepulse-student-panel/`
atau `http://bluepulse.example.com/` jika pakai virtual host.

---

## 🔒 H. Tips Keamanan Tambahan

1. **PHP file upload size** — sesuaikan di `/etc/php/*/apache2/php.ini`:
   ```ini
   upload_max_filesize = 5M
   post_max_size = 8M
   ```
   Lalu restart: `sudo systemctl restart apache2`

2. **Sembunyikan error di production** — ubah di `php.ini`:
   ```ini
   display_errors = Off
   log_errors = On
   ```

3. **HTTPS** — gunakan Certbot/Let's Encrypt:
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d bluepulse.example.com
   ```

---

## 📋 Fitur Ringkasan

| Fitur | Keterangan |
|---|---|
| CRUD Siswa | Tambah, Edit, Hapus dengan validasi |
| Upload Foto | Bukti kehadiran, drag-drop, preview langsung |
| Filter | Kelas & Status Kehadiran (GET params) |
| Toggle Inline | Ubah status hadir langsung dari tabel |
| Tabel / Kartu | Toggle view dengan preferensi tersimpan di localStorage |
| Lightbox | Klik foto untuk tampilan penuh |
| Statistik | Total, Hadir, Tidak Hadir, Jumlah Kelas |
| Security | PDO prepared statements, validasi MIME, .htaccess protection |
| Responsive | Mobile-friendly, tabel scroll horizontal |

---

*BluePulse Student Panel — dibuat dengan ⚡*