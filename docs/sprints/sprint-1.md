# Sprint 1 — SIGALON

**Sumber:** Form J-01 Sprint Planning dan Sprint Backlog, Sprint ke-1 (sudah direkonsiliasi; lihat PRD v1.2 §12)  
**Periode sprint:** 12–25 Oktober 2026  
**Tanggal Sprint Planning (tertulis di J-01):** 30 September 2026 · **Anggota hadir:** 5  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa

## A. Sprint Goal
Membangun fondasi sistem SIGALON — autentikasi multi-peran, landing page dengan info produk dan peta cabang, pengelolaan data cabang dan akun staf, serta dashboard monitoring dasar — sehingga Super Admin dan Admin Cabang CV. Aufa sudah dapat mulai mengelola dan memantau cabang pilot secara digital sejak awal proyek.

## Aturan sprint
- Story belum `DONE` sampai acceptance criteria, tes, dan review terpenuhi.
- AI tidak melakukan commit/push/merge. AI hanya memberikan saran pesan commit.
- Semua task harus muncul pada papan tugas tim yang disepakati.
- Status diperbarui oleh pemilik/reviewer berdasarkan bukti di `docs/USER_STORIES_STATUS.md`.

## B. Sprint Backlog

| Kode US | Judul user story | Acceptance criteria (ringkas) | Prioritas | SP | PIC utama |
|---|---|---|---|---:|---|
| US-01 | Login staf (Super Admin, Admin Cabang, Kasir) ke website | Login dengan email & password sesuai role; role salah diarahkan ke akses ditolak; akun berstatus nonaktif tidak dapat login | Must | 3 | Reyhan |
| US-02 | Login Pelanggan menggunakan akun Google | Pelanggan dapat login via Google (Laravel Socialite); akun baru otomatis tersimpan (google_id, email); no HP & alamat dikosongkan untuk dilengkapi saat pesanan pertama | Must | 2 | Velisa |
| US-03 | Landing page menampilkan info produk, harga, dan lokasi cabang di peta | Menampilkan info produk serta harga isi ulang (Rp6.000) & antar-jemput (Rp7.000); peta Leaflet + OpenStreetMap dengan atribusi menampilkan titik cabang pilot dari tabel CABANG; nama & alamat muncul saat marker diklik; tersedia tombol Daftar dan Masuk | Should | 3 | Shalwa |
| US-04 | Dashboard Super Admin (monitor seluruh cabang, pilot 2 cabang) | Dashboard menampilkan ringkasan pendapatan & jumlah transaksi per cabang dan per kanal (walk-in, COD, canvassing); siap menampung hingga 13 cabang | Must | 5 | Reyhan |
| US-05 | Dashboard Admin Cabang (monitor cabang sendiri) | Dashboard hanya menampilkan data cabang Admin yang login; pembatasan cabang_id diperiksa di backend, bukan hanya di tampilan | Must | 3 | Velisa |
| US-06 | Super Admin mengelola data cabang dan akun Admin Cabang | Super Admin dapat menambah & mengubah data cabang (nama, alamat, latitude, longitude); menambah, mengubah, menonaktifkan/mengaktifkan, dan mereset password akun Admin Cabang; akun tidak dihapus | Should | 5 | Shalwa |
| US-07 | Admin Cabang mengelola akun Kasir & Kurir | Admin Cabang dapat menambah, mengubah, menonaktifkan/mengaktifkan akun Kasir/Kurir di cabangnya saja; akun tidak dihapus agar riwayat transaksi tetap utuh | Should | 3 | Nurul |

**Total story point terpilih:** 24 · **Kapasitas/velocity acuan:** belum diisi di J-01

## C. Breakdown task

| No | Kode US | Uraian task | Estimasi (jam) | PIC | Status awal |
|---:|---|---|---:|---|---|
| 1 | US-01 | Setup autentikasi & middleware role (Laravel Breeze/Sanctum) dengan role super_admin, admin_cabang, kasir, kurir dan pengecekan kolom aktif | 4 | Fikri | To Do |
| 2 | US-01 | Buat halaman login & redirect sesuai role; tolak akun nonaktif dengan pesan yang jelas | 3 | Reyhan | To Do |
| 3 | US-02 | Integrasi Laravel Socialite untuk login Google | 4 | Shalwa | To Do |
| 4 | US-02 | Simpan data pelanggan baru dari Google (google_id, email, nama) ke tabel PELANGGAN | 2 | Velisa | To Do |
| 5 | US-03 | Setup Leaflet.js + OpenStreetMap (dengan atribusi) di landing page | 3 | Velisa | To Do |
| 6 | US-03 | Tampilkan marker cabang pilot dari tabel CABANG beserta popup nama & alamat | 3 | Fikri | To Do |
| 7 | US-03 | Bagian info produk, harga isi ulang & antar-jemput, serta tombol Daftar/Masuk | 2 | Nurul | To Do |
| 8 | US-04 | Desain UI dashboard Super Admin | 3 | Nurul | To Do |
| 9 | US-04 | Query ringkasan pendapatan & transaksi per cabang dan per kanal | 4 | Shalwa | To Do |
| 10 | US-04 | Tampilkan grafik/statistik ringkas di dashboard | 3 | Fikri | To Do |
| 11 | US-05 | Desain UI dashboard Admin Cabang | 2 | Reyhan | To Do |
| 12 | US-05 | Query transaksi khusus cabang login (scoping cabang_id di backend) | 3 | Nurul | To Do |
| 13 | US-06 | Buat form tambah/edit akun Admin Cabang | 3 | Fikri | To Do |
| 14 | US-06 | Implementasi nonaktifkan/aktifkan & reset password akun Admin Cabang | 2 | Velisa | To Do |
| 15 | US-06 | Buat form kelola data cabang (nama, alamat, latitude, longitude) | 3 | Reyhan | To Do |
| 16 | US-07 | Buat form tambah/edit akun Kasir & Kurir | 3 | Shalwa | To Do |
| 17 | US-07 | Implementasi nonaktifkan/aktifkan akun Kasir & Kurir | 2 | Reyhan | To Do |

