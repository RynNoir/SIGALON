# Sprint 2 — SIGALON

**Sumber:** Form J-01 Sprint Planning dan Sprint Backlog, Sprint ke-2  
**Periode sprint:** 26 Oktober – 8 November 2026 (mencakup UTS 2–8 November)  
**Tanggal Sprint Planning:** 26 Oktober 2026 · **Anggota hadir:** belum diisi  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa

## A. Sprint Goal
Menggantikan buku besar untuk transaksi harian — walk-in, galon baru, COD manual, dan pemesanan online — sehingga pesanan telepon/WhatsApp tidak lagi terlewat dan setiap pesanan memiliki kurir serta status yang jelas di 2 cabang pilot.

## B. Sprint Backlog

| Kode US | Judul user story | Acceptance criteria (ringkas) | Prioritas | SP | PIC utama |
|---|---|---|---|---:|---|
| US-08 | Registrasi & login Pelanggan dengan nomor HP dan password | Daftar dengan nama, nomor HP (unik), dan password (di-hash); login tanpa OTP dengan rate limiting; nomor yang sudah terdaftar ditolak dengan pesan "Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot" | Must | 3 | Nurul |
| US-09 | Input transaksi walk-in & galon baru oleh Kasir | Kasir memilih item (isi ulang/galon baru), jumlah, dan metode bayar (tunai/QR); tanpa data pelanggan; total dihitung dari file konfigurasi (isi ulang Rp6.000, galon baru nilai sementara); tersimpan sebagai kanal walk_in berstatus selesai dengan dicatat_oleh | Must | 3 | Fikri |
| US-10 | Input pesanan COD manual oleh Kasir | Form nama pemesan, no HP, alamat antar, jumlah galon, metode bayar; pelanggan_id boleh kosong; total Rp7.000 × jumlah; status awal "diproses" | Must | 3 | Shalwa |
| US-11 | Pelanggan memesan air galon secara online | Pelanggan login (Google atau nomor HP), memilih cabang, mengisi jumlah & alamat, memilih bayar (tunai/QR); akun Google wajib melengkapi no HP & alamat pada pesanan pertama; pesanan masuk ke daftar Kasir & Admin Cabang berstatus "diproses" | Must | 5 | Velisa |
| US-12 | Kasir/Admin Cabang menugaskan kurir, membatalkan pesanan, dan sistem mencatat riwayat status | Memilih kurir aktif di cabangnya; status hanya maju berurutan (diproses, dijemput, diantar, selesai) dan diperiksa di backend; pembatalan hanya saat "diproses" disertai alasan; setiap perubahan tersimpan di tabel RIWAYAT_STATUS | Must | 5 | Reyhan |
| US-14 | Pembayaran QR statis pada pemesanan online | Gambar QR tampil bila pelanggan memilih metode QR; Super Admin dapat mengunggah/mengganti gambar QR; pembayaran QR tidak dianggap terverifikasi otomatis | Should | 2 | Fikri |
| US-15 | Daftar transaksi cabang dengan filter | Kasir & Admin Cabang melihat transaksi cabangnya, dapat difilter per kanal, status, dan tanggal, dengan pagination | Should | 2 | Velisa |

**Total story point terpilih:** 23 · **Kapasitas/velocity acuan:** 24 SP (sementara, dari rencana Sprint 1; ganti dengan velocity aktual setelah Sprint 1 selesai) · **Selisih:** +1 SP

## C. Breakdown task

| No | Kode US | Uraian task | Estimasi (jam) | PIC | Status awal |
|---:|---|---|---:|---|---|
| 1 | US-08 | Buat form registrasi pelanggan (nama, no HP unik, password) + validasi | 3 | Nurul | To Do |
| 2 | US-08 | Login nomor HP + password dengan rate limiting percobaan login | 3 | Reyhan | To Do |
| 3 | US-08 | Validasi nomor terdaftar & pesan penolakan (juga untuk akun Google) | 2 | Nurul | To Do |
| 4 | US-09 | Buat form transaksi walk-in (pilih item isi ulang/galon baru, jumlah, metode bayar) | 3 | Velisa | To Do |
| 5 | US-09 | Buat file konfigurasi harga + logika hitung total & simpan kanal walk_in (jenis_item, dicatat_oleh) | 3 | Fikri | To Do |
| 6 | US-10 | Buat form input COD manual (nama pemesan, no HP, alamat antar, jumlah, metode bayar) | 3 | Shalwa | To Do |
| 7 | US-10 | Simpan pesanan COD manual berstatus "diproses" dengan harga dari file konfigurasi | 2 | Reyhan | To Do |
| 8 | US-11 | Desain UI halaman pemesanan pelanggan (pilih cabang, jumlah, alamat, metode bayar) | 3 | Nurul | To Do |
| 9 | US-11 | Implementasi form pemesanan Livewire + validasi; lengkapi no HP & alamat akun Google | 4 | Velisa | To Do |
| 10 | US-11 | Simpan pesanan online ke tabel TRANSAKSI (pelanggan_id, cabang_id) | 3 | Fikri | To Do |
| 11 | US-11 | Tampilkan pesanan baru di daftar pesanan Kasir & Admin Cabang | 3 | Shalwa | To Do |
| 12 | US-12 | Migration tabel RIWAYAT_STATUS + service transisi status (cek status sebelumnya) | 4 | Reyhan | To Do |
| 13 | US-12 | Halaman daftar pesanan COD + pilihan kurir aktif di cabang | 3 | Shalwa | To Do |
| 14 | US-12 | Tombol batalkan pesanan (hanya status diproses) + isian alasan | 2 | Nurul | To Do |
| 15 | US-12 | Feature test transisi status, pembatalan, dan isolasi cabang | 3 | Fikri | To Do |
| 16 | US-14 | Tampilkan gambar QR statis di halaman pemesanan bila metode QR | 2 | Fikri | To Do |
| 17 | US-14 | Fitur Super Admin unggah/ganti gambar QR | 2 | Reyhan | To Do |
| 18 | US-15 | Halaman daftar transaksi cabang dengan filter kanal, status & tanggal | 3 | Velisa | To Do |
| 19 | US-15 | Query transaksi per cabang & pagination | 2 | Fikri | To Do |

**Total estimasi:** 53 jam

## D. Kesepakatan tambahan sprint ini

