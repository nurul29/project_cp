# PRD — SUNDIGITAL

**Perpustakaan Digital SMP Negeri 16 Depok** · Versi 0.3 · Sumber Acuan: `SUNDIGITAL_Mockup.html`

---

## 1. Ringkasan

SUNDIGITAL adalah aplikasi web perpustakaan digital terpadu untuk lingkungan sekolah (SMP Negeri 16 Depok). Aplikasi ini berorientasi pada literasi digital (*Digital Library First*), memadukan penjelajahan katalog buku/modul PDF dengan pembaca dokumen di dalam aplikasi (*in-app PDF reader*), video pembelajaran pendukung, rak bacaan pribadi, serta asesmen pemahaman bacaan berupa latihan dan kuis pilihan ganda (baik yang terikat ke materi maupun mandiri).

* **Tagline:** *"Buka buku. Temukan ide. Tumbuh bersama."*
* **Nilai Inti:** **BACA • PAHAMI • TERAPKAN**

---

## 2. Tujuan Produk

1. **Pusat Sumber Belajar:** Memusatkan buku digital, materi pembelajaran, modul PDF, dan video pendukung dari 11 mata pelajaran dalam satu portal terpadu.
2. **Pengalaman Literasi Digital Nyaman:** Memberikan pengalaman membaca langsung di browser dengan kontrol zoom, navigasi halaman, dan *Mode Fokus* (*fullscreen reading stage*) tanpa perlu unduh file manual.
3. **Penguatan Pemahaman Materi:** Menyediakan asesmen formatif interaktif (kuis pilihan ganda terhubung dengan materi bacaan) lengkap dengan kunci jawaban dan pembahasan.
4. **Alat Kurasi Guru:** Memudahkan guru mengunggah buku/materi PDF, menyematkan tautan video (YouTube/Vimeo/MP4), merancang butir kuis, dan memantau rekap pengerjaan siswa.
5. **Tata Kelola Sekolah & Akun:** Memberikan kendali penuh kepada admin atas akun pengguna, kelas, mata pelajaran, bantuan pemulihan akses, dan pengaturan periode ajaran.

**Bukan tujuan (v1):** Sistem pembayaran/denda buku fisik, sirkulasi peminjaman buku fisik, absensi sekolah, chat/forum publik, aplikasi mobile native, integrasi e-rapor.

---

## 3. Pengguna dan Peran

| Peran | Deskripsi | Akses Utama | Contoh Akun Demo |
|---|---|---|---|
| **Siswa** | Menjelajahi katalog, membaca buku/modul PDF, menyimpan ke rak pribadi, menonton video, mengerjakan kuis, dan memantau nilai latihan | Portal Siswa (`/siswa/*`) | Nadia Aulia (VIII A) |
| **Guru** | Mengelola koleksi buku/materi PDF & video pendukung, menyusun kuis pilihan ganda, dan memantau rekap nilai siswa kelas yang diampu | Panel Guru (`/guru`) | Pak Arif (Matematika), Bu Rina (IPA) |
| **Admin** | Mengelola akun pengguna (siswa, guru, admin), rombel kelas, mapel, bantuan akun/lupa kata sandi, log aktivitas, dan pengaturan sekolah | Panel Admin (`/admin`) | Admin Sekolah |

---

## 4. Kebutuhan Fungsional

### 4.1 Autentikasi & Akses Akun
* **FR-A1 (Unified Login):** Halaman masuk satu pintu (`/login`) untuk semua peran dengan visual cover buku 3D dan kutipan literasi. Sistem membedakan peran otomatis dari database akun.
* **FR-A2 (Kredensial):** Siswa masuk menggunakan **NIS / Nama Pengguna**, guru dan admin menggunakan **Nama Pengguna / Email**.
* **FR-A3 (Interaktivitas Form):** Fitur lihat/sembunyikan kata sandi, deteksi tombol Caps Lock aktif (*caps-hint*), dan indikator proses (*spinner*).
* **FR-A4 (Keamanan & Rate Limiting):** Pembatasan percobaan login (lockout 60 detik setelah 5 kali gagal berturut-turut).
* **FR-A5 (Bantuan Akun & Lupa Kata Sandi):** Dialog pengajuan bantuan akun ("Lupa kata sandi / Belum punya akun") yang dicatat untuk diverifikasi secara manual oleh admin sekolah.
* **FR-A6 (Tanpa Registrasi Publik):** Semua akun dibuat dan dikelola oleh Administrator Sekolah.
* **FR-A7 (Profil & Ganti Kata Sandi):** Menu profil pengguna di topbar avatar untuk memeriksa data akun dan mengganti kata sandi mandiri (8–128 karakter).