**Total estimasi:** 49 jam

## D. Kesepakatan tambahan sprint ini

| No | Hal yang disepakati | PIC |
|---:|---|---|
| 1 | Daily Scrum setiap pagi pukul 08.00 via WhatsApp Group | Seluruh tim |
| 2 | Durasi sprint 2 minggu (12–25 Oktober 2026) | Reyhan |
| 3 | Akun staf tidak dihapus, hanya dinonaktifkan (kolom aktif di USER_STAFF) | Seluruh tim |
| 4 | Scope data 2 cabang pilot; struktur siap diperluas ke 13 cabang | Seluruh tim |

## Catatan rekonsiliasi J-01 dengan kode (25 Oktober 2026)
Bagian ini bukan isi J-01. Ini catatan agar AI agent dan developer tidak salah membaca J-01 terhadap kode yang sudah ada.
- **Task 1 menyebut "Laravel Breeze/Sanctum".** Kode memakai Laravel Fortify (starter kit Livewire) untuk login web. Sanctum baru dibutuhkan untuk API Android (US-22, Sprint 3).
- **Task 4 menyebut tabel PELANGGAN.** Sudah sesuai sejak 25 Oktober: pelanggan pindah dari `users` ke tabel `pelanggan` dengan guard `pelanggan`.
- **US-01 memasukkan Kasir sebagai pengguna website.** Sudah sesuai sejak 25 Oktober: Kasir bisa masuk, dan Kurir diarahkan ke halaman akses ditolak.
- **US-02 di J-01 hanya mencakup Google.** Implementasi juga sudah membuat registrasi/login nomor HP, yang secara resmi adalah US-08 (Sprint 2).
- **Pemetaan nama ERD ke tabel fisik:** CABANG → `cabang` (`nama_cabang` = `nama`, ditambah `is_active`); USER_STAFF → `users` (`nama` = `name`, `aktif` = `is_active`); PELANGGAN → `pelanggan` (ditambah `google_avatar` dan `remember_token`). TRANSAKSI → `transaksi` sesuai ERD sejak US-04 (25 Oktober; `total_harga` disimpan sebagai bilangan bulat Rupiah).

## Referensi desain per story
Baca `docs/design/README.md` sebelum mengerjakan UI. Gambar hanya untuk gaya visual. Ikuti bagian "Jangan ambil" dan tabel konflik di sana.

| Story | Layar referensi (`docs/design/references/`) | Catatan |
|---|---|---|
| US-01 | `05-admin-dasbor-cabang.jpg` (kerangka sidebar/topbar), gaya kartu `02-pelanggan-masuk-daftar.jpg` | Tidak ada gambar login staf; pakai kartu sederhana email + password dengan token design system |
| US-02 | `02-pelanggan-masuk-daftar.jpg` | Tambahkan tombol Google; tanpa "Pesan sebagai Tamu" |
| US-03 | `01-pelanggan-landing.jpg` | Tambahkan peta Leaflet; jangan salin data/klaim dari gambar |
| US-04, US-05 | `05-admin-dasbor-cabang.jpg`, stat card `09-admin-laporan.jpg` | Hanya metrik yang datanya ada |
| US-06, US-07 | `10-admin-pengaturan-cabang-staf.jpg` | Tab profil cabang dan tabel staf saja |

## Keputusan yang berlaku
Ringkasan dari PRD v1.2 §6 dan §16. Bila berbeda, PRD yang berlaku.
- Role staf: `super_admin`, `admin_cabang`, `kasir`, `kurir`. Kasir memakai website **dan** Android; Kurir hanya Android.
- Pelanggan login dengan Google atau nomor HP + password (tanpa OTP); kedua akun tidak digabung otomatis.
- Semua harga ada di satu file konfigurasi (`config/sigalon.php`), bukan di tabel atau menu. Harga galon kosong baru masih nilai sementara.
- Satu transaksi berisi satu jenis item.
- Peminjaman galon dan WhatsApp API otomatis di luar scope; WhatsApp hanya tautan `wa.me`.
- Pilot 2 cabang; rancangan mendukung 13 cabang.

## Catatan untuk PIC story berikutnya
Diperbarui 25 Oktober 2026 setelah US-01–US-04. Baca sebelum mulai coding story Sprint 1 yang tersisa.