| No | Hal yang disepakati | PIC |
|---:|---|---|
| 1 | Daily Scrum setiap pagi pukul 08.00 via WhatsApp Group | Seluruh tim |
| 2 | Sprint 2 bergantung pada hasil Sprint 1 (migration tabel, login Google, akun kasir/kurir); item Sprint 1 yang belum selesai dikerjakan lebih dulu | Reyhan |
| 3 | Sprint ini mencakup UTS (2–8 November); bila progres tertinggal, US-14 dan US-15 dipindah ke Sprint 3 | Reyhan |
| 4 | Input transaksi dilakukan Kasir lewat website; aplikasi Android kasir/kurir dikerjakan di Sprint 4 | Seluruh tim |
| 5 | Harga galon baru memakai nilai sementara di file konfigurasi sampai dikonfirmasi mitra | Fikri |
| 6 | Setiap user story diuji (unit/feature test) sebelum dinyatakan Done | Seluruh tim |
| 7 | Scope data tetap 2 cabang pilot | Seluruh tim |

## Dependensi dari Sprint 1
- US-01 (Kasir boleh login website) dibutuhkan US-09, US-10, US-12, dan US-15. Sudah diperbaiki di branch `fix/sprint-1-penyesuaian-prd-v1.2` dan harus di-merge ke `main` sebelum story tersebut dimulai.
- US-06/US-07 (akun Kasir dan Kurir) dibutuhkan untuk data uji US-09–US-12.
- Tabel TRANSAKSI sesuai ERD dibuat di US-04 dan harus di-merge ke `main` sebelum US-09 dimulai.

## Referensi desain per story
Baca `docs/design/README.md` lebih dulu. PRD v1.2 §11 menetapkan palet baru, jadi warna di gambar referensi tidak dipakai.

| Story | Layar referensi (`docs/design/references/`) |
|---|---|
| US-08 | `02-pelanggan-masuk-daftar.jpg` |
| US-09 | `06-admin-transaksi-kasir.jpg` |
| US-10, US-12 | `07-admin-pesanan-pengantaran.jpg` |
| US-11, US-14 | `03-pelanggan-pemesanan.jpg` |
| US-15 | Pola tabel dari `07-admin-pesanan-pengantaran.jpg` |

## Catatan rekonsiliasi J-01 dengan PRD, ERD, dan kode (25 Oktober 2026)
Bagian ini bukan isi J-01.
- **US-08 sebagian besar sudah dibuat di US-02.** Registrasi, login nomor HP, rate limiting, dan pesan penolakan nomor terdaftar sesuai teks J-01 sudah ada di `CustomerAuthController` (tabel `pelanggan`). Sisa pekerjaan: penolakan nomor terdaftar untuk akun Google saat melengkapi nomor HP (task 3).
- **TRANSAKSI sudah sesuai ERD (dikerjakan di US-04).** Model `App\Models\Transaksi`, enum `Kanal`, `JenisItem` (`isi_ulang`/`galon_baru`), `MetodePembayaran`, dan `StatusTransaksi` siap dipakai US-09–US-12. Scope `sah()` dan `antaraTanggal()` tersedia untuk perhitungan.
- **US-11:** kolom `pelanggan.alamat` dan `pelanggan.no_hp` sudah ada dan kosong untuk akun Google, siap dilengkapi saat pesanan pertama.
- **US-14 tanpa tabel pengaturan.** PRD §8 melarang tabel PENGATURAN, jadi lokasi penyimpanan gambar QR (misalnya satu file tetap di storage) perlu disepakati.
- **Task 5 (file konfigurasi harga) sebagian sudah ada.** `config/sigalon.php` sudah berisi harga walk-in, COD, dan galon baru sesuai PRD §6; yang tersisa adalah logika hitung total dan penyimpanan transaksi.

## Catatan untuk PIC story berikutnya
Diperbarui 25 Oktober 2026 dari hasil Sprint 1 (US-01–US-04).

**Umum**
- **Akun uji lokal:** `php artisan db:seed` membuat `superadmin@sigalon.test`, `admin.cabang@sigalon.test`, `kasir@sigalon.test`, dan `kurir@sigalon.test` beserta 2 cabang pilot. Lihat bagian yang sama di `sprint-1.md`.
- **Route pelanggan wajib memakai middleware `auth:pelanggan`, bukan `auth`.** Middleware `EnsureStaffCanAccessWeb` menganggap semua route ber-middleware `auth` sebagai area staf, sehingga pelanggan akan mendapat 403.
- **Ambil pelanggan yang login** lewat `Auth::guard('pelanggan')->user()` (model `App\Models\Pelanggan`: `nama`, `no_hp`, `email`, `google_id`, `alamat`).
- **Harga** hanya dari `config('sigalon.harga.walk_in' | 'cod' | 'galon_baru')`. Jangan menulis angka harga di kode, dan simpan hasil kali ke `total_harga`.

**Model TRANSAKSI (siap dipakai)**
- `App\Models\Transaksi` sesuai ERD: `jenis_kanal` (enum `Kanal`), `jenis_item` (enum `JenisItem`: `isi_ulang`/`galon_baru`), `metode_bayar` (enum `MetodePembayaran`), dan `status` (enum `StatusTransaksi`).
- Relasi yang tersedia: `cabang`, `pelanggan`, `kurir`, `pencatat`, `verifikator`.
- Scope `sah()` hanya mengambil transaksi yang dihitung di laporan; `antaraTanggal($dari, $sampai)` memfilter kolom `tanggal`.
- `TransaksiFactory` punya state `cod($status)` dan `canvassing($status)` untuk tes.

