# SiKos - Sistem Informasi Pengelolaan Kamar Kos
**Proyek Ujian Tengah Semester (UTS) Konstruksi dan Evolusi Perangkat Lunak**  
**Tahun Akademik Gasal 2026/2027 | D4 Teknologi Rekayasa Perangkat Lunak - Sekolah Vokasi UGM**

* **Nama Mahasiswa**: Januarsyah Akbar
* **NIM**: 24/535846/SV/24314
* **Repositori**: `uts-pl-24-535846-sv-24314`

---

## 1. Deskripsi Singkat
**SiKos** adalah aplikasi terpadu untuk pemilik kos dalam mengelola ketersediaan, tipe, harga sewa, dan status hunian kamar kos secara *real-time*. Sistem ini dibangun menggunakan arsitektur modern berbasis micro-services ter-kontainerisasi (Docker Compose) dengan backend Laravel 11 yang ditenagai oleh **FrankenPHP**, frontend **Vue 3** berbasis Vite yang disajikan oleh **Nginx**, serta basis data **MySQL 8.4** dengan volume persisten.

---

## 2. Arsitektur & Aliran Data Sistem

```text
[ Browser Klien ]
        │
        ▼ (HTTP :8080)
┌────────────────────────────────────────────────────────┐
│               Docker Network (sikos-network)           │
│                                                        │
│   ┌───────────────┐        Axios API Request           │
│   │   frontend    │ ──────────────────────────────┐    │
│   │   (Nginx/Vue) │                               │    │
│   └───────────────┘                               │    │
│                                                   ▼    │
│                                         ┌──────────────────┐
│                                         │     backend      │
│                                         │ (Laravel 11 /    │
│                                         │  FrankenPHP)     │
│                                         └─────────┬────────┘
│                                                   │ PDO (:3306)
│                                                   ▼
│                                         ┌──────────────────┐
│                                         │    db (MySQL)    │
│                                         │  (Named Volume)  │
│                                         └──────────────────┘
└────────────────────────────────────────────────────────┘
```

1. **Frontend (Vue 3 / Nginx)**: Menyajikan UI Single Page Application pada port 8080. Seluruh request API diarahkan ke `/api` menggunakan token otentikasi Sanctum.
2. **Backend (Laravel / FrankenPHP)**: Server aplikasi modern berbasis FrankenPHP (Caddy engine) melayani permintaan API pada port 8000 secara asinkron dan efisien tanpa memerlukan reverse proxy Nginx dan PHP-FPM terpisah.
3. **Basis Data (MySQL 8.4)**: Terhubung melalui service name `db` di dalam `sikos-network`. Data fisik persisten disimpan pada *named volume* `sikos-db-data`.

---

## 3. Spesifikasi Endpoint RESTful API

| Method | Endpoint | Keterangan | Auth | Status Sukses |
|---|---|---|---|---|
| `POST` | `/api/login` | Masuk dan menerbitkan token Sanctum | Publik | `200 OK` |
| `POST` | `/api/logout` | Mencabut token aktif pengguna | Bearer | `200 OK` |
| `GET` | `/api/me` | Profil pemilik kos | Bearer | `200 OK` |
| `GET` | `/api/kamars` | Menampilkan seluruh unit kamar kos | Bearer | `200 OK` |
| `GET` | `/api/kamars/{id}` | Menampilkan rincian satu unit kamar | Bearer | `200 OK` |
| `POST` | `/api/kamars` | Menambahkan unit kamar baru | Bearer | `201 Created` |
| `PUT` | `/api/kamars/{id}` | Memperbarui data atau status kamar | Bearer | `200 OK` |
| `DELETE` | `/api/kamars/{id}` | Menghapus unit kamar kos | Bearer | `200 OK` |

* **Respon 401**: Dihasilkan saat mengakses endpoint berbayar tanpa token bearer.
* **Respon 422**: Dihasilkan saat masukan form kamar tidak memenuhi aturan validasi.

---

## 4. Cara Menjalankan Sistem (Docker Compose)

### Prasyarat
* Docker Engine (>= 24.0) & Docker Compose (>= v2.20)
* Git

### Langkah Instalasi
1. Klon repositori ini:
   ```bash
   git clone git@github.com:januarsyah901/uts-pl-24-535846-sv-24314.git
   cd uts-pl-24-535846-sv-24314
   ```
2. Buat file konfigurasi lingkungan:
   ```bash
   cp .env.example .env
   ```
3. Bangun dan jalankan seluruh container:
   ```bash
   docker compose up -d --build
   ```
4. Periksa status seluruh layanan:
   ```bash
   docker compose ps
   ```
5. Akses antarmuka aplikasi melalui browser di [http://localhost:8080](http://localhost:8080).
   * **Email Default**: `ibukos@sikos.id`
   * **Kata Sandi**: `password123`

---

## 5. Pengujian API (Postman Collection)
File ekspor Postman Collection tersimpan di `backend/postman_collection.json`. Koleksi ini mencakup seluruh skenario (login sukses, login gagal, validasi 422, operasi CRUD, dan verifikasi proteksi 401) dengan auto-injection bearer token.

---

## 6. Otomasi CI/CD (GitHub Actions)
Pipeline CI/CD terkonfigurasi pada `.github/workflows/ci.yml`:
* **Branch Fitur & Dev / Pull Requests**: Menjalankan pengujian backend (`php artisan test`) dan kompilasi aset frontend (`npm run build`).
* **Branch `main` (Push)**: Menjalankan pengujian backend, build frontend, lalu membangun image Docker backend dan frontend untuk diunggah ke GitHub Container Registry (`ghcr.io`) berlabel Commit SHA.