### 4.2 Navigasi & Tata Letak Aplikasi
* **FR-L1 (Sidebar Responsif):** Logo identitas sekolah, kelompok menu navigasi utama, info pengguna aktif, dan tombol keluar. Di layar kecil (≤ 690px) bertransformasi menjadi navigasi horizontal yang ringkas.
* **FR-L2 (Topbar Dinamis):** Breadcrumbs navigasi, chip konteks (contoh: *Kelas VIII A • 11 pelajaran* atau *Tahun Ajaran 2026/2027*), avatar interaktif menuju profil saya, dan tombol keluar darurat di mobile.
* **FR-L3 (Umpan Balik / Toast):** Komponen notifikasi mengambang (*toast accessible* dengan `aria-live`) saat menyimpan ke rak, mengumpulkan kuis, atau memperbarui data.

### 4.3 Fitur Portal Siswa (Inertia + Vue)
* **FR-S1 (Beranda Perpustakaan):**
  * Sapaan personal siswa dan hero banner *"Satu perpustakaan, banyak pengetahuan"*.
  * Pencarian koleksi instan di bagian atas.
  * Bagian *Bacaan Terakhir* (buku/modul yang baru dibuka).
  * Bagian *Pilihan dari Koleksi* (rekomendasi bacaan).
  * Bagian *Jelajahi Kategori* (pilihan 11 mata pelajaran).
  * Banner pintasan ke *Video Pendukung* dan *Latihan Setelah Membaca*.
* **FR-S2 (Katalog Perpustakaan - `/siswa/perpustakaan`):**
  * Filter pencarian teks dan dropdown mata pelajaran.
  * Kartu koleksi: cover berwarna, kategori mapel, jenis dokumen (*Buku / Materi / Modul*), judul, tombol *Baca sekarang*, tombol simpan ke rak (`♡` / `♥`), dan indikator kuis tertaut.
* **FR-S3 (Kategori Pelajaran - `/siswa/mapel`):**
  * Grid 11 mata pelajaran dengan simbol, deskripsi, dan tone warna. Memilih kartu mengarahkan ke katalog yang telah terfilter sesuai mapel tersebut.
* **FR-S4 (Rak Saya - `/siswa/rak`):**
  * Daftar buku/materi yang disimpan (*bookmarked*) oleh siswa agar mudah diakses kembali.
* **FR-S5 (Riwayat Bacaan - `/siswa/riwayat-baca`):**
  * Jejak kronologis buku dan modul yang pernah dibuka dan dibaca oleh siswa.
* **FR-S6 (Video Pendukung - `/siswa/video`):**
  * Koleksi video pembelajaran terkurasi (YouTube, Vimeo, MP4) yang dapat diputar langsung di dalam aplikasi.
* **FR-S7 (Pembaca Dokumen Terintegrasi / In-App PDF Reader):**
  * Dialog penampil materi (`collection-reader`) berbasis canvas/PDF rendering.
  * Navigasi pagination halaman, zoom level (Pas Lebar, 75% – 200%).
  * **Mode Fokus:** Opsi menyembunyikan panel samping untuk pengalaman membaca layar penuh.
  * Teks dokumen untuk pembaca layar (*accessibility*).
  * Tautan langsung ke latihan kuis terkait pada panel samping bacaan.
* **FR-S8 (Latihan & Kuis - `/siswa/latihan`):**
  * Daftar kuis pilihan ganda yang tersedia untuk kelas siswa (kuis terhubung materi maupun kuis mandiri).
  * **Runner Kuis:** Satu soal per tampilan, opsi A/B/C/D, navigasi soal (*Sebelumnya/Berikutnya/Nomor Soal*), penanda soal terjawab, dan konfirmasi pengumpulan lengkap.
  * **Review Hasil:** Menampilkan skor nilai (0–100), jumlah jawaban benar, kunci jawaban, dan pembahasan di tiap butir soal.
  * Riwayat pengerjaan kuis siswa (termasuk opsi coba lagi jika diizinkan).

### 4.4 Fitur Portal Guru (Filament / Panel Guru)
* **FR-G1 (Beranda Guru):** Statistik koleksi bacaan, koleksi terbit vs draf, materi pendukung video, prioritas penilaian/rekap kuis, dan daftar kelas yang diampu.
* **FR-G2 (Koleksi Saya - `/guru/koleksi`):** Daftar seluruh buku dan materi yang dikelola oleh guru untuk kelas dan mata pelajaran yang diampunya.
* **FR-G3 (Tambah Koleksi - `/guru/materi`):**
  * Pemilihan sumber: Dokumen PDF (Buku, Materi, Modul maks 50 MB dengan dropzone) atau Tautan Video (YouTube, Vimeo, MP4 dengan deteksi provider otomatis).
  * Metadata: Judul, kategori mata pelajaran, kelas tujuan, ringkasan materi, dan status (Draf / Terbit).
  * Tombol pratinjau sebelum menerbitkan.