**Per story**
- **US-08:** registrasi dan login nomor HP sudah ada di `CustomerAuthController`, termasuk pesan nomor terdaftar sesuai J-01. Sisa: penolakan nomor terdaftar untuk akun Google saat melengkapi nomor HP.
- **US-09:** walk-in langsung berstatus `selesai`, `tanggal` = hari ini, `dicatat_oleh` = Kasir yang login, `cabang_id` = cabang Kasir. Galon baru hanya untuk walk-in.
- **US-10, US-11:** COD berstatus awal `diproses`. COD manual mengisi `nama_pemesan`, `no_hp_pemesan`, `alamat_antar`; COD online juga mengisi `pelanggan_id` dan mengosongkan `dicatat_oleh` (ERD). Akun Google wajib melengkapi `no_hp` dan `alamat` pada pesanan pertama.
- **US-12:** tabel RIWAYAT_STATUS belum ada. Simpan perubahan status dan riwayat dalam satu transaksi database. Pembatalan hanya dari `diproses` dan wajib alasan. Kurir yang dipilih harus aktif dan berasal dari cabang yang sama.
- **US-10, US-11 (ditambahkan 5 Nov 2026):** setelah menyimpan pesanan COD, panggil `StatusPesananService::catatStatusAwal($pesanan, $pengubah)` dalam satu `DB::transaction` yang sama. `$pengubah` = Kasir yang login (US-10) atau pelanggan pemilik pesanan (US-11). Perhitungan harga US-09 masih ditulis di halaman Livewire-nya dan belum menjadi fungsi bersama.
- **US-11 (ditambahkan 5 Nov 2026):** saat pelanggan akun Google melengkapi nomor HP pada pesanan pertama, gunakan `App\Services\CustomerPhoneService` (`normalize()` dan `ensurePhoneIsUnique($normalized, $pelanggan->id)`) agar validasi dan pesan penolakan duplikasi seragam persis dengan PRD §6.
- **US-12 bagian UI (ditambahkan 5 Nov 2026):** jangan mengubah `status` atau `kurir_id` pesanan langsung. Pakai `ubahStatus()`, `batalkan()`, dan `tugaskanKurir()` dari `App\Services\StatusPesananService`. Otorisasi dan aturan transisi sudah ada di sana; tampilkan pesan dari `ValidationException`. Method mengembalikan model terbaru.
- **US-13, US-15 (ditambahkan 7 Nov 2026):** halaman `cabang.pesanan` hanya memuat pesanan COD cabang pengguna tanpa filter tanggal; filter tanggal dan semua kanal masuk US-15. Pembatalan oleh pelanggan (US-13) cukup memanggil `StatusPesananService::batalkan($pesanan, $pelanggan, $alasan)`.
- **US-14 (ditambahkan 10 Nov 2026):** lokasi penyimpanan gambar QR statis ditetapkan di disk public `storage/app/public/qr/` (`qr-statis.png`, dengan fallback `jpg`/`jpeg`/`webp`) dan dibaca melalui `App\Services\QrCodeService::getQrCodeUrl()`. Fitur unggah Super Admin (Task 17) dapat langsung menyimpan file ke direktori ini. Pastikan symlink `php artisan storage:link` sudah dibuat.
- **US-15:** batasi query dengan `cabang_id` milik pengguna. Super Admin tidak termasuk peran story ini kecuali PO meminta.

## Definition of Done
Mengikuti PRD v1.2 §14. Tambahan sprint ini: setiap story diuji dengan unit/feature test sebelum Done (kesepakatan no. 6).

## Catatan status per story

### US-09: Input transaksi walk-in & galon baru oleh Kasir
- **Tanggal:** 5 November 2026
- **Story/Task:** US-09 (Task 4 & Task 5)
- **Dikerjakan oleh:** Fikri (PIC) & Velisa
- **Perubahan:**
  - Menambahkan halaman Livewire `resources/views/pages/kasir/⚡transaksi.blade.php` untuk pencatatan transaksi walk-in & galon baru sesuai panduan visual `06-admin-transaksi-kasir.jpg` dan token warna PRD v1.2 §11 / `docs/DESIGN_SYSTEM.md`.
  - Menghitung total harga transaksi murni di sisi server dari `config('sigalon.harga.walk_in')` dan `config('sigalon.harga.galon_baru')`, serta mengabaikan input total dan cabang dari browser.
  - Mengisi field otomatis server: `jenis_kanal = Kanal::WalkIn`, `status = StatusTransaksi::Selesai`, `tanggal = CarbonImmutable::today()`, `dicatat_oleh = auth()->id()`, `cabang_id = auth()->user()->cabang_id`, dan tanpa data pelanggan (`pelanggan_id = null`).
  - Menerapkan validasi ketat: `jenis_item` (enum `JenisItem`), `metode_bayar` (enum `MetodePembayaran`), dan `jumlah_galon` (integer minimal 1, maksimal 500).
  - Otorisasi berlapis yang diulang di setiap request Livewire (`mount` dan `hydrate`): hanya Kasir dengan cabang aktif yang diizinkan; Super Admin, Admin Cabang, Kurir, Pelanggan, dan tamu ditolak; Kasir tanpa cabang atau cabang nonaktif diberikan pesan aman tanpa error 500.
  - Mencegah double-submit dengan `wire:loading.attr="disabled"` pada tombol simpan, menampilkan konfirmasi total transaksi, dan mengosongkan/mereset form setelah tersimpan.
  - Menambahkan route `kasir.transaksi` pada blok `// [US-09]` di `routes/web.php` dan satu item menu `Transaksi Walk-in` khusus Kasir di `resources/views/layouts/app/sidebar.blade.php`.
  - Membuat 18 unit/feature test komprehensif di `tests/Feature/Kasir/TransaksiWalkInTest.php`.
- **Acceptance criteria yang lulus:**
  - Kasir memilih item (isi ulang / galon baru), jumlah, dan metode bayar (tunai / QR) [LULUS].
  - Tanpa data pelanggan (`pelanggan_id = null`) [LULUS].
  - Total dihitung di server dari file konfigurasi (`walk_in` Rp6.000, `galon_baru` Rp45.000 nilai sementara) [LULUS].
  - Tersimpan sebagai kanal `walk_in` berstatus `selesai` dengan `dicatat_oleh` kasir login dan `cabang_id` kasir [LULUS].
- **Tes dan hasil:**
  - `php artisan test`: 195/195 tests lulus (745 assertions).
  - `tests/Feature/Kasir/TransaksiWalkInTest.php`: 18/18 tests lulus (98 assertions).
  - `vendor\bin\phpstan analyse --memory-limit=1G`: 0 error (passed).
  - `vendor\bin\pint --test`: passed.
  - `npm run build`: lulus dalam 5.14s.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser kasir secara nyata.
  - Keputusan terbuka PO: Batas atas jumlah galon per transaksi walk-in (diusulkan 500 galon).
  - Keputusan terbuka PO: Apakah Admin Cabang atau Super Admin diizinkan melakukan input transaksi walk-in langsung (saat ini ditolak 403).
  - Keputusan terbuka PO: Nilai pasti harga galon kosong baru dari mitra (saat ini nilai sementara Rp45.000 di `config/sigalon.php`).
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):**
  `feat(US-09): implement walk-in and new gallon transaction input for cashier`

