# Arsitektur — SUNDIGITAL

Versi 0.3 · Pendamping `prd.md` · Struktur file lengkap ada di `ARCHITECTURE.md`

Revisi 0.3 menyelaraskan arsitektur teknis dengan acuan baru (`SUNDIGITAL_Mockup.html` - Perpustakaan Digital SMP Negeri 16 Depok), mencakup modul pembaca dokumen PDF terintegrasi, video pendukung, rak bacaan, kuis pilihan ganda terhubung (*linked quiz*), dan rekapitulasi nilai analitik.

---

## 1. Keputusan Arsitektur

| Aspek | Pilihan | Alasan |
|---|---|---|
| **Backend Framework** | **Laravel 12** (PHP 8.2+) | Validasi tangguh, Eloquent ORM, auth bawaan, abstraction storage, dan ekosistem matang |
| **Starter Kit** | **Laravel Breeze** (Vue + Inertia, Pest) | Autentikasi session standar, layout Breeze yang disesuaikan, tanpa API publik terpisah |
| **Frontend Siswa** | **Inertia.js + Vue 3 (script setup) + Tailwind CSS** | Pengalaman SPA responsif tanpa full reload, sinkronisasi state data langsung via server props |
| **PDF Rendering** | **PDF.js (Canvas & Text Layer)** | Pembacaan buku/modul langsung di browser dengan kontrol halaman, zoom, dan *Mode Fokus* |
| **Panel Guru & Admin** | **Filament** (dua panel: `/guru` & `/admin`) | Panel administrasi berkinerja tinggi, table builder, form builder, repeater butir soal, dan otorisasi query |
| **Database** | **MySQL / MariaDB** | Dukungan tipe JSON, transaksi ACID, dan kompatibilitas luas |
| **File Storage** | `storage/app/public` (simlink ke `public/storage`) | Penyimpanan buku/modul PDF aman, siap dialihkan ke S3/Cloud Storage bila diperlukan |
| **Video Hosting** | Eksternal embed (YouTube, Vimeo, direct MP4) | Menghemat beban bandwidth server sekolah; validasi URL di sisi server |

Arsitektur: **Monolit Modular** dalam satu repositori Laravel.

---

## 2. Gambaran Sistem & Aliran Pengguna

```
Browser
  │
  ├── Siswa  ──►  Inertia + Vue (/siswa/*)  ──┐
  │                                            ├──►  Laravel (Router, Controllers, Policies)
  ├── Guru   ──►  Filament Panel (/guru)  ────┤            │
  │                                            │            ▼
  └── Admin  ──►  Filament Panel (/admin) ────┘       Eloquent ORM
                                                            │
                                               ┌────────────┴────────────┐
                                               ▼                         ▼
                                             MySQL                Storage (PDF)
```

### Penyesuaian Alur Autentikasi (Breeze)
1. **Satu Pintu Masuk (`/login`):** Form login tunggal berbasis `username` (NIS untuk siswa; username/email untuk guru & admin).
2. **Tanpa Registrasi Publik:** Akses hanya dibuat melalui Administrator Sekolah.
3. **Penyortiran Peran Otomatis:** Setelah berhasil login, pengguna diarahkan sesuai `role`:
   - Siswa → `/siswa/dashboard`
   - Guru → `/guru`
   - Admin → `/admin`
4. **Fitur Bantuan Akun:** Permintaan bantuan dari formulir *Lupa Kata Sandi* disimpan ke database `account_requests` untuk ditinjau oleh Admin Sekolah.
5. **Rate Limiting:** Menggunakan middleware `throttle:login` (5 percobaan per menit) dengan lockout 60 detik.

---

## 3. Skema Data (Database Model)

```
users ──────┬───< shelf_items >────────── materials
            ├───< reading_histories >─────┤    │
            ├───< teachings >── classrooms     ├──< quizzes ──< questions
            │                       │          │       │
            └───< quiz_attempts >───┴──────────┴───────┘
```

### Tabel Utama dan Kolom:

1. **`users`**
   - `id`, `name`, `username` (unik), `password`, `role` (`student|teacher|admin`), `classroom_id` (nullable, relasi ke kelas siswa), `is_active` (boolean), `timestamps`.