* **FR-G4 (Kelola Kuis - `/guru/kuis`):**
  * Kuis builder dinamis: judul, petunjuk, mata pelajaran, kelas tujuan, dan relasi materi (*Kuis Mandiri* vs *Terkait Materi Buku*).
  * Repeater butir soal: teks pertanyaan, 4 pilihan jawaban (A, B, C, D), penentuan 1 kunci jawaban benar, dan teks pembahasan.
  * Kontrol urutan soal (pindah atas/bawah), duplikasi butir soal (*Salin*), dan hapus butir.
  * Manajemen versi kuis (*revisi*) agar perubahan soal tidak merusak riwayat nilai yang telah dikumpulkan.
* **FR-G5 (Rekap Nilai Kuis - `/guru/rekap-nilai`):**
  * **Tab Ringkasan Kelas:** Menampilkan daftar siswa per kelas, jumlah kuis selesai vs belum lengkap, rata-rata nilai tertinggi, dan filter status pengerjaan.
  * **Tab Nilai per Kuis:** Rincian status per siswa (*Sudah mengerjakan, Sedang mengerjakan, Belum mulai*), nilai tertinggi, nilai terakhir, jumlah percobaan, dan pratinjau lembar jawaban siswa.

### 4.5 Fitur Portal Admin (Filament / Panel Admin)
* **FR-AD1 (Dashboard Ringkasan):** Metrik total pengguna aktif, kelas aktif, mata pelajaran, dan antrean bantuan akun yang memerlukan perhatian.
* **FR-AD2 (Manajemen Pengguna):** CRUD akun siswa, guru, dan administrator; filter berdasarkan peran dan status aktif; reset kata sandi langsung dengan verifikasi identitas fisik.
* **FR-AD3 (Manajemen Kelas):** CRUD rombongan belajar (nama kelas, wali kelas, tahun ajaran, dan status aktif/arsip).
* **FR-AD4 (Manajemen Mata Pelajaran):** Kode mapel, nama pelajaran, dan penugasan guru pengampu.
* **FR-AD5 (Bantuan Akun):** Daftar pengajuan lupa kata sandi / aktivasi akun, pemeriksaan identitas pemohon, dan pencatatan catatan penyelesaian.
* **FR-AD6 (Riwayat Aktivitas):** Log audit jejak rekam perubahan data sekolah (siapa, aksi apa, kapan).
* **FR-AD7 (Pengaturan Sekolah):** Identitas nama sekolah, tahun ajaran aktif (misal 2026/2027), dan semester berjalan (Ganjil/Genap).
* **FR-AD8 (CRUD Governance / Data Protection):** Aturan proteksi integritas:
  * Pencegahan penghapusan akun yang masih memiliki kuis/koleksi aktif atau menjabat wali kelas/pengampu.
  * Pencegahan penghapusan kelas yang masih memiliki siswa aktif.
  * Pencegahan penghapusan materi yang masih terikat pada kuis aktif.

---

## 5. Mata Pelajaran (Kategori Koleksi Awal)

11 mata pelajaran standar kurikulum SMP:
1. **Matematika** (Ikon: ∑, Tone: Green)
2. **IPA** (Ikon: 🔬, Tone: Blue)
3. **Bahasa Indonesia** (Ikon: ✎, Tone: Orange)
4. **IPS** (Ikon: 🌍, Tone: Pink)
5. **Bahasa Inggris** (Ikon: Aa, Tone: Lilac)
6. **Pendidikan Pancasila** (Ikon: ⚖, Tone: Yellow)
7. **Bahasa Sunda** (Ikon: ᮃ, Tone: Green)
8. **Seni Budaya** (Ikon: 🎨, Tone: Pink)
9. **PJOK** (Ikon: ⚽, Tone: Blue)
10. **Pendidikan Agama** (Ikon: ✦, Tone: Yellow)
11. **Informatika** (Ikon: ⌘, Tone: Lilac)

---

## 6. Kebutuhan Non-Fungsional

* **Performa & UX:** Navigasi siswa berbasis Inertia SPA (tanpa *full-page reload*). Lazy loading file PDF dan aset video.
* **Aksesibilitas (a11y):** Dukungan keyboard navigation pada pembaca PDF dan runner kuis, kontras warna rasio WCAG AA, atribut ARIA lengkap (`aria-live`, `aria-current`, `aria-pressed`, `aria-invalid`).
* **Responsif:** Tampilan optimal di resolusi 440px (ponsel), 690px (tablet vertikal), 900px (tablet landscape), hingga desktop lebar (1150px+).
* **Keamanan:**
  * Otorisasi ketat di level backend (Policies): siswa hanya mengakses materi & kuis kelasnya; guru hanya mengelola mapel & kelasnya.
  * Kunci jawaban kuis (`correct_index`) tidak pernah dikirimkan ke browser siswa saat pengerjaan berlangsung.
  * Validasi file upload: format `.pdf` valid, ukuran maksimal 50 MB, sanitasi nama file.
* **Penyimpanan:** Disiapkan untuk local storage (`storage/app/public`) dengan arsitektur abstraction yang siap dipindahkan ke cloud object storage (S3/GCS).