### US-12 — Riwayat status dan aturan pesanan COD (bagian backend)
- **Tanggal:** 5 November 2026
- **Story/Task:** US-12 task 12, branch `feature/US-12-penugasan-status-pesanan`. Task 13–14 (UI) belum dikerjakan.
- **Dikerjakan oleh:** Reyhan (PIC) dengan bantuan AI (Claude Code)
- **Keputusan yang dipakai (disetujui Reyhan, 5 Nov 2026):**
  - Kasir dan Admin Cabang di cabangnya serta Kurir yang ditugaskan boleh memajukan status. Tanpa ini COD tidak bisa `selesai` sampai Android tersedia di Sprint 4. **Perlu konfirmasi PO.**
  - Kurir wajib ditugaskan sebelum `dijemput` dan hanya boleh diganti selama `diproses`.
  - Super Admin tidak mengelola pesanan.
  - `riwayat_status` hanya memiliki `created_at`, mengikuti ERD. PRD §8 menyebut semua tabel memakai `created_at` dan `updated_at`, jadi kalimat itu perlu diselaraskan.
  - Folder baru `app/Services` disetujui.
- **Asumsi tambahan:** alasan pembatalan maksimal 500 karakter, karena PRD tidak menetapkan batas.
- **Perubahan:**
  - Migration `create_riwayat_status_table`, model `RiwayatStatus`, `RiwayatStatusFactory`, dan relasi `Transaksi::riwayatStatus()`.
  - `StatusTransaksi::bolehBerubahKe()` berisi transisi COD. Transisi canvassing ditambahkan di US-16.
  - `App\Services\StatusPesananService`: `catatStatusAwal()`, `ubahStatus()`, `batalkan()`, `tugaskanKurir()`.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Status hanya maju berurutan; lompat, mundur, dan perubahan dari status akhir ditolak.
  - Pembatalan hanya dari `diproses` dan wajib disertai alasan.
  - Kurir yang dipilih harus aktif, berperan Kurir, dan dari cabang yang sama.
  - Setiap perubahan status tersimpan di `riwayat_status` dalam satu transaksi database dengan perubahan statusnya.
  - Staf cabang lain, Super Admin, dan pelanggan yang bukan pemilik pesanan ditolak.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 254/254 (970 assertions).
  - `tests/Feature/Transaksi/StatusPesananServiceTest.php`: 59/59 (225 assertions).
  - `npm run build` berhasil.
- **Yang belum selesai / blocker:**
  - Task 13–14: halaman daftar pesanan, pilihan kurir, dan tombol batal. Task 15 (feature test lewat halaman) mengikuti UI.
  - Migration dan kunci baris belum diuji di MariaDB karena server tidak menyala saat pengujian. SQLite di tes mengabaikan kunci baris.
  - Reviewer belum ditunjuk (DoD no. 8).
- **Reviewer:** belum ada
- **Status yang diusulkan:** IN_PROGRESS
- **Saran pesan commit (tidak dijalankan):** `feat(US-12): add status history and COD order status service`

### US-08 — Registrasi & login Pelanggan dengan nomor HP dan password
- **Tanggal:** 5 November 2026
- **Story/Task:** US-08 task 1–3, branch `feature/US-08-registrasi-login-pelanggan`
- **Dikerjakan oleh:** Nurul (PIC) dengan bantuan AI (Antigravity)
- **Perubahan:**
  - `App\Services\CustomerPhoneService`: normalisasi nomor telepon Indonesia (+62, 08, 8) dan validasi keunikan dengan pesan resmi PRD `CustomerPhoneService::DUPLICATE_PHONE_MESSAGE`.
  - `CustomerAuthController`: integrasi `CustomerPhoneService`, penambahan method `showCompletePhoneForm()` dan `updatePhone()`, serta pembatasan rate limiting 5 percobaan gagal per IP + nomor HP.
  - View `pages.auth.customer-complete-phone`: form Aqua Professional untuk melengkapi nomor WhatsApp bagi pelanggan yang masuk via Google atau belum memiliki nomor HP, lengkap dengan pencegahan double submit.
  - Komponen `customer-navbar`: penambahan pill/tautan peringatan "Lengkapi No. HP" pada desktop dan mobile drawer jika pelanggan yang login belum memiliki nomor HP.
  - Route `routes/web.php`: penambahan route `customer.phone.edit` dan `customer.phone.update` dalam grup `auth:pelanggan`.
  - Middleware redirection di `bootstrap/app.php`: tamu yang mengakses `/pelanggan/*` otomatis diarahkan ke `route('customer.login')` (`/masuk`).
  - Unit/Feature test komprehensif `tests/Feature/Auth/CustomerRegistrationLoginTest.php` (14 tes, 63 assertions).
- **Acceptance criteria yang lulus:**
  - Daftar dengan nama, nomor HP unik, dan password di-hash Bcrypt [LULUS].
  - Login tanpa OTP dengan rate limiting (maksimal 5 percobaan salah per menit) [LULUS].
  - Nomor yang sudah terdaftar ditolak dengan pesan: "Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot." (baik saat registrasi maupun saat akun Google melengkapi nomor HP) [LULUS].
  - Otorisasi terpisah antara guard `pelanggan` dan staf `web` [LULUS].
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 268/268 tests lulus (1.033 assertions).
  - `tests/Feature/Auth/CustomerRegistrationLoginTest.php`: 14/14 tests lulus (63 assertions).
  - `npm run build`: Berhasil tanpa error (3.60s).
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser Chrome oleh pengguna.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):**
  `feat(US-08): implement customer registration, login with rate limiting, and google phone completion`

### US-10 — Input pesanan COD manual oleh Kasir
- **Tanggal:** 5 November 2026
- **Story/Task:** US-10 task 6 & 7, branch `feature/US-10-input-pesanan-cod-manual-oleh-kasir`
- **Dikerjakan oleh:** Shalwa (PIC) dengan bantuan AI (Antigravity)
- **Perubahan:**
  - Menambahkan halaman Livewire Volt `resources/views/pages/kasir/⚡pesanan-cod.blade.php` untuk formulir input pesanan COD manual (nama pemesan, nomor HP, alamat antar, stepper kuantitas galon, pilihan metode bayar tunai / QR statis).
  - Menghitung total harga transaksi murni di sisi server dari `config('sigalon.harga.cod')` (Rp7.000 × kuantitas galon) dan mengabaikan nilai total/cabang manipulasi dari browser.
  - Menyimpan transaksi ke tabel `transaksi` dengan field otomatis server: `jenis_kanal = Kanal::Cod`, `jenis_item = JenisItem::IsiUlang`, `status = StatusTransaksi::Diproses`, `pelanggan_id = null` (tanpa akun pelanggan), `kurir_id = null`, `dicatat_oleh = auth()->id()`, `cabang_id = auth()->user()->cabang_id`, dan `tanggal = CarbonImmutable::today()`.
  - Mengintegrasikan pencatatan status awal ke tabel `riwayat_status` melalui `StatusPesananService::catatStatusAwal()` di dalam satu `DB::transaction` atomik.
  - Menerapkan validasi: nama pemesan (2-100 karakter), no HP (9-20 digit angka), alamat antar (5-500 karakter), jumlah galon (1-500), dan metode bayar (enum `MetodePembayaran`).
  - Otorisasi berlapis di setiap request Livewire (`mount` dan `hydrate`): hanya Kasir aktif dengan cabang aktif yang diizinkan; Super Admin, Admin Cabang, Kurir, Pelanggan, dan tamu ditolak.
  - Mencegah double-submit dengan `wire:loading.attr="disabled"` pada tombol simpan, menampilkan kartu konfirmasi pesanan dengan ID pesanan, dan mereset formulir.
  - Menambahkan route `kasir.pesanan-cod` pada blok `// [US-10]` di `routes/web.php` dan item menu `Pesanan COD` khusus Kasir di `resources/views/layouts/app/sidebar.blade.php`.
  - Menulis 20 unit/feature test komprehensif di `tests/Feature/Kasir/PesananCodManualTest.php`.
