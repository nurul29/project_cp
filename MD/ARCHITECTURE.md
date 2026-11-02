# ARCHITECTURE — Struktur File dan Folder SUNDIGITAL

Versi 0.3 · Penjelasan keputusan ada di `arch.md`, kebutuhan produk di `prd.md`, daftar kerja di `todo.md`.

Legenda: **[Breeze]** dibuat starter kit (boleh diubah) · **[Baru]** dibuat sendiri · **[Filament]** dibuat Filament · **[Ubah]** bawaan yang disesuaikan.

---

## 1. Pohon Project

```
sundigital/
├── app/
│   ├── Actions/                              [Baru] Logika bisnis satu tugas per class
│   │   ├── Student/
│   │   │   ├── SubmitQuizAttempt.php         Kalkulasi skor & simpan percobaan kuis
│   │   │   ├── ToggleShelfItem.php           Simpan / hapus buku ke rak pribadi
│   │   │   └── RecordReadingHistory.php      Catat jejak bacaan siswa
│   │   └── Admin/
│   │       ├── ResetUserPassword.php         Reset kata sandi pengguna via admin
│   │       └── LogActivity.php               Pencatatan audit log aktivitas
│   │
│   ├── Enums/                                [Baru]
│   │   ├── UserRole.php                      student | teacher | admin
│   │   ├── MaterialType.php                  book | module | reading | video
│   │   ├── VideoProvider.php                 youtube | vimeo | direct | none
│   │   ├── PublicationStatus.php             draft | published
│   │   └── RequestStatus.php                 pending | completed
│   │
│   ├── Filament/                             [Filament]
│   │   ├── Admin/
│   │   │   ├── Pages/
│   │   │   │   └── SchoolSettings.php        Form pengaturan sekolah & semester
│   │   │   ├── Resources/
│   │   │   │   ├── UserResource.php (+ aksi Reset Password)
│   │   │   │   ├── ClassroomResource.php
│   │   │   │   ├── SubjectResource.php
│   │   │   │   ├── TeachingResource.php
│   │   │   │   ├── AccountRequestResource.php
│   │   │   │   └── ActivityLogResource.php
│   │   │   └── Widgets/
│   │   │       └── SchoolStatsOverview.php
│   │   └── Teacher/
│   │       ├── Pages/
│   │       │   └── GradeReport.php           Rekap nilai siswa (Ringkasan & per kuis)
│   │       ├── Resources/
│   │       │   ├── MaterialResource.php      Koleksi PDF (Buku/Modul) & Tautan Video
│   │       │   └── QuizResource.php          Kuis Builder (Repeater soal A-D & Pembahasan)
│   │       └── Widgets/
│   │           └── TeacherStatsOverview.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                         [Breeze, Ubah] Login via username, tanpa tab peran
│   │   │   ├── HomeController.php            [Baru] Redirect "/" sesuai peran
│   │   │   ├── ProfileController.php         [Breeze, Ubah] Profil pengguna & ganti kata sandi
│   │   │   └── Student/                      [Baru] Controller Portal Siswa
│   │   │       ├── DashboardController.php   Beranda perpustakaan
│   │   │       ├── LibraryController.php     Katalog buku & penampil materi
│   │   │       ├── SubjectController.php     Kategori mata pelajaran
│   │   │       ├── ShelfController.php       Rak Saya (Bookmark)
│   │   │       ├── HistoryController.php     Riwayat bacaan
│   │   │       ├── VideoController.php       Galeri video pendukung
│   │   │       └── QuizController.php        Daftar kuis, runner kuis, & hasil review
│   │   ├── Middleware/
│   │   │   ├── EnsureRole.php                [Baru] Alias "role:"
│   │   │   └── HandleInertiaRequests.php     [Breeze, Ubah] Share user, settings, & flash message
│   │   ├── Requests/
│   │   │   ├── Auth/LoginRequest.php         [Breeze, Ubah] Validasi username + rate limiting
│   │   │   └── Student/
│   │   │       └── FinishQuizRequest.php     Jawaban array siswa
│   │   └── Resources/                        [Baru] Resource JSON untuk props Inertia
│   │       ├── SubjectResource.php
│   │       ├── MaterialResource.php
│   │       ├── QuizResource.php              Tanpa correct_index (keamanan)
│   │       └── QuizResultResource.php        Lengkap dengan kunci jawaban & pembahasan
│   │
│   ├── Models/                               [Baru]
│   │   ├── User.php                          implements FilamentUser
│   │   ├── Classroom.php
│   │   ├── Subject.php
│   │   ├── Teaching.php
│   │   ├── Material.php
│   │   ├── ShelfItem.php
│   │   ├── ReadingHistory.php
│   │   ├── Quiz.php
│   │   ├── Question.php
│   │   ├── QuizAttempt.php
│   │   ├── AccountRequest.php
│   │   ├── ActivityLog.php
│   │   └── SchoolSetting.php
│   │
│   ├── Policies/                             [Baru] Otorisasi ketat
│   │   ├── MaterialPolicy.php
│   │   ├── QuizPolicy.php
│   │   ├── UserPolicy.php
│   │   └── ClassroomPolicy.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/
│           ├── AdminPanelProvider.php        [Filament] /admin
│           └── TeacherPanelProvider.php      [Filament] /guru
│
├── bootstrap/app.php                         Alias middleware "role" & penanganan auth
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 2014_10_11_000000_create_classrooms_table.php
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── 2025_01_01_000003_create_subjects_table.php
│   │   ├── 2025_01_01_000004_create_teachings_table.php
│   │   ├── 2025_01_01_000005_create_materials_table.php       (tipe PDF/Video, status, url, provider)
│   │   ├── 2025_01_01_000008_create_quizzes_table.php         (relasi material_id, revisi, status)
│   │   ├── 2025_01_01_000009_create_questions_table.php       (pilihan A-D JSON, pembahasan)
│   │   ├── 2025_01_01_000010_create_quiz_attempts_table.php   (skor, revisi, jawaban)
│   │   ├── 2025_01_01_000012_create_shelf_items_table.php     (Rak Saya)
│   │   ├── 2025_01_01_000013_create_reading_histories_table.php (Riwayat Bacaan)
│   │   ├── 2025_01_01_000014_create_account_requests_table.php (Bantuan Akun / Lupa Password)
│   │   ├── 2025_01_01_000015_create_activity_logs_table.php    (Audit Trail)
│   │   └── 2025_01_01_000016_create_school_settings_table.php  (Pengaturan Sekolah)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── SubjectSeeder.php                 11 mata pelajaran
│       ├── ClassroomSeeder.php               VIII A, VIII B
│       ├── UserSeeder.php                    Nadia, Rafi, Salsa, Pak Arif, Bu Rina, Admin
│       ├── MaterialSeeder.php                Koleksi contoh buku PDF & video embed
│       └── QuizSeeder.php                    Contoh kuis Pythagoras (terkait materi) & mandiri
│
├── resources/
│   ├── css/
│   │   └── app.css                           Token warna & style kustom (Inter, teal, ink, cream)
│   └── js/
│       ├── Components/                       Komponen reusable Vue
│       │   ├── AppTopbar.vue                 Breadcrumbs, chip konteks, avatar profil
│       │   ├── AppSidebar.vue                Navigasi perpustakaan responsif
│       │   ├── CollectionReader.vue          Modal pembaca PDF in-app dengan Mode Fokus
│       │   ├── VideoPlayer.vue               Pemutar video YouTube / Vimeo / MP4
│       │   ├── BookCard.vue                  Kartu koleksi buku dengan tombol Love (Rak)
│       │   ├── SubjectCard.vue               Kartu kategori mata pelajaran
│       │   ├── Toast.vue (+ useToast)        Notifikasi aksi mengambang
│       │   └── EmptyState.vue
│       ├── Layouts/
│       │   ├── AuthenticatedLayout.vue
│       │   └── GuestLayout.vue
│       └── Pages/
│           ├── Auth/
│           │   ├── Login.vue                 Form login satu pintu + hero art buku
│           │   └── HelpDialog.vue            Dialog bantuan akun / lupa kata sandi
│           ├── Profile/
│           │   ├── Edit.vue                  Informasi akun & sekolah
│           │   └── Partials/
│           │       └── UpdatePasswordForm.vue
│           └── Student/
│               ├── Dashboard.vue             Beranda perpustakaan digital
│               ├── Library/
│               │   ├── Index.vue             Katalog buku PDF & pencarian
│               │   └── Show.vue              Detail buku & pembaca dokumen
│               ├── Subjects/
│               │   └── Index.vue             11 kategori mata pelajaran
│               ├── Shelf/
│               │   └── Index.vue             Rak Saya (koleksi tersimpan)
│               ├── History/
│               │   └── Index.vue             Riwayat buku yang dibaca
│               ├── Videos/
│               │   └── Index.vue             Koleksi video pendukung
│               └── Quizzes/
│                   ├── Index.vue             Daftar latihan & kuis kelas
│                   ├── Runner.vue            Runner kuis (1 soal per navigasi)
│                   └── Result.vue            Review nilai, kunci jawaban & pembahasan
```

