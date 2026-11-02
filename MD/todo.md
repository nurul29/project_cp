# TODO — SUNDIGITAL

Acuan: `prd.md`, `arch.md` (v0.3), dan `ARCHITECTURE.md` (berbasis `SUNDIGITAL_Mockup.html` — Perpustakaan Digital SMP Negeri 16 Depok). Centang saat selesai.

---

## Fase 0 — Persiapan
- [x] Putuskan pertanyaan terbuka di PRD (fokus: Perpustakaan digital, PDF reader in-app, kuis pilihan ganda)
- [x] Install Laravel 12 dan cek kompatibilitas Breeze dan Filament
- [x] Install Breeze stack Vue + Inertia dengan Pest (**tanpa** `--ssr`)
- [x] Atur Git repository dan `.env` lokal
- [x] Siapkan database MySQL lokal
- [x] Pindahkan token warna mockup ke `tailwind.config.js` dan `app.css` (`ink`, `muted`, `teal`, `deep`, `mint`, `cream`, `line`, `orange`, `rose`, font Inter)

## Fase 1 — Database dan Data Awal
- [x] Enum dasar: `UserRole`, `MaterialType`
- [x] Migration awal: `users`, `classrooms`, `subjects`, `teachings`
- [x] Migration awal: `materials`, `assignments`, `submissions`, `quizzes`, `questions`, `quiz_attempts`, `portfolio_items`
- [x] Model dan relasi awal Eloquent
- [x] Seeder dasar: 11 mata pelajaran dan kelas contoh (VIII A, VIII B)
- [x] Migration tambahan fitur perpustakaan:
  - [x] Update `materials`: tambahkan `video_provider`, `size_bytes`, status `draft|published`
  - [x] Update `quizzes`: tambahkan `material_id` (foreign key opsional ke `materials`), status `draft|published`, `revision`
  - [x] Normalisasi status kuis lama `active` menjadi `published`
  - [x] Update `questions`: tambahkan kolom `explanation` (pembahasan soal)
  - [x] Tabel `shelf_items`: simpan buku favorit siswa (Rak Saya)
  - [x] Tabel `reading_histories`: simpan jejak bacaan siswa
  - [x] Tabel `account_requests`: penampung bantuan akun & lupa kata sandi
  - [x] Tabel `activity_logs`: log audit aktivitas sekolah
  - [x] Tabel `school_settings`: konfigurasi identitas sekolah & tahun ajaran
- [x] Model, relasi, dan enum baru:
  - [x] `VideoProvider`
  - [x] `PublicationStatus`
  - [x] `RequestStatus`
- [x] Seeder koleksi contoh lengkap:
  - [x] Buku/modul contoh, tautan video, dan kuis Pythagoras
  - [x] Hubungkan kuis contoh ke materi dan tambahkan kuis mandiri

## Fase 2 — Autentikasi dan Akun
- [x] Ubah login Breeze ke `username` (`LoginRequest`, controller)
- [x] Hapus registrasi publik dan verifikasi email
- [x] Middleware `EnsureRole` dan alias `role:`
- [x] Redirect `/` dan `/dashboard` setelah login sesuai peran
- [x] Rate limiting login (lockout 60 detik setelah 5x percobaan gagal)
- [x] Sesuaikan halaman `Pages/Auth/Login.vue` dengan `SUNDIGITAL_Mockup.html`:
  - [x] Form satu pintu tanpa tab peran (peran otomatis dari database)
  - [x] Tombol lihat/sembunyikan kata sandi interaktif
  - [x] Deteksi Caps Lock aktif (*caps-hint*)
  - [x] Dialog bantuan akun (*HelpDialog.vue*) untuk permohonan lupa kata sandi
- [x] Halaman profil pengguna (`Pages/Profile/Edit.vue`):
  - [x] Tampilkan informasi sekolah dan konteks akun sesuai mockup
  - [x] Ganti kata sandi mandiri