- **Acceptance criteria yang lulus:**
  - Form nama pemesan, no HP, alamat antar, jumlah galon, metode bayar tersedia lengkap [LULUS].
  - `pelanggan_id` dibiarkan kosong/null untuk COD manual [LULUS].
  - Total dihitung otomatis dari konfigurasi `config('sigalon.harga.cod')` (Rp7.000 × jumlah) [LULUS].
  - Status awal pesanan tersimpan sebagai `diproses` dan tercatat di tabel `riwayat_status` [LULUS].
  - Otorisasi hanya Kasir aktif di cabangnya [LULUS].
- **Tes dan hasil:**
  - `php artisan test`: 288/288 tests lulus (1.121 assertions).
  - `tests/Feature/Kasir/PesananCodManualTest.php`: 20/20 tests lulus (88 assertions).
  - `vendor/bin/pint --dirty`: passed.
  - `composer types:check` (PHPStan): passed (0 error).
  - `npm run build`: passed (8.74s).
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser kasir.
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):**
  `feat(US-10): implement manual COD order input for cashier`

### US-11 — Pelanggan memesan air galon secara online
- **Tanggal:** 6 November 2026
- **Story/Task:** US-11 task 8–11, branch `feature/US-11-pemesanan-online-pelanggan`
- **Dikerjakan oleh:** Velisa (PIC) dengan bantuan AI (Antigravity)
- **Perubahan:**
  - Membuat halaman Livewire Volt `resources/views/pages/pelanggan/⚡pesan.blade.php` dengan layout khusus `layouts/pelanggan.blade.php` (tanpa sidebar staf, navbar ringkas pelanggan).
  - Formulir pemesanan mencakup: pilih cabang aktif, stepper jumlah galon (1–20), alamat pengantaran (pre-fill dari profil), catatan opsional, pilih metode bayar (tunai/QR statis).
  - Khusus akun Google (`no_hp = null`): form wajib mengisi nomor HP yang dinormalisasi dan divalidasi keunikannya via `CustomerPhoneService::normalize()` dan `ensurePhoneIsUnique()`. Nomor dan alamat disimpan ke profil di dalam `DB::transaction` yang sama.
  - Menyimpan transaksi ke tabel `transaksi` dengan field otomatis: `jenis_kanal = Kanal::Cod`, `jenis_item = JenisItem::IsiUlang` (online hanya isi ulang, PRD §5), `status = StatusTransaksi::Diproses`, `pelanggan_id = $pelanggan->id`, `dicatat_oleh = null`, `kurir_id = null`, total dari `config('sigalon.harga.cod')`.
  - Mencatat status awal via `StatusPesananService::catatStatusAwal($transaksi, $pelanggan)` dalam satu `DB::transaction` atomik.
  - Pengaman double-submit: `wire:loading.attr="disabled"` di klien + `Cache::lock('order-pelanggan-{id}', 10)` di server (mengembalikan `ValidationException` pada lock yang gagal diperoleh).
  - Menambahkan route `pelanggan.pesan` di blok `// [US-11]` dalam grup `auth:pelanggan` di `routes/web.php`.
  - Memperbarui tombol CTA "Pesan Air Galon" di `resources/views/welcome.blade.php` dari WhatsApp URL ke `route('pelanggan.pesan')` (ikon shopping cart, tanpa `target="_blank"`, `data-test="hero-cta-pesan"`).
  - Menulis 19 feature test di `tests/Feature/Pelanggan/PemesananOnlineTest.php`.
- **Acceptance criteria yang lulus:**
  - Pelanggan login (Google/nomor HP) dapat mengakses halaman pemesanan [LULUS].
  - Tamu dan staf (guard `web`) diarahkan / ditolak [LULUS].
  - Akun Google wajib melengkapi no HP; no HP duplikat ditolak dengan pesan PRD [LULUS].
  - Cabang nonaktif ditolak di validasi [LULUS].
  - Jumlah galon: minimal 1, maksimal 20 [LULUS].
  - Total dihitung di server dari `config('sigalon.harga.cod')`, nilai browser diabaikan [LULUS].
  - Riwayat status awal `diproses` tersimpan di `riwayat_status` dengan `diubah_oleh_pelanggan = $pelanggan->id` [LULUS].
  - Pengaman double-submit via `Cache::lock` mencegah pesanan dobel [LULUS].
  - Input berbahaya diescape saat ditampilkan (`{{ }}`) [LULUS].
  - `jenis_item` selalu `IsiUlang`, tidak ada `GalonBaru` di pesanan online [LULUS].
  - Alamat tersimpan ke profil pelanggan setelah pesanan pertama [LULUS].
  - Form pre-fill dari profil pelanggan [LULUS].
- **Tes dan hasil:**
  - `php artisan test`: 307/307 tests lulus (1.174 assertions).
  - `tests/Feature/Pelanggan/PemesananOnlineTest.php`: 19/19 tests lulus (53 assertions).
  - `vendor/bin/pint --dirty`: passed.
  - `vendor/bin/phpstan analyse --memory-limit=1G`: 0 error.
  - `npm run build`: passed.