**Umum**
- **Akun uji lokal:** jalankan `php artisan migrate` lalu `php artisan db:seed`. Seeder membuat 2 cabang pilot dan 4 akun: `superadmin@sigalon.test`, `admin.cabang@sigalon.test`, `kasir@sigalon.test`, `kurir@sigalon.test` (staf cabang di cabang pertama). Password dari `SEED_DEFAULT_PASSWORD` di `.env`; jika kosong, password acak ditampilkan sekali. Akun ini tidak dibuat di production.
- **Akun staf** ada di `users` (guard `web`); **akun pelanggan** di `pelanggan` (guard `pelanggan`). Jangan mencampur keduanya.
- **Batas akses website staf:** Super Admin, Admin Cabang, dan Kasir boleh masuk; Kurir diarahkan ke `/akses-ditolak`. Middleware `EnsureStaffCanAccessWeb` memperlakukan semua route ber-middleware `auth` sebagai area staf.
- **Kerangka admin:** sidebar ada di `resources/views/layouts/app/sidebar.blade.php`. Tambahkan item menu baru di sana, dan tampilkan hanya untuk peran yang berhak; pembatasan tetap wajib di backend.
- **Warna:** pakai token Tailwind (`primary`, `primary-strong`, `deep`, `muted`, `surface`, dan seterusnya), bukan hex.

**US-05 — Dashboard Admin Cabang (Velisa)**
- Halaman dasbor sudah ada di `resources/views/pages/⚡dashboard.blade.php`. Ganti blok placeholder di cabang `@if ($this->ringkasan === null)`.
- Pakai `Transaksi::ringkasanPerCabangDanKanal($dari, $sampai, $user->cabang_id)`. **Selalu isi `cabang_id`** untuk peran selain Super Admin.
- Kasir juga membuka halaman ini. Tentukan bersama PO apakah Kasir melihat ringkasan yang sama dengan Admin Cabang.

**US-06 — Data cabang dan akun Admin Cabang (Shalwa)**
- Model `Cabang` memakai kolom `nama`, `alamat`, `latitude`, `longitude`, `is_active`.
- Nama dan alamat cabang tampil di landing page dan popup peta. Popup sudah memakai `textContent`; tetap tampilkan dengan `{{ }}` di Blade.
- Akun tidak dihapus, hanya dinonaktifkan (`is_active`). Sesi akun yang dinonaktifkan otomatis dikeluarkan oleh middleware.
- Kolom `role` dan `cabang_id` sengaja tidak masuk `Fillable` di model `User`; isi secara eksplisit setelah validasi dan otorisasi.

**US-07 — Akun Kasir dan Kurir (Nurul)**
- Pakai pola kelola akun dari US-06. Admin Cabang hanya boleh membuat dan mengubah akun dengan `cabang_id` miliknya sendiri; periksa di backend, bukan hanya di form.
- Factory sudah punya state `kasir()`, `kurir()`, dan `inactive()` untuk tes.

## Definition of Ready
- [ ] Tujuan story dan aktor jelas
- [ ] Acceptance criteria bisa diuji
- [ ] Dependensi teridentifikasi
- [ ] Estimasi disepakati tim
- [ ] UI/data/API cukup jelas
- [ ] Story cukup kecil untuk sprint

## Definition of Done
- [ ] Acceptance criteria terpenuhi
- [ ] Tes relevan dijalankan dan hasil dicatat
- [ ] Otorisasi dan batas cabang diuji bila relevan
- [ ] Loading/empty/error/success state ditangani bila relevan
- [ ] Tidak ada bug blocker/critical yang diketahui
- [ ] Perubahan direview
- [ ] Status dan bukti diperbarui
- [ ] AI memberikan saran commit saja; commit dilakukan manual oleh developer

## Catatan status per story

Tambahkan catatan dengan format:
- **Tanggal:**
- **Story/Task:**
- **Dikerjakan oleh:**
- **Perubahan:**
- **Acceptance criteria yang lulus:**
- **Tes dan hasil:**
- **Yang belum selesai / blocker:**
- **Reviewer:**
- **Status yang diusulkan:**
- **Saran pesan commit (tidak dijalankan):**

### US-01 — Login staf web dengan role
- **Tanggal:** 19 Oktober 2026
- **Story/Task:** US-01, branch `feature/US-01-login-staf-web-with-role`
- **Dikerjakan oleh:** Reyhan (PIC) dengan bantuan AI (Claude Code)
- **Keputusan PO yang dipakai (19 Okt 2026):**
  - Kasir/Kurir ditolak di website dan diarahkan ke aplikasi Android.
  - Akun nonaktif mendapat pesan umum.
  - Fitur starter kit "hapus akun sendiri", "mode tampilan", dan "lupa kata sandi via email" dihapus. Reset kata sandi dilakukan admin lewat US-06/US-07.