- [ ] Test autentikasi (saat ini PHPUnit): login valid, login gagal, rate limit lockout, dan redirect per peran
  - [x] Login valid/gagal dan redirect per peran
  - [ ] Uji lockout setelah lima percobaan gagal selama 60 detik

## Fase 3 — Portal Siswa: Perpustakaan & Membaca
- [x] `AuthenticatedLayout.vue` dengan `Sidebar.vue` dan `Topbar.vue` (avatar profil, chip konteks)
- [x] `HandleInertiaRequests`: share data pengguna, kelas, dan flash message
- [x] Sesuaikan navigasi siswa dengan acuan perpustakaan:
  - [x] **Beranda (`Student/Dashboard.vue`)**: hero perpustakaan, pencarian instan, pilihan koleksi, kategori mapel, banner video pendukung, dan aktivitas kelas
  - [x] Riwayat **bacaan terakhir** di beranda
  - [x] **Katalog Buku (`Student/Library/Index.vue`, `/siswa/katalog`)**:
    - [x] Pencarian judul/deskripsi/mapel, filter mapel, kartu koleksi, aksi baca dan simpan/hapus rak
    - [x] Penanda kuis terkait pada kartu materi
  - [x] **Kategori Mapel (`Student/Subjects/Index.vue`)**: grid seluruh kategori yang mengarahkan ke katalog terfilter
  - [x] **Rak Saya (`Student/Library/Shelf.vue`)**: daftar buku tersimpan dengan aksi hapus/baca
  - [x] **Riwayat Bacaan (`Student/Library/History.vue`)**: daftar kronologis buku yang dibuka
  - [x] **Video Pendukung (`Student/Videos/Index.vue`)**:
    - [x] Daftar video pembelajaran untuk kelas siswa
    - [x] Pemutaran YouTube, Vimeo, dan video langsung
- [x] Komponen **In-App PDF Reader (`CollectionReader.vue`)**:
  - [x] Render PDF dalam pembaca di aplikasi
  - [x] Pembaca PDF kustom via canvas
  - [x] Kontrol pagination (sebelumnya, berikutnya, input halaman)
  - [x] Kontrol zoom (pas lebar, 75%–200%)
  - [x] **Mode Fokus**: toggle fullscreen reading stage (sembunyikan panel samping)
  - [x] Panel informasi detail koleksi & tautan kuis terkait
- [x] Fungsi rak dan riwayat siswa di `LibraryController` (simpan/hapus koleksi dan rekam bacaan saat dibuka)

## Fase 4 — Portal Siswa: Latihan & Kuis Interaktif
- [x] `Student/Quizzes/Index.vue`:
  - [x] Daftar kuis tersedia untuk kelas dan nilai percobaan terakhir
  - [x] Tampilkan dengan jelas kuis mandiri vs kuis yang terkait dengan materi
- [x] `Student/Quizzes/Show.vue` (runner):
  - [x] Tampilan satu soal per layar dengan opsi A, B, C, D
  - [x] Nomor soal saat ini dan progress bar
  - [x] Navigasi langsung antarsoal dan indikator soal yang sudah dijawab
  - [x] Konfirmasi kelengkapan jawaban sebelum pengumpulan
- [x] Backend Keamanan Kuis:
  - [x] Payload soal ke siswa tidak memuat `correct_index` maupun `explanation`
  - [x] `FinishQuiz`: validasi, koreksi jawaban di server, kalkulasi skor (0–100), dan simpan ke `quiz_attempts`
- [x] Hasil kuis pada `Student/Quizzes/Show.vue`:
  - [x] Tampilkan skor akhir dan opsi coba lagi
  - [x] Review butir soal dengan kunci jawaban resmi dan pembahasan