- **File yang diubah (scope US-11):**
  - `resources/views/pages/pelanggan/⚡pesan.blade.php` (baru)
  - `resources/views/layouts/pelanggan.blade.php` (baru)
  - `tests/Feature/Pelanggan/PemesananOnlineTest.php` (baru)
  - `routes/web.php` (hanya blok `// [US-11]`)
  - `resources/views/welcome.blade.php` (hanya baris tombol CTA hero)
  - `docs/USER_STORIES_STATUS.md` (hanya baris US-11)
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser belum dilakukan.
- **Pertanyaan terbuka untuk PO:**
  1. Apakah batas maksimal 20 galon per pesanan online tepat untuk pengantaran motor? (Implementasi saat ini: 20)
  2. Apakah alamat yang diisi pelanggan saat pesanan pertama otomatis disimpan ke profil (`pelanggan.alamat`) untuk pesanan berikutnya? (Implementasi saat ini: **ya**, selalu diperbarui jika berubah)
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):**
  `feat(US-11): implement online gallon order form for pelanggan`

### US-12 — Daftar pesanan, penugasan kurir, dan pembatalan (bagian UI)
- **Tanggal:** 7 November 2026
- **Story/Task:** US-12 task 13–15, branch `feature/US-12-ui-daftar-pesanan`. Task 12 (backend) sudah di `main` lewat PR #13.
- **Dikerjakan oleh:** Reyhan (PIC) dengan bantuan AI (Claude Code). Di J-01, task 13–15 tercatat milik Shalwa, Nurul, dan Fikri.
- **Keputusan yang dipakai (disetujui Reyhan, 7 Nov 2026):**
  - Tab: Aktif (bawaan), Diproses, Dijemput, Diantar, Selesai, Dibatalkan; 10 pesanan per halaman.
  - Pesanan yang masih berjalan urut dari terlama; selesai dan batal urut dari terbaru.
  - Tanpa pembaruan otomatis; notifikasi pesanan baru menyusul di US-30.
  - Tombol "Tambah Pesanan Manual" hanya untuk Kasir, karena form US-10 menolak Admin Cabang.
- **Perubahan:**
  - Halaman Livewire `resources/views/pages/cabang/⚡pesanan.blade.php`, route `cabang.pesanan` (`/cabang/pesanan`), dan menu "Daftar Pesanan" untuk Kasir dan Admin Cabang.
  - `StatusTransaksi::statusBerikutnyaCod()` menjadi satu sumber urutan status; `bolehBerubahKe()` memakainya.
  - Halaman ini juga memenuhi AC US-11 "pesanan masuk ke daftar Kasir & Admin Cabang" (task 11).
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Memilih kurir aktif di cabangnya; kurir nonaktif dan kurir cabang lain ditolak.
  - Status hanya maju berurutan; hanya tombol langkah berikutnya yang tampil; permintaan melompat ditolak.
  - Pembatalan hanya saat `diproses` dan wajib alasan; alasan tampil di tab Dibatalkan.
  - Setiap perubahan tersimpan di `riwayat_status`.
  - Isolasi cabang: pesanan cabang lain tidak tampil dan aksinya mengembalikan 404. Super Admin dan pelanggan 403; Kurir ke akses ditolak; tamu ke login.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 362/362 (1.387 assertions).
  - `tests/Feature/Cabang/PesananTest.php`: 53 tes.
  - `npm run build` berhasil.
- **Yang belum selesai / blocker:**
  - Belum diuji di MariaDB (migration `riwayat_status`, query jumlah per tab, kunci baris) karena server tidak menyala saat pengujian.
  - Tampilan belum diperiksa di browser (desktop, ponsel, dan modal).
  - Konfirmasi PO: Kasir dan Admin Cabang boleh memajukan status sampai Android tersedia.
  - Reviewer belum ditunjuk (DoD no. 8).
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-12): add order list with courier assignment, status updates, and cancellation`

### US-15 — Daftar transaksi cabang dengan filter
- **Tanggal:** 9 November 2026
- **Story/Task:** US-15 task 18 & 19, branch `feature/US-15-daftar-transaksi-cabang`
- **Dikerjakan oleh:** Velisa (PIC) dengan bantuan AI (Antigravity)
- **Keputusan yang dipakai:**
  - Halaman baru mandiri di `resources/views/pages/cabang/⚡transaksi.blade.php`, route `cabang.transaksi` (`/cabang/transaksi`), tanpa menyenggol atau memperluas halaman pesanan COD milik US-12.
  - Memuat SEMUA kanal transaksi cabang pengguna (`walk_in`, `cod`, `canvassing`), termasuk transaksi berstatus dibatalkan atau ditolak (tanpa scope `sah()`).
  - Filter kanal memvalidasi nilai enum `Kanal`; filter status memvalidasi nilai enum `StatusTransaksi`; nilai ilegal diabaikan secara aman.
  - Filter rentang tanggal memakai kolom `tanggal` dengan scope `antaraTanggal()`; jika tanggal awal lebih besar dari tanggal akhir, sistem menampilkan error validasi ramah tanpa crash 500 atau query rusak.
  - Pagination 15 per halaman di sisi server via Livewire `WithPagination`; parameter filter dipertahankan saat navigasi halaman dan perubahan filter otomatis mereset halaman ke 1.
  - Isolasi ketat cabang di server-side: query transaksi mengunci `where('cabang_id', auth()->user()->cabang_id)` murni dari akun staf yang login; manipulasi parameter/payload Livewire tidak pernah membocorkan data cabang lain.
  - Bebas N+1 query: eager loading relasi `pencatat:id,name`, `pelanggan:id,nama,no_hp`, dan `kurir:id,name`.
  - Otorisasi server-side diulang pada setiap siklus request Livewire (`mount` dan `hydrate`): hanya Kasir dan Admin Cabang aktif yang diizinkan; Super Admin, Kurir, Pelanggan, dan tamu ditolak; Kasir atau Admin Cabang dengan cabang null atau nonaktif mendapatkan pesan peringatan aman.
  - Satu item menu "Transaksi" ditambahkan di `resources/views/layouts/app/sidebar.blade.php` khusus untuk Kasir dan Admin Cabang.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Kasir dan Admin Cabang melihat daftar transaksi cabangnya [LULUS].
  - Filter kanal, status, dan rentang tanggal berfungsi individual maupun kombinasi [LULUS].
  - Transaksi yang dibatalkan tetap tampil dengan badge status yang jelas [LULUS].
  - Tombol reset filter mengembalikan daftar ke kondisi default [LULUS].
  - Pagination 15 per halaman berjalan dan filter dipertahankan saat ganti halaman [LULUS].
  - Isolasi cabang ketat: Admin Cabang/Kasir Cabang A tidak pernah melihat transaksi Cabang B [LULUS].
  - Super Admin (403), Kurir (redirect akses ditolak), Pelanggan (403), dan tamu (redirect login) ditolak [LULUS].
  - Staf tanpa cabang atau cabang nonaktif mendapatkan alert aman [LULUS].
  - Bebas N+1 query (jumlah query tetap konstan terlepas dari jumlah baris) [LULUS].
  - Menu "Transaksi" di sidebar tampil untuk Kasir dan Admin Cabang, tidak untuk peran lain [LULUS].
  - Escaping XSS pada data nama pemesan terverifikasi aman [LULUS].
- **Tes dan hasil:**
  - `php artisan test`: 384/384 tests passed (1.450 assertions).
  - `tests/Feature/Cabang/DaftarTransaksiTest.php`: 22/22 tests passed (63 assertions).
  - `vendor\bin\phpstan analyse --memory-limit=1G`: 0 error.
  - `vendor\bin\pint --dirty`: passed.
  - `npm run build`: passed.
- **Yang belum selesai / blocker:**
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser staf.
- **Pertanyaan terbuka untuk PO:**
  - Apakah Super Admin nantinya diizinkan melihat daftar transaksi per cabang atau lintas cabang pada halaman ini? (Saat ini Super Admin ditolak 403 sesuai AC US-15).
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-15): add branch transaction list with filter and pagination`