- **Perubahan:**
  - `Fortify::authenticateUsing` di `app/Providers/FortifyServiceProvider.php`.
  - `Role::canAccessWeb()` dan `User::canAccessStaffWeb()`.
  - Middleware `app/Http/Middleware/EnsureStaffCanAccessWeb.php` dipasang di grup `web`; sesi yang tidak berhak dikeluarkan di setiap request.
  - Halaman login staf dan halaman 429 berbahasa Indonesia.
  - Kerangka admin: sidebar dengan "Lokasi aktif" dan label peran, mode terang saja.
  - Terjemahan di `lang/id.json`, `lang/id/auth.php`, `lang/id/validation.php`.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Super Admin dan Admin Cabang bisa masuk.
  - Kasir dan Kurir ditolak dengan pesan Android.
  - Akun nonaktif, Admin Cabang tanpa cabang, password salah, dan email tidak dikenal mendapat pesan umum yang sama.
  - Kasir nonaktif tidak membocorkan perannya.
  - Percobaan dibatasi 5 kali per menit.
  - Sesi Kasir dan akun yang dinonaktifkan di tengah sesi dikeluarkan.
  - Tamu diarahkan ke halaman masuk.
  - Sidebar hanya menampilkan cabang sendiri dan peran pengguna.
  - Fitur yang dihapus mengembalikan 404.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 50/50 lulus.
  - `npm run build` berhasil.
  - Smoke test HTTP: `/login` 200; `/register`, `/forgot-password`, `/settings/appearance` 404; `/dashboard` sebagai tamu 302.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Tampilan belum diperiksa manual di browser (desktop dan ponsel).
  - Isi halaman dasbor masih placeholder starter kit (bagian US-04/US-05).
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-01): restrict admin website login to super admin and admin cabang`

### US-02 — Login pelanggan
- **Tanggal:** 20 Oktober 2026
- **Story/Task:** US-02, branch `feature/US-02-login-pelanggan`
- **Dikerjakan oleh:** Velisa (PIC) dengan bantuan AI (Antigravity)
- **Keputusan PO yang dipakai (20 Okt 2026):**
  - Autentikasi mencakup login Google DAN nomor HP + kata sandi.
  - Akun pelanggan menggunakan tabel `users` dengan `Role::Pelanggan`.
  - Akun Google dan akun nomor HP berdiri sendiri (tidak ada penautan akun otomatis untuk mencegah risiko pengambilalihan akun tanpa OTP).
  - Login Google mencari user hanya via `google_id`; jika email sudah dipakai akun lain, akses ditolak secara aman.
  - Tautan bantuan lupa kata sandi mengarah ke `wa.me` nomor admin dari konfigurasi environment (`ADMIN_WHATSAPP_NUMBER` di `config/services.php`).
  - Setelah login berhasil, pelanggan diarahkan ke `route('home')`.
- **Perubahan:**
  - Migrasi `database/migrations/2026_10_20_000001_add_pelanggan_fields_to_users_table.php` (menambahkan `phone`, `google_id`, nullable `email` dan `password`).
  - Penambahan `Role::Pelanggan` dan helper `Role::staffCases()` pada `app/Enums/Role.php`.
  - Model `app/Models/User.php` diperbarui dengan fillable `phone`, `google_id`, dan helper `isPelanggan()`.
  - Middleware `app/Http/Middleware/EnsureStaffCanAccessWeb.php` disesuaikan untuk mengizinkan pelanggan di halaman publik/pelanggan (`isCustomerPage()`) dan memblokir (403) akses pelanggan ke area staf (`/dashboard`, `/settings/*`), serta mengarahkan pelanggan dari `/login` ke `route('home')`.
  - Controller `app/Http/Controllers/Auth/CustomerAuthController.php` (login nomor HP, registrasi pelanggan, logout) dan `app/Http/Controllers/Auth/GoogleAuthController.php` (Google OAuth via Socialite).
  - Tampilan kartu form masuk/daftar pelanggan di `resources/views/pages/auth/customer-login.blade.php` mengacu pada referensi desain `02-pelanggan-masuk-daftar.jpg` dengan token Aqua Professional.
  - Komponen navbar pelanggan di `resources/views/components/customer-navbar.blade.php` dan integrasi minimal di `resources/views/welcome.blade.php`.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - AC 2.1: Pelanggan dapat masuk dengan nomor HP + password valid dan diarahkan ke `home`.
  - AC 2.2: Pendaftaran pelanggan baru via nomor HP tersimpan dengan role `pelanggan` dan otomatis login.
  - AC 2.3: Pelanggan dapat login/daftar via Google OAuth.
  - AC 2.4: Nomor HP/password salah mengembalikan pesan error umum tanpa membocorkan nomor terdaftar.
  - AC 2.5: Percobaan login nomor HP dibatasi maksimal 5 kali per menit (HTTP 429).
  - AC 2.6: Akun pelanggan nonaktif ditolak saat masuk.
  - AC 2.7: Staf yang mencoba login di form pelanggan ditolak dengan pesan yang mengarahkan ke `/login`.
  - AC 2.8: Navbar di `home` menampilkan identitas pelanggan dan tombol keluar saat login aktif.
  - AC 2.9: Pelanggan dilarang keras mengakses dasbor dan pengaturan staf (HTTP 403), dan dialihkan saat membuka `/login`.
  - AC 2.10: Link bantuan lupa kata sandi mengarah ke `wa.me` admin dari konfigurasi environment.
- **Tes dan hasil:**
  - `vendor/bin/pint --test`: Lulus.
  - `vendor/bin/phpstan analyse --memory-limit=1G`: 0 error.
  - `php artisan test`: 74/74 tes lulus (238 assertions), mencakup 50 tes lama US-01 dan 24 tes baru US-02.
  - `npm run build`: Berhasil tanpa error.
- **Catatan untuk PIC story berikutnya (US-03):**
  - Story berikutnya yang menambahkan rute/halaman publik pelanggan baru harus mendaftarkan pola rutenya ke method `isCustomerPage()` di `app/Http/Middleware/EnsureStaffCanAccessWeb.php` agar sesi pelanggan tidak tertolak.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Nilai asli `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, dan `ADMIN_WHATSAPP_NUMBER` masih menggunakan placeholder/nomor contoh sementara.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-02): implement customer authentication via phone and google oauth`

### US-03 — Landing page dan peta cabang
- **Tanggal:** 20 Oktober 2026
- **Story/Task:** US-03, branch `feature/US-03-landing-page-dan-peta-cabang`
- **Dikerjakan oleh:** Shalwa (PIC) dengan bantuan AI (Antigravity)
- **Keputusan PO yang dipakai (20 Okt 2026):**
  - Menggunakan 2 cabang nyata di Kota Padang: Cabang Dadok Tunggul Hitam (`Jl. Utama Dadok Tunggul Hitam, Padang`) dan Cabang Siteba (`Jl. Siteba, Padang`).
  - `CabangSeeder` dibuat idempotent (`updateOrCreate` dengan kunci nama cabang) dan tidak dijalankan otomatis di `DatabaseSeeder`. Dapat dijalankan manual: `php artisan db:seed --class=CabangSeeder`.
  - Koordinat latitude/longitude di `CabangSeeder` (-0.8715, 100.3640 untuk Dadok Tunggul Hitam dan -0.8980, 100.3680 untuk Siteba) adalah perkiraan untuk pengujian, butuh verifikasi PO via Google Maps/OSM sebelum dianggap final.
  - Alamat cabang ditulis sesuai data PO tanpa menambah detail fiktif.
  - Peta Leaflet + OpenStreetMap membaca seluruh cabang aktif (`is_active = true` dan memiliki koordinat) secara dinamis tanpa pembatasan jumlah di kode; `fitBounds` otomatis menyesuaikan sebaran marker.
  - Harga resmi PRD §6: Walk-in Rp6.000/galon dan COD Rp7.000/galon. Kartu galon kosong baru hanya tampil jika `sigalon.harga.galon_baru` dikonfigurasi Super Admin (tidak di-hardcode). Tampilan katalog disajikan dalam 1 baris (3 kolom di desktop).
  - Foto stasiun pengisian depot modern (`public/images/depot-refill.jpg`) ditambahkan ke kolom kanan Hero section sesuai referensi visual `01-pelanggan-landing.jpg`.
  - Revisi PO Active State Navbar:
    - Navbar menandai seksi aktif (Beranda, Layanan & Harga, Cara Pesan, Lokasi Cabang) dengan warna primer teal (`text-primary`), ketebalan `font-semibold`, garis bawah tipis (`border-b-2 border-primary`), dan atribut aksesibilitas `aria-current="page"` (kontras WCAG AA terpenuhi).
    - Berubah saat menu diklik (state terkunci selama animasi smooth-scroll untuk mencegah flicker) dan saat scroll manual (scroll-spy ringan menggunakan `IntersectionObserver`).
    - Halaman dibuka di posisi atas mengaktifkan "Beranda"; jika dibuka dengan hash (misal `/#lokasi-cabang`), seksi terkait langsung aktif.
    - Seksi halaman diberi offset anchor `scroll-mt-20` dan `scroll-pt-20` sehingga konten tidak tertutup sticky navbar saat dinavigasi.
    - Jika `<x-customer-navbar />` digunakan di halaman non-landing page, script keluar dengan aman tanpa error dan mereset penanda aktif anchor.
  - Tautan WhatsApp mengarah ke `wa.me` nomor admin dengan pesan pembuka terisi.
  - `LandingPageTest` tidak bergantung pada seeder nyata (menggunakan factory dan data uji sendiri, tanpa hardcode jumlah cabang).
- **Perubahan:**
  - Controller `app/Http/Controllers/LandingPageController.php` (menyiapkan data cabang aktif, harga resmi, dan tautan WhatsApp).
  - Seeder `database/seeders/CabangSeeder.php` (idempotent, 2 cabang nyata dengan koordinat perkiraan).
  - Konfigurasi `config/sigalon.php` (menambahkan konfigurasi tarif harga).
  - Rute `routes/web.php` (menghubungkan `/` ke `LandingPageController`).
  - View `resources/views/welcome.blade.php` (merombak landing page dengan tema Aqua Professional, seksi harga, alur pemesanan 3 langkah, peta Leaflet.js dengan atribusi OpenStreetMap, foto hero depot, offset `scroll-mt-20`, dan isolasi layer peta `isolate z-0`).
  - Komponen `resources/views/components/customer-navbar.blade.php` (navigasi anchor seksi landing page dengan active state vanilla JS, scroll-spy IntersectionObserver, aksesibilitas `aria-current="page"`, CTA Pesan Air Galon, dan status sesi).
  - Pengujian `tests/Feature/LandingPageTest.php` (16 skenario tes komprehensif untuk seluruh AC dan active state navbar).
- **Acceptance criteria yang lulus (diuji otomatis):**
  - AC 3.1: Halaman landing page dapat diakses publik dengan layout Aqua Professional responsif dan foto hero depot yang selaras dengan panduan visual.
  - AC 3.2: Transparansi harga Walk-in Rp6.000 dan COD Rp7.000 tertera jelas. Kartu galon kosong baru hanya muncul jika dikonfigurasi dan tersusun rapi 1 baris di desktop.
  - AC 3.3: Peta Leaflet interaktif menampilkan marker cabang aktif dengan popup nama dan alamat, lengkap dengan atribusi wajib OpenStreetMap. Peta terisolasi secara stacking context sehingga tidak menutupi navbar saat scroll.
  - AC 3.4: Daftar kartu cabang terintegrasi dengan aksi pemusatan peta (pan/zoom) ke marker cabang.
  - AC 3.5: Seksi 3 langkah pemesanan dan tautan WhatsApp admin `wa.me` dengan pesan terisi berfungsi.
  - AC 3.6: Pelanggan login dan staf dapat mengakses landing page tanpa terhalang middleware keamanan; navbar menampilkan informasi sesi yang tepat.
  - AC 3.7 (Revisi PO): Active state navbar berfungsi presisi di desktop & mobile, scroll-spy via IntersectionObserver aktif, offset sticky navbar tidak memotong judul seksi, dan aksesibilitas `aria-current="page"` terpasang.
  - Seeder `CabangSeeder` teruji idempotent saat dijalankan berulang.
- **Tes dan hasil:**
  - `vendor/bin/pint`: Lulus tanpa pelanggaran gaya kode.
  - `vendor/bin/phpstan analyse`: 0 errors.
  - `composer test`: 90/90 tes lulus (302 assertions, durasi ~6.8s), mencakup 74 tes lama US-01 & US-02 dan 16 tes US-03.
  - `npm run build`: Berhasil tanpa error (asset Vite terkompilasi bersih).
  - Pengujian visual browser: Terverifikasi langsung di browser peramban (desktop dan mobile menu, klik anchor smooth-scroll, dan scroll-spy aktif).
- **Catatan untuk PIC story berikutnya (US-04 / Dashboard Super Admin):**
  - Model `Cabang` telah siap dan memiliki data 2 cabang nyata via `CabangSeeder`.
  - Jika memerlukan data cabang di database lokal saat pengembangan, jalankan `php artisan db:seed --class=CabangSeeder`.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Koordinat latitude/longitude di `CabangSeeder` masih perkiraan dan butuh verifikasi PO via Google Maps/OSM.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-03): implement navbar active state with intersection observer scroll-spy and anchor clearance`

### Perbaikan US-01–US-03 sesuai PRD v1.2 dan ERD v1.1
- **Tanggal:** 25 Oktober 2026
- **Story/Task:** US-01, US-02, US-03 (perbaikan), branch `fix/sprint-1-penyesuaian-prd-v1.2`
- **Dikerjakan oleh:** Reyhan dengan bantuan AI (Claude Code)
- **Keputusan PO yang dipakai (25 Okt 2026):**
  - Data pelanggan dipindah ke tabel `pelanggan` sesuai ERD.
  - Kartu galon baru di landing page tetap ditampilkan.
  - Koordinat 2 cabang di `CabangSeeder` sudah benar.
- **Perubahan:**
  - **US-01:**
    - Kasir boleh masuk website (`Role::canAccessWeb()`).
    - Kurir yang masuk, atau sesi Kurir yang aktif, diarahkan ke halaman `/akses-ditolak` (view `errors/403`).
    - Pelanggan yang membuka area staf mendapat 403 "Akses ditolak".
  - **US-02:**
    - Migration `2026_10_25_163131_create_pelanggan_table_and_move_customers` membuat tabel `pelanggan` (dengan kolom `alamat`) dan memindahkan pelanggan lama dari `users`.
    - Model `App\Models\Pelanggan`, guard dan provider `pelanggan` di `config/auth.php`, serta `PelangganFactory`.
    - Role `pelanggan` dihapus dari `users`.
    - Login Google tidak lagi memakai fallback `stateless()`, dan pesan error teknis tidak ditampilkan ke pelanggan.
    - Pesan nomor terdaftar disamakan dengan teks wajib PRD §6.
  - **US-03:**
    - Popup peta dibangun dengan `textContent` untuk menutup celah XSS.
    - Warna hex yang ditulis langsung di popup diganti token.
  - **Palet PRD v1.2:** token di `resources/css/app.css` diganti; `primary-bright` diganti `primary-strong`; token baru `brand`, `brand-bright`, `deep`.
  - **Dokumen** yang diselaraskan: `docs/DESIGN_SYSTEM.md`, `docs/design/README.md`, `CLAUDE.md`, `AGENTS.md`, `config/sigalon.php`, `docs/PRD.md` §11 (catatan kontras).
- **Acceptance criteria yang lulus (diuji otomatis):**
  - US-01: Kasir masuk; Kurir diarahkan ke akses ditolak; akun nonaktif dan Kasir tanpa cabang ditolak; sesi Kurir dikeluarkan.
  - US-02: akun Google baru tersimpan di `pelanggan` dengan `no_hp`/`alamat` kosong; sesi pelanggan bukan sesi staf; `state` OAuth yang tidak valid ditolak tanpa fallback; pesan error teknis tidak tampil.
  - US-03: nama dan alamat cabang yang berisi skrip ditampilkan ter-escape.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 94/94 (332 assertions).
  - `npm run build` berhasil.
  - Migration diuji di database MariaDB terpisah dengan data contoh: pelanggan nomor HP dan Google pindah utuh; akun staf utuh; sesi pelanggan lama dihapus; rollback mengembalikan data.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Cek visual di browser (palet baru, halaman akses ditolak, popup peta) belum dilakukan.
  - Setiap anggota perlu menjalankan `php artisan migrate` dan `npm run build` setelah merge. Pelanggan yang sedang login akan keluar sekali.
  - Pelanggan nonaktif tidak lagi didukung karena ERD PELANGGAN tidak memiliki kolom aktif.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** lihat laporan handoff

### US-04 — Dashboard Super Admin
- **Tanggal:** 25 Oktober 2026
- **Story/Task:** US-04 (task 8–10) + penyelarasan tabel TRANSAKSI dengan ERD, branch `feature/US-04-dashboard-super-admin`
- **Dikerjakan oleh:** Reyhan (PIC) dengan bantuan AI (Claude Code)
- **Keputusan yang dipakai** (rekomendasi AI di Tahap 2; PO melanjutkan tanpa koreksi):
  - Periode ringkasan: Hari ini (default) dan Bulan ini, berdasarkan kolom `tanggal`.
  - "Jumlah transaksi" memakai aturan yang sama dengan pendapatan (PRD §6): walk-in/COD `selesai` dan canvassing `tervalidasi`.
  - Statistik ringkas memakai batang Tailwind tanpa library grafik baru (PRD §16 terbuka no. 5 tetap terbuka untuk US-20).
  - Dasbor Admin Cabang dan Kasir menampilkan placeholder tanpa angka sampai US-05.
  - `total_harga` disimpan sebagai bilangan bulat Rupiah (ERD menulis `decimal`).
  - Penyelarasan TRANSAKSI dikerjakan di branch ini, bukan branch terpisah.
- **Perubahan:**
  - **TRANSAKSI:** migration `2026_10_25_170655_recreate_transaksi_table_sesuai_erd` (tabel dibuat ulang karena belum pernah diisi fitur apa pun); enum baru `StatusTransaksi`; nilai `JenisItem` menjadi `isi_ulang`/`galon_baru`; model `Transaksi` dengan relasi ERD, scope `sah()` dan `antaraTanggal()`, serta method `ringkasanPerCabangDanKanal()` yang bisa dipakai ulang US-05/US-20; `TransaksiFactory` dengan state `cod()` dan `canvassing()`.
  - **Dasbor:** halaman Livewire `resources/views/pages/⚡dashboard.blade.php` menggantikan `resources/views/dashboard.blade.php`; route `dashboard` memakai `Route::livewire`.
- **Acceptance criteria yang lulus (diuji otomatis di `SuperAdminDashboardTest`):**
  - Ringkasan pendapatan dan jumlah transaksi total, per kanal, dan per cabang cocok dengan data uji.
  - Hanya transaksi sah yang dihitung; COD `diproses`/`diantar`/`dibatalkan` dan canvassing `menunggu_verifikasi`/`ditolak` tidak dihitung.
  - Periode Hari ini tidak memuat hari sebelumnya; Bulan ini memuat awal bulan dan tidak memuat bulan lalu.
  - 13 cabang tampil semua.
  - Admin Cabang dan Kasir tidak pernah menerima angka atau nama cabang lain; Kurir diarahkan ke akses ditolak.
  - Empty state tampil saat belum ada transaksi; periode tidak valid kembali ke Hari ini; nama cabang ter-escape.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 105/105 (378 assertions).
  - `npm run build` berhasil.
  - Query ringkasan diuji di database MariaDB terpisah (hari ini, bulan ini, filter satu cabang): hasil sesuai.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Tampilan belum diperiksa manual di browser (desktop dan ponsel).
  - Di data nyata dasbor masih kosong sampai pencatatan transaksi Sprint 2 (US-09–US-11) selesai.
- **Catatan untuk US-05 (Velisa):** pakai `Transaksi::ringkasanPerCabangDanKanal($dari, $sampai, $user->cabang_id)` dan ganti blok placeholder di `⚡dashboard.blade.php` (cabang `@if ($this->ringkasan === null)`). Jangan pernah memanggilnya tanpa `cabang_id` untuk peran selain Super Admin.
- **Reviewer:** belum ada
### US-06 — Data cabang dan akun Admin Cabang
- **Tanggal:** 25 Oktober 2026
- **Story/Task:** US-06 (task 13–15), branch `feature/US-06-super-admin-mengelola-data-cabang-dan-akun-admin-cabang`
- **Dikerjakan oleh:** Shalwa (PIC) dengan bantuan AI (Antigravity)
- **Keputusan yang dipakai (disetujui PO):**
  - Reset password Admin Cabang dilakukan langsung oleh Super Admin dengan password baru minimal 8 karakter; password di-hash.
  - Validasi koordinat menggunakan `nullable|numeric` untuk latitude dan longitude.
  - Status aktif/nonaktif cabang ditampilkan di daftar, tanpa fitur toggle status cabang.
  - UI menggunakan dua halaman terpisah: Kelola Cabang (`/admin/cabang`) dan Kelola Admin Cabang (`/admin/admin-cabang`).
  - Akun tidak dihapus, hanya dinonaktifkan (`is_active`).
- **Perubahan:**
  - **Controllers:** `CabangController` (index, create, store, edit, update) dan `AdminCabangController` (index, create, store, edit, update, toggleActive, showResetPassword, resetPassword).
  - **Views:** 7 file view terpisah di `resources/views/pages/admin/` (`cabang/index`, `cabang/create`, `cabang/edit`, `admin-cabang/index`, `admin-cabang/create`, `admin-cabang/edit`, `admin-cabang/reset-password`).
  - **Routes:** Route resource dan custom endpoints di dalam group middleware `auth` dan prefix `admin` pada `routes/web.php`.
  - **Sidebar:** Item menu `Kelola Cabang` dan `Admin Cabang` di `resources/views/layouts/app/sidebar.blade.php`, khusus untuk Super Admin.
  - **Tests:** `tests/Feature/Admin/CabangManagementTest.php` dan `tests/Feature/Admin/AdminCabangManagementTest.php`.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Super Admin dapat melihat daftar cabang, menambah cabang baru, dan mengubah data cabang (nama, alamat, latitude, longitude).
  - Koordinat latitude dan longitude tersimpan dengan benar dan validasi numeric/nullable terpenuhi.
  - Nama dan alamat cabang di-escape di Blade untuk mencegah XSS.
  - Super Admin dapat melihat daftar Admin Cabang, membuat akun baru (role terisi `admin_cabang`, terikat `cabang_id`, password ter-hash).
  - Super Admin dapat memperbarui nama, email, dan cabang penempatan Admin Cabang.
  - Super Admin dapat menonaktifkan dan mengaktifkan akun Admin Cabang; akun tidak dihapus dari database.
  - Super Admin dapat mereset kata sandi Admin Cabang secara langsung dengan konfirmasi; kata sandi baru tersimpan dalam bentuk hash dan dapat dipakai login.
  - Otorisasi server-side: hanya Super Admin yang diizinkan mengakses seluruh endpoint kelola cabang dan Admin Cabang; Admin Cabang dan Kasir menerima respons 403 Forbidden; tamu diarahkan ke `/login`.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 129/129 lulus (488 assertions).
  - `npm run build` berhasil.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi tampilan visual manual di browser belum dilakukan.
- **Catatan untuk US-07 (Nurul):** Gunakan pola controller dan view dari US-06 ini untuk mengelola akun Kasir dan Kurir. Pastikan otorisasi membatasi Admin Cabang hanya dapat mengelola staf pada `cabang_id` miliknya sendiri.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** lihat laporan handoff

### US-07 — Akun Kasir dan Kurir
- **Tanggal:** 25 Oktober 2026
- **Story/Task:** US-07 (task 16–17), branch `feature/US-07-kelola-kasir-kurir`
- **Dikerjakan oleh:** Nurul (PIC) dengan bantuan AI (Antigravity)
- **Keputusan yang dipakai (mengikuti pola US-06 & PRD):**
  - Admin Cabang hanya dapat mengelola staf pada cabang miliknya sendiri (`cabang_id` terikat otomatis pada saat create dan divalidasi server-side).
  - Role yang dapat dibuat dan diubah hanya Kasir (`kasir`) dan Kurir (`kurir`). Upaya memasukkan role lain ditolak (422).
  - Akun tidak dihapus, hanya dinonaktifkan (`is_active`). Sesi staf yang dinonaktifkan ditolak oleh middleware web.
  - Reset kata sandi staf oleh Admin Cabang disediakan langsung tanpa via email (minimal 8 karakter, di-hash).
  - UI tabel menampilkan nama, inisial avatar, email, badge peran (Kasir/Kurir), status keaktifan, dan tombol aksi (Ubah, Reset Sandi, Toggle Aktif).
- **Perubahan:**
  - **Model:** Penambahan helper `isAdminCabang(): bool` di `app/Models/User.php`.
  - **Controller:** `App\Http\Controllers\Cabang\StafController` dengan proteksi otorisasi Admin Cabang dan penegakan isolasi cabang pada setiap aksi.
  - **Views:** 4 file view di `resources/views/pages/cabang/staf/` (`index.blade.php`, `create.blade.php`, `edit.blade.php`, `reset-password.blade.php`).
  - **Routes:** Route resource dan endpoint toggle-active / reset-password di dalam prefix `cabang` pada `routes/web.php`.
  - **Sidebar:** Item menu `Kelola Staf` di `resources/views/layouts/app/sidebar.blade.php` khusus untuk peran Admin Cabang.
  - **Tests:** `tests/Feature/Cabang/StafManagementTest.php` (24 skenario pengujian komprehensif).
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Admin Cabang dapat melihat daftar Kasir dan Kurir di cabangnya sendiri.
  - Admin Cabang tidak dapat melihat staf dari cabang lain, maupun akun Admin Cabang lain dan Super Admin.
  - Admin Cabang dapat menambah akun Kasir dan Kurir baru (tersimpan dengan `cabang_id` admin, `is_active = true`, password ter-hash).
  - Admin Cabang tidak dapat membuat akun dengan role selain Kasir dan Kurir.
  - Admin Cabang dapat mengubah nama, email, dan role staf di cabangnya.
  - Admin Cabang tidak dapat melihat, mengubah, menonaktifkan, atau mereset sandi staf cabang lain (403 Forbidden).
  - Admin Cabang dapat menonaktifkan dan mengaktifkan kembali akun staf cabangnya tanpa menghapus data.
  - Admin Cabang dapat mereset kata sandi staf cabangnya.
  - Otorisasi server-side: Kasir dan Super Admin ditolak 403; Kurir dialihkan ke `/akses-ditolak`; Admin Cabang tanpa cabang dialihkan ke `/login`.
  - Karakter khusus / XSS pada nama staf di-escape dengan aman.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 153/153 lulus (586 assertions).
  - `npm run build` berhasil.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi tampilan visual manual di browser belum dilakukan.
- **Catatan untuk PIC story berikutnya (Sprint 2 / US-09–US-12):** Akun Kasir dan Kurir kini sudah dapat dibuat oleh masing-masing Admin Cabang untuk keperluan pengujian transaksi kasir dan penugasan kurir.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** lihat laporan handoff

## Keputusan backlog Sprint 1
Butir lama sudah diselesaikan lewat J-01 final dan PRD v1.2:
1. Scope Sprint 1 adalah US-01 sampai US-07 (24 SP).
2. US-02 mencakup Google; login nomor HP adalah US-08 (Sprint 2).
3. Data cabang: 2 cabang pilot. Koordinat di `CabangSeeder` masih perkiraan dan perlu diverifikasi PO (PRD §16 terbuka no. 4).
4. Metrik dashboard: pendapatan dan jumlah transaksi per cabang dan per kanal; aturan hitung di PRD §6.
5. Skema role: `super_admin`, `admin_cabang`, `kasir`, `kurir` (+ `pelanggan` di kode; lihat catatan rekonsiliasi).
6. Story Sprint 1 yang belum selesai dibawa ke Sprint 2 (kesepakatan Sprint 2 no. 2).
