# Sprint 1 — SIGALON

**Periode acuan terbaru:** 12–25 Oktober 2026  
**Goal:** Membangun fondasi SIGALON agar staf dapat mengakses sistem sesuai peran, cabang dapat ditampilkan pada peta, dan transaksi/dashboard dasar mulai beralih dari pencatatan manual ke digital.  
**Status dokumen:** Perlu rekonsiliasi dengan J-01 yang diunggah.

> J-01 yang tersedia mencantumkan periode 5–11 Oktober 2026 dan US-01 sampai US-07 dengan total 22 story point. Jadwal proyek terbaru menyatakan sprint dua minggu mulai 12 Oktober. Jangan menganggap daftar lama sudah otomatis disetujui sebagai backlog final Sprint 1.

## Aturan sprint
- Fokus hanya pada story yang dikonfirmasi masuk Sprint 1 oleh Product Owner.
- Story belum `DONE` sampai acceptance criteria, tes, dan review terpenuhi.
- AI tidak melakukan commit/push/merge. AI hanya memberikan saran pesan commit.
- Semua tugas harus muncul pada papan tugas tim yang disepakati.
- Status harus diperbarui oleh pemilik/reviewer berdasarkan bukti.

## Kandidat user story dari J-01 (belum otomatis dianggap final)

| ID | User story | AC ringkas dari J-01 | Prioritas J-01 | SP | PIC utama pada J-01 | Status awal |
|---|---|---|---|---:|---|---|
| US-01 | Login Super Admin/Admin Cabang ke website | Login email/password sesuai role; role salah ditolak | Must | 3 | Reyhan | TODO — konfirmasi |
| US-02 | Login pelanggan menggunakan akun Google | Login Google; akun baru tersimpan | Must | 2 | Velisa | TODO — perlu diselaraskan dengan opsi nomor HP + password |
| US-03 | Landing page menampilkan lokasi cabang di peta | Peta menampilkan 13 titik; nama/alamat muncul saat marker diklik | Should | 3 | Shalwa | TODO — konfirmasi cakupan data |
| US-04 | Dashboard Super Admin | Ringkasan penjualan/transaksi seluruh cabang | Must | 5 | Reyhan | TODO — perlu sumber data transaksi |
| US-05 | Dashboard Admin Cabang | Hanya data cabang admin yang login | Must | 3 | Velisa | TODO — perlu uji isolasi cabang |
| US-06 | Super Admin mengelola akun Admin Cabang | Tambah/edit/hapus akun | Should | 3 | Shalwa | TODO — periksa kebijakan hapus/nonaktif |
| US-07 | Admin Cabang mengelola akun Kasir & Kurir | Tambah/edit/hapus akun pada cabangnya | Should | 3 | Nurul | TODO — role Kasir dipisah dan perlu konsisten di schema |

**Total pada J-01 yang diunggah:** 22 SP. Ini bukan konfirmasi kapasitas final untuk sprint dua minggu.

## Referensi desain per story
Baca `docs/design/README.md` sebelum mengerjakan UI. Gambar hanya untuk gaya visual. Ikuti bagian "Jangan ambil" dan tabel konflik di sana.

| Story | Layar referensi (`docs/design/references/`) | Catatan |
|---|---|---|
| US-01 | `05-admin-dasbor-cabang.jpg` (kerangka sidebar/topbar), gaya kartu `02-pelanggan-masuk-daftar.jpg` | Tidak ada gambar login staf; pakai kartu sederhana email + password dengan token design system |
| US-02 | `02-pelanggan-masuk-daftar.jpg` | Tambahkan tombol Google; tanpa "Pesan sebagai Tamu" |
| US-03 | `01-pelanggan-landing.jpg` | Tambahkan peta Leaflet; jangan salin data/klaim dari gambar |
| US-04, US-05 | `05-admin-dasbor-cabang.jpg`, stat card `09-admin-laporan.jpg` | Hanya metrik yang datanya ada |
| US-06, US-07 | `10-admin-pengaturan-cabang-staf.jpg` | Tab profil cabang dan tabel staf saja |

## Perubahan/keputusan terbaru yang harus tercermin saat refinement
- Role Kasir terpisah dari Kurir.
- Pelanggan dapat memilih Google atau nomor HP + password (tanpa OTP) — perlu penambahan/penyesuaian AC.
- Peminjaman galon dihapus dari scope.
- Galon kosong baru dijual hanya melalui walk-in; harga dapat diatur Super Admin.
- WhatsApp menggunakan tautan `wa.me`, bukan API otomatis.
- Kasir dapat mencatat walk-in dan COD.
- Pengeluaran hanya dicatat Admin Cabang.
- Canvassing Rp7.000/galon, jumlah saja, diverifikasi Kasir; kemungkinan masuk sprint berikutnya sesuai backlog.
- Hosting/domain ditunda.
- Sistem pilot 2 cabang, rancangan mendukung 13 cabang.

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

## Keputusan sebelum mengunci backlog
1. Konfirmasi daftar US-01 sampai US-07 masih menjadi scope Sprint 1.
2. Tentukan apakah US-02 mencakup login Google saja atau Google + nomor HP/password.
3. Konfirmasi data cabang dan koordinat yang tersedia.
4. Tentukan definisi metrik dashboard jika transaksi belum sepenuhnya dibuat.
5. Pastikan skema role mencakup `super_admin`, `admin_cabang`, `kasir`, `kurir`.
6. Sepakati apakah story tertentu harus ditunda karena ketergantungan data transaksi.