### US-14 — Pembayaran QR statis pada pemesanan online
- **Tanggal:** 10 November 2026
- **Story/Task:** US-14 Task 16 (Tampilkan gambar QR statis di halaman pemesanan bila metode QR), branch `feature/US-14-qr-statis`. Task 17 (fitur unggah/ganti gambar QR oleh Super Admin) dikerjakan di branch terpisah.
- **Dikerjakan oleh:** Fikri (PIC) dengan bantuan AI (Antigravity)
- **Keputusan yang dipakai:**
  - File gambar QR statis disimpan pada storage disk `public` di path `qr/qr-statis.png` (dengan fallback ekstensi `.jpg`, `.jpeg`, `.webp`), diakses melalui service `App\Services\QrCodeService`.
  - Halaman pemesanan online pelanggan `resources/views/pages/pelanggan/⚡pesan.blade.php` menampilkan gambar QR statis secara reaktif ketika metode QR dipilih, dan menyembunyikannya ketika metode tunai dipilih.
  - Jika file gambar QR statis belum tersedia di storage, sistem menampilkan empty state ramah bernada netral: "QR pembayaran belum tersedia. Silakan pilih Tunai atau hubungi depot." tanpa pesan error atau broken image, dan tidak memblokir pelanggan melakukan pemesanan.
  - Sesuai PRD §6 & §110, sistem menampilkan teks edukasi bahwa pembayaran QR tidak diverifikasi otomatis oleh sistem dan pelanggan diminta menyimpan bukti transfer untuk diperlihatkan kepada kurir saat tiba.
  - Kartu konfirmasi pesanan berhasil (`$pesananBerhasil`) menampilkan gambar kode QR statis serta instruksi penyimpanan bukti bayar jika pesanan dibuat dengan metode QR.
  - Pesanan dengan metode QR tersimpan dengan `metode_bayar = 'qr'`, `status = 'diproses'`, `diverifikasi_oleh = null`, dan `dicatat_oleh = null`, membuktikan pembayaran QR tidak dianggap terverifikasi otomatis.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Gambar QR statis tampil bila pelanggan memilih metode QR [LULUS].
  - Area gambar QR statis tersembunyi bila pelanggan memilih metode Tunai [LULUS].
  - Empty state ramah dan netral muncul bila file gambar QR belum diunggah [LULUS].
  - Klausul pembayaran QR tidak diverifikasi otomatis tampil jelas di UI [LULUS].
  - Nominal tagihan pada area QR dinamis mengikuti stepper jumlah galon [LULUS].
  - Kartu konfirmasi berhasil menampilkan QR statis bila metode pesanan adalah QR [LULUS].
  - Database mencatat pesanan QR dengan status awal diproses dan diverifikasi_oleh kosong [LULUS].
- **Tes dan hasil:**
  - `php -d memory_limit=512M vendor/phpunit/phpunit/phpunit`: 394/394 tests passed (1.486 assertions).
  - `tests/Feature/Pelanggan/QrStatisPemesananTest.php`: 10/10 tests passed (36 assertions).
  - `tests/Feature/Pelanggan/PemesananOnlineTest.php`: 19/19 tests passed (53 assertions).
  - `vendor/bin/phpstan analyse --memory-limit=1G`: 0 error.
  - `vendor/bin/pint --dirty`: passed.
  - `npm run build`: passed (2.07s).
- **Yang belum selesai / blocker:**
  - Task 17: Fitur Super Admin unggah/ganti gambar QR (dikerjakan oleh PIC Reyhan di branch terpisah).
  - Reviewer belum ditunjuk (DoD no. 8).
  - Verifikasi visual di browser pelanggan.
- **Pertanyaan terbuka untuk PO:**
  - Apakah sistem perlu memvalidasi/memblokir pemilihan metode pembayaran QR jika Super Admin belum mengunggah file gambar QR ke storage? (Saat ini sistem tetap mengizinkan pemesanan dengan empty state netral).
- **Reviewer:** belum ada
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-14): display static QR code for online orders with non-automatic verification notice`

### Penyelesaian Sprint 2 — US-14 task 17 dan perbaikan setelah analisis
- **Tanggal:** 10 November 2026
- **Story/Task:** US-14 task 17, perbaikan US-11 dan US-05, branch `fix/sprint-2-penyelesaian`
- **Dikerjakan oleh:** Reyhan (PIC task 17) dengan bantuan AI (Claude Code)
- **Perubahan:**
  - **US-14 task 17:** halaman Livewire `resources/views/pages/admin/⚡qr-statis.blade.php`, route `admin.qr-statis` (`/admin/qr-statis`), dan menu "Gambar QR" khusus Super Admin. Validasi PNG/JPG/WEBP maksimal 2 MB (SVG ditolak). `QrCodeService::replaceQrCode()` menyimpan file baru lalu menghapus file QR lama berekstensi lain; `getQrCodeUrl()` kini menambahkan `?v=` agar browser tidak menampilkan gambar lama.
  - **US-11:** kolom catatan dihapus dari form pemesanan karena isinya tidak pernah tersimpan (ERD TRANSAKSI tidak punya kolom catatan; keputusan Reyhan 10 Nov 2026). Contoh alamat kini menyebut patokan lokasi. Kartu konfirmasi tidak lagi memakai `{{ e(...) }}` yang membuat `&` dan tanda kutip tampil sebagai `&amp;`/`&#039;`.
  - **US-05:** data contoh yang ditulis langsung di `⚡dashboard-cabang.blade.php` dihapus: tabel pesanan, setoran canvassing, kasir cepat, pengeluaran, tombol tanpa fungsi, rincian Tunai/QRIS yang dihitung 64%/36% dari pendapatan, dan angka antrean/verifikasi palsu. Kartu antrean kini menghitung pesanan COD berjalan sungguhan (menunggu kurir, sedang diantar) dengan tautan ke Daftar Pesanan untuk Admin Cabang. Perbaikan `65442a3` di branch US-05 tidak diperlukan lagi.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - US-14: Super Admin dapat mengunggah dan mengganti gambar QR; peran lain ditolak; file tidak sah ditolak tanpa menyimpan apa pun; ukuran tepat 2 MB diterima.
  - US-11: alamat dan nama cabang di kartu konfirmasi ter-escape satu kali; form tidak lagi meminta catatan.
  - US-05: antrean hanya menghitung pesanan COD berjalan milik cabang sendiri; data contoh tidak tampil.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 414/414 (1.550 assertions).
  - Tes dasbor, QR, pelanggan, dan cabang juga lulus di MariaDB 10.4 (183/183, database sementara).
  - `npm run build` berhasil.