---

## 2. Alur Interaksi Data & Keamanan

### A. Pembacaan Dokumen PDF di Browser
1. Siswa menekan **"Baca sekarang"** pada kartu buku.
2. `LibraryController@show` mengirimkan URL aman file PDF dan metadata materi, sekaligus mencatat rekaman ke tabel `reading_histories`.
3. Komponen `CollectionReader.vue` merender halaman demi halaman menggunakan pustaka PDF.js ke elemen `<canvas>`.
4. Jika tombol **Mode Fokus** diaktifkan, sidebar dan detail metadata disembunyikan agar layar terfokus penuh pada dokumen bacaan.

### B. Runner & Penilaian Kuis Pilihan Ganda
1. Guru membuat kuis melalui `QuizResource` di Filament dengan 4 pilihan opsi (A, B, C, D), kunci jawaban benar (`correct_index`), dan pembahasan.
2. Ketika siswa membuka kuis (`/siswa/latihan/{quiz}`), controller **menghilangkan kolom `correct_index` dan `explanation`** dari payload JSON yang dikirim ke browser siswa.
3. Siswa menjawab soal satu per satu di `Runner.vue` dan mengumpulkan jawaban lengkap.
4. Action `SubmitQuizAttempt` di server memeriksa jawaban siswa terhadap kunci jawaban yang tersimpan di database, menghitung skor nilai (0–100), dan menyimpan hasil ke `quiz_attempts`.
5. Siswa dialihkan ke halaman `Result.vue` yang kini menyajikan hasil evaluasi, nilai, kunci jawaban resmi, dan pembahasan.

### C. Rak Koleksi Pribadi (Bookmark)
1. Siswa menekan tombol hati (`♡` / `♥`) pada kartu buku di katalog atau beranda.
2. Request dikirim ke `siswa.shelf.toggle`.
3. Action `ToggleShelfItem` menambah atau menghapus relasi di tabel `shelf_items`.
4. State tombol berubah seketika dan memicu notifikasi toast informatif (*"Buku disimpan ke rak saya"*).