## Fase 5 — Panel Guru (Filament)
- [x] Instalasi & konfigurasi panel Filament `guru` di `/guru`
- [x] Pembatasan otorisasi berdasarkan `teachings` (hanya mapel & kelas yang diampu)
- [x] **MaterialResource**:
  - [x] Upload dokumen PDF (buku/modul maks 50 MB) dengan dropzone
  - [x] Input tautan video (YouTube, Vimeo, MP4) dengan parser provider otomatis
  - [x] Pilihan kelas tujuan, kategori dokumen, ringkasan, dan status (Draf / Terbit)
- [x] **QuizResource (Kuis Builder)**:
  - [x] Form kuis: judul, kelas, petunjuk, opsi relasi materi (*Mandiri* vs *Terkait Buku*)
  - [x] Repeater soal: teks pertanyaan, opsi jawaban A–D, radio button kunci jawaban, pembahasan
  - [x] Urutan soal (naik/turun), duplikasi soal (*Salin*), dan hapus soal
  - [x] Pengelolaan versi/revisi kuis dan status Draf vs Terbit
- [x] **Halaman Rekap Nilai (`GradeReport`)**:
  - [x] Tab 1: Ringkasan Kelas (daftar siswa aktif, kuis selesai vs belum lengkap, rata-rata nilai tertinggi)
  - [x] Tab 2: Nilai per Kuis (status pengerjaan: sudah/sedang/belum mulai, nilai tertinggi, nilai terakhir, lembar jawaban siswa)
- [x] Widget Dashboard Guru: statistik buku, koleksi terbit/draf, video pendukung, dan siswa aktif

## Fase 6 — Panel Admin (Filament)
- [x] Instalasi & konfigurasi panel Filament `admin` di `/admin`
- [x] **UserResource**: CRUD siswa, guru, admin, filter peran & status aktif, aksi reset kata sandi langsung
- [x] **ClassroomResource**: CRUD kelas, wali kelas, tahun ajaran, dan status aktif/arsip
- [x] **SubjectResource**: CRUD kode, nama mata pelajaran, dan penugasan guru pengampu
- [x] **AccountRequestResource (Bantuan Akun)**: antrean permohonan lupa kata sandi & pencatatan tindak lanjut
- [x] **ActivityLogResource**: log audit aktivitas administratif sekolah
- [x] **SchoolSettingsPage**: pengaturan nama sekolah, tahun ajaran aktif, dan semester
- [x] Aturan Proteksi Data (*CRUD Safety Policies*):
  - [x] Blokir hapus akun jika masih menjadi wali kelas, pengampu, atau memiliki materi/kuis aktif
  - [x] Blokir hapus kelas jika masih ada siswa aktif
  - [x] Blokir hapus materi jika masih terikat pada kuis aktif

## Fase 7 — Kualitas, Aksesibilitas, dan Keamanan
- [ ] Validasi keamanan upload file PDF:
  - [x] Pembatasan tipe MIME PDF dan ukuran maksimal 50 MB di form upload
  - [ ] Uji validasi MIME dan ekstensi dengan berkas valid/tidak valid
- [ ] Uji responsivitas di 440px, 690px, 900px, dan 1150px
- [ ] Audit aksesibilitas: keyboard navigation pada PDF reader & kuis runner, `aria-live` toast, kontras warna
- [ ] Uji alur penuh: Guru unggah buku & kuis → Siswa baca PDF di browser & kerjakan kuis → Siswa lihat pembahasan → Guru pantau di rekap nilai

## Fase 8 — Persiapan Deploy
- [ ] Server environment (PHP 8.2+, MySQL, Composer, HTTPS)
- [ ] Konfigurasi `.env` produksi (`APP_DEBUG=false`)
- [ ] Build/deploy aset dan database:
  - [x] Build aset Vite (`npm run build`) berhasil di lingkungan lokal
  - [ ] Verifikasi migrasi, `storage:link`, dan optimasi cache pada lingkungan deploy
- [ ] Backup otomatis database dan direktori `storage`