- **Yang belum selesai / blocker:**
  - Cek visual di browser (halaman QR, dasbor cabang, form pesanan).
  - Reviewer belum ditunjuk (DoD no. 8).
- **Langkah setelah merge:** `php artisan storage:link` (sekali per komputer), `npm run build`.
- **Reviewer:** belum ada
- **Status yang diusulkan:** US-14 REVIEW (task 16 dan 17 selesai)
- **Saran pesan commit (tidak dijalankan):** lihat laporan handoff

### US-14 — Revisi: QR statis per cabang (keputusan PO)
- **Tanggal:** 10 November 2026
- **Story/Task:** US-14 revisi task 16–17, branch `feature/US-14-qr-per-cabang`. Dicatat sebagai progres Sprint 2 atas permintaan Reyhan.
- **Dikerjakan oleh:** Reyhan dengan bantuan AI (Claude Code)
- **Keputusan yang dipakai:**
  - PO (10 Nov 2026): setiap cabang memakai QR statis sendiri, karena akun penerima QRIS bisa berbeda per cabang.
  - Tidak ada QR bersama sebagai cadangan. Cabang tanpa QR menampilkan pesan netral agar uang tidak masuk ke akun cabang lain.
  - Unggah tetap hanya oleh Super Admin (PRD §4). PRD §4, §7.D, dan §16 sudah diperbarui (disetujui Reyhan).
- **Perubahan:**
  - `QrCodeService`: semua method menerima `Cabang`; file disimpan di disk `public` sebagai `qr/cabang-{id}.{png|jpg|jpeg|webp}`; mengganti QR satu cabang hanya menghapus file lama cabang itu.
  - Halaman `admin.qr-statis`: pilih cabang, unggah, dan kartu status QR setiap cabang (termasuk cabang nonaktif).
  - Form pesanan pelanggan: QR mengikuti cabang yang dipilih; pilihan cabang kini `wire:model.live` agar QR langsung berganti; belum memilih cabang menampilkan ajakan memilih; kartu konfirmasi memakai QR cabang pesanan.
  - Tes milik US-14 (`QrStatisPemesananTest.php`) disesuaikan dengan aturan baru.
- **Acceptance criteria yang lulus (diuji otomatis):**
  - Super Admin mengunggah/mengganti QR per cabang; cabang lain tidak tersentuh.
  - QR yang tampil sesuai cabang yang dipilih dan berganti saat cabang diganti; cabang tanpa QR tidak memakai QR cabang lain atau QR bersama lama.
  - Kartu konfirmasi menampilkan QR cabang pesanan.
  - Validasi file dan otorisasi tetap sama.
- **Tes dan hasil:** lihat laporan handoff dan baris US-14 di `USER_STORIES_STATUS.md`.
- **Yang belum selesai / blocker:**
  - Cek di browser, terutama QR yang langsung berganti saat cabang diganti (perilaku `wire:model.live` tidak bisa dibedakan oleh tes Livewire).
  - Super Admin perlu mengunggah ulang QR untuk setiap cabang; file `qr/qr-statis.*` lama tidak dipakai lagi.
  - Reviewer belum ditunjuk (DoD no. 8).
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `feat(US-14): store static QR per branch and show the selected branch QR`

### US-08 — Perbaikan validasi nomor HP (temuan J-05)
- **Tanggal:** 10 November 2026
- **Story/Task:** perbaikan US-08, branch `fix/US-08-validasi-nomor-hp`
- **Dikerjakan oleh:** Reyhan dengan bantuan AI (Claude Code); PIC US-08: Nurul
- **Temuan yang diperbaiki (J-05 US-08, TC-07 dan TC-08):**
  - Nomor berisi huruf (`abcdefghij`) diterima dan tersimpan sebagai nomor kosong.
  - Nomor dengan spasi yang digitnya kurang dari 9 (`0812 3456`) diterima.
  - Penyebab: batas `min:9`/`max:20` menghitung karakter sebelum nomor dibersihkan.
- **Perubahan:**
  - `CustomerPhoneService::rules()`: kolom nomor HP hanya boleh berisi angka dan pemisah (spasi, `-`, `+`, titik, kurung), lalu hasil normalisasinya wajib `62` diikuti 8–13 digit, sama dengan form pesanan US-11. Dipakai di registrasi dan pelengkapan nomor akun Google, dan semua galat form tetap tampil bersamaan.
  - Pesan galat baru: "Format nomor HP tidak sah. Gunakan nomor seluler, misalnya 081234567890."
  - Teks petunjuk di halaman "Lengkapi No. HP" disesuaikan.
- **Perubahan perilaku:** batas atas turun dari 20 karakter menjadi 13 digit setelah awalan, mengikuti US-11. Nomor 20 digit yang sebelumnya diterima kini ditolak.
- **Tes dan hasil:**
  - `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 429/429 (1.605 assertions).
  - `tests/Feature/Auth/CustomerRegistrationLoginTest.php`: 24 tes, termasuk 6 nomor tidak sah, 3 nomor sah di batas, dan pelengkapan nomor akun Google.
- **Yang belum selesai / blocker:** reviewer belum ditunjuk (DoD no. 8); cek di browser.
- **Status yang diusulkan:** REVIEW
- **Saran pesan commit (tidak dijalankan):** `fix(US-08): validate customer phone digits after normalization`