2. **`classrooms`**
   - `id`, `name` (misal: "VIII A"), `grade_level` (8), `school_year` ("2026/2027"), `teacher_name` (nama wali kelas), `is_active` (boolean), `timestamps`.
3. **`subjects`**
   - `id`, `name`, `slug` (unik), `code` (misal: "MP01"), `icon`, `tone` (`green|blue|orange|pink|lilac|yellow`), `description`, `sort_order`, `timestamps`.
4. **`teachings`**
   - `id`, `teacher_id`, `subject_id`, `classroom_id`, `timestamps`. Unik gabungan: `(teacher_id, subject_id, classroom_id)`.
5. **`materials`** (Koleksi Buku, Modul, & Video)
   - `id`, `subject_id`, `classroom_id` (nullable; jika null berlaku untuk semua kelas), `title`, `type` (`book|module|reading|video`), `file_path` (nullable untuk PDF), `url` (nullable untuk video), `video_provider` (nullable: `youtube|vimeo|direct`), `size_bytes` (bigint nullable), `description` (text nullable), `status` (`draft|published`), `created_by` (foreign key `users`), `timestamps`.
6. **`shelf_items`** (Rak Saya / Bookmark Siswa)
   - `id`, `user_id`, `material_id`, `created_at`. Unik gabungan: `(user_id, material_id)`.
7. **`reading_histories`** (Riwayat Bacaan Siswa)
   - `id`, `user_id`, `material_id`, `last_read_at`, `timestamps`. Unik gabungan: `(user_id, material_id)`.
8. **`quizzes`** (Latihan & Kuis)
   - `id`, `subject_id`, `classroom_id`, `material_id` (nullable; foreign key ke `materials` untuk kuis terkait buku), `title`, `instructions` (text nullable), `status` (`draft|published`), `revision` (int, default 1), `created_by`, `timestamps`.
9. **`questions`** (Butir Soal Pilihan Ganda)
   - `id`, `quiz_id`, `body` (pertanyaan), `options` (JSON array: 4 opsi jawaban [A, B, C, D]), `correct_index` (int: 0, 1, 2, atau 3), `explanation` (text nullable, pembahasan), `position` (int, urutan soal), `timestamps`.
10. **`quiz_attempts`** (Riwayat Pengerjaan Kuis Siswa)
    - `id`, `quiz_id`, `student_id`, `revision` (int), `answers` (JSON: array indeks jawaban siswa), `correct_count` (int), `score` (int 0–100), `finished_at`.
11. **`account_requests`** (Bantuan Akun & Lupa Kata Sandi)
    - `id`, `username`, `type` (`forgot_password|account_activation`), `status` (`pending|completed`), `note` (text nullable, catatan verifikasi admin), `completed_at` (nullable), `timestamps`.
12. **`activity_logs`** (Riwayat Aktivitas Administratif)
    - `id`, `actor_id` (nullable), `actor_name`, `action` (teks tindakan), `created_at`.
13. **`school_settings`** (Pengaturan Umum Sekolah)
    - `id`, `school_name`, `academic_year`, `semester` (`ganjil|genap`), `timestamps`.

---

## 4. Rute Siswa (Inertia + Vue)

Semua rute dilindungi oleh middleware `auth` dan `role:student`, dengan prefix `/siswa` dan penamaan rute `siswa.*`.

