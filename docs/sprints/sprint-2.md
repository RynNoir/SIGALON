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
- **US-14:** PRD melarang tabel pengaturan; sepakati lokasi file gambar QR dulu.
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