| Method | URL | Nama Rute | Controller@Aksi | Keterangan |
|---|---|---|---|---|
| GET | `/siswa/dashboard` | `siswa.dashboard` | DashboardController@index | Beranda, hero bacaan, bacaan terakhir, koleksi pilihan |
| GET | `/siswa/perpustakaan` | `siswa.library.index` | LibraryController@index | Katalog buku & modul PDF dengan filter mapel & search |
| GET | `/siswa/perpustakaan/{material}` | `siswa.library.show` | LibraryController@show | Detail koleksi & modal reader (in-app PDF reader) |
| GET | `/siswa/mapel` | `siswa.subjects.index` | SubjectController@index | Grid 11 kategori mata pelajaran |
| GET | `/siswa/rak` | `siswa.shelf.index` | ShelfController@index | Halaman Rak Saya (buku-buku yang disimpan) |
| POST | `/siswa/rak/{material}/toggle`| `siswa.shelf.toggle` | ShelfController@toggle | Tambah / hapus dari rak (tombol love ♡/♥) |
| GET | `/siswa/riwayat-baca` | `siswa.history.index` | HistoryController@index | Daftar buku yang baru saja dibaca |
| GET | `/siswa/video` | `siswa.videos.index` | VideoController@index | Galeri video pendukung pembelajaran |
| GET | `/siswa/latihan` | `siswa.quizzes.index` | QuizController@index | Daftar kuis kelas & riwayat pengerjaan |
| GET | `/siswa/latihan/{quiz}` | `siswa.quizzes.show` | QuizController@show | Runner kuis interaktif (satu soal per halaman) |
| POST | `/siswa/latihan/{quiz}/finish` | `siswa.quizzes.finish` | QuizController@finish | Server-side scoring & simpan attempt |
| GET | `/siswa/latihan/{quiz}/hasil/{attempt}` | `siswa.quizzes.result` | QuizController@result | Tampilan skor, kunci jawaban, dan pembahasan |
| GET | `/profile` | `profile.edit` | ProfileController@edit | Profil akun & ganti kata sandi |
| PUT | `/password` | `password.update` | PasswordController@update | Proses update kata sandi mandiri |

---

## 5. Panel Guru (Filament — `/guru`)

Semua akses dibatasi oleh `teachings` (hanya mata pelajaran dan kelas yang diampu guru):

1. **Dashboard Guru:**
   - Statistik: Total buku/materi PDF, koleksi terbit vs draf, materi pendukung video, dan siswa aktif.
   - Widget prioritas: Kuis yang butuh perhatian & ringkasan pengerjaan siswa.
2. **MaterialResource (Koleksi Saya & Tambah Koleksi):**
   - Form upload file PDF (maks. 50 MB) atau input tautan video (YouTube/Vimeo/MP4).
   - Penentuan kategori (Buku, Materi, Modul, Video), kelas tujuan, dan status (Draf / Terbit).
3. **QuizResource (Kelola Kuis):**
   - Form kuis: Judul, mata pelajaran, kelas tujuan, petunjuk pengerjaan, dan relasi materi (`material_id` nullable).
   - Repeater butir soal: Pertanyaan, 4 pilihan opsi (A, B, C, D), radio button penentu kunci benar, pembahasan.
   - Opsi publikasi (Draf / Terbit) dan penanganan revisi soal.
4. **Halaman Rekap Nilai (`/guru/rekap-nilai`):**
   - **Tab Ringkasan Kelas:** Matriks kelengkapan siswa kelas target (kuis selesai, belum lengkap, rata-rata nilai tertinggi).
   - **Tab Nilai per Kuis:** Rincian status siswa (*Sudah, Sedang, Belum mengerjakan*), nilai tertinggi, nilai terakhir, serta pratinjau lembar jawaban siswa.

---

## 6. Panel Admin (Filament — `/admin`)

1. **Dashboard Admin:**
   - Ringkasan total siswa aktif, guru, mata pelajaran, kelas, dan permintaan bantuan akun.
2. **UserResource:**
   - Manajemen akun (siswa, guru, admin) dengan status aktif/nonaktif.
   - Aksi *Reset Password* langsung dengan checklist konfirmasi verifikasi identitas.
3. **ClassroomResource & SubjectResource:**
   - Kelola rombel kelas (tahun ajaran, wali kelas) dan mata pelajaran (kode, nama, penugasan guru pengampu).
4. **AccountRequestResource (Bantuan Akun):**
   - Daftar permohonan pemulihan akses akun / lupa kata sandi dengan dialog pencatatan tindak lanjut.
5. **ActivityLogResource:**
   - Audit trail riwayat aktivitas pengelolaan sekolah.
6. **SchoolSettingsPage:**
   - Form nama sekolah (default: *SMP Negeri 16 Depok*), tahun ajaran (*2026/2027*), dan semester.
7. **CRUD Safety Policies:**
   - Larangan menghapus pengguna yang masih menjabat/memiliki kuis/koleksi aktif.
   - Larangan menghapus kelas yang masih memiliki siswa aktif.
   - Larangan menghapus materi yang masih terikat dengan kuis aktif.
