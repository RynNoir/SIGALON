# Product Requirements Document (PRD) — SIGALON

**Versi:** 1.2 (25 Okt 2026: diselaraskan dengan Dokumentasi SIGALON v1.1 dan J-01 Sprint Planning 1–4)  
**Tanggal acuan:** 25 Oktober 2026  
**Status:** Draft kerja; keputusan berlabel *Disepakati* sudah diputuskan pemilik proyek, yang berlabel *Terbuka* belum  
**Proyek formal:** Digitalisasi Proses Transaksi dan Distribusi Air Galon pada CV. Aufa  
**Mata kuliah:** Proyek Utama (ISY3215), D3 Manajemen Informatika, Politeknik Negeri Padang  
**Kelas:** MI3B · **Kelompok:** 5  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa  
**Mitra:** CV. Aufa · **Narasumber:** Ikhlas Adiyofa (pemilik cabang Dadok Tunggul Hitam) · **Pilot:** 2 cabang · **Target arsitektur:** 13 cabang

> **Aturan dokumen:** PRD ini mengikuti Dokumentasi SIGALON v1.1 (25 Oktober 2026). Bila ada perbedaan, Dokumentasi v1.1 yang berlaku, lalu PRD ini diperbarui. Jangan menganggap hal berlabel *Terbuka* sudah diputuskan.

## 1. Ringkasan produk

SIGALON adalah sistem digital untuk pencatatan transaksi, pemesanan, distribusi air galon isi ulang, penjualan galon kosong baru, pencatatan pengeluaran, dan pelaporan operasional CV. Aufa. Sistem menggantikan buku besar dengan alur digital untuk tiga kanal: walk-in, COD/antar-jemput, dan canvassing.

SIGALON terdiri dari:
- **Website** (Laravel + Livewire): landing page publik, portal pelanggan, dan halaman staf (Super Admin, Admin Cabang, Kasir).
- **Aplikasi Android** (Flutter): untuk Kasir dan Kurir, dalam satu aplikasi dengan menu menyesuaikan role.

Pelanggan memakai website responsif, bukan aplikasi tersendiri.

## 2. Latar belakang dan masalah

CV. Aufa memiliki 13 cabang. Di cabang Dadok Tunggul Hitam terdapat 2 kurir dan 1 karyawan depot yang merangkap kasir. Semua transaksi dicatat manual di buku besar. Masalah utama:
- Pencatatan manual rawan salah, sulit direkap, dan tidak menghasilkan laporan otomatis.
- Pesanan telepon/WhatsApp sering terlewat saat staf sibuk melayani walk-in.
- Galon sering tertukar atau lupa milik siapa karena tidak ada penugasan kurir.
- Pelanggan belum bisa memesan online dan tidak bisa melihat status pesanan.
- Pengeluaran depot belum dicatat terstruktur.
- Data perlu dibatasi per cabang, tetapi Super Admin membutuhkan gambaran gabungan.

Peminjaman galon **di luar ruang lingkup** dan tidak boleh dikembangkan.

## 3. Tujuan dan indikator keberhasilan

### Tujuan
1. Mendigitalisasi pencatatan transaksi tiga kanal.
2. Menyediakan pemesanan online melalui website sebagai pengganti pemesanan lewat WhatsApp.
3. Menyediakan aplikasi Android untuk Kasir dan Kurir.
4. Menghasilkan laporan pendapatan dan pengeluaran per cabang serta gabungan.
5. Mengatasi galon tertukar lewat penugasan kurir, pelacakan status, dan linimasa pesanan.
6. Mendukung pengelolaan banyak cabang dari satu sistem.

### Indikator yang diukur saat UAT (Sprint 4)
- Transaksi uji tersimpan satu kali dan totalnya benar.
- Pengguna tidak dapat mengakses data cabang lain tanpa izin.
- Status pesanan hanya mengikuti transisi yang ditetapkan, dan setiap perubahan tercatat di riwayat status.
- Ringkasan dashboard dan laporan cocok dengan data transaksi uji.
- Pengguna mitra dapat menyelesaikan tugas utama tanpa bantuan berulang.

Target angka kuantitatif (misalnya waktu respons) **belum ditetapkan** (*Terbuka*).

## 4. Pengguna dan hak akses

| Peran | Platform | Hak/kebutuhan utama |
|---|---|---|
| Super Admin | Website | Mengelola data cabang (nama, alamat, koordinat) dan akun Admin Cabang; mengunggah/mengganti gambar QR statis; melihat dashboard dan laporan lintas cabang |
| Admin Cabang | Website | Mengelola akun Kasir/Kurir di cabangnya; menugaskan kurir dan memantau status; membatalkan pesanan; mereset password pelanggan; mencatat pengeluaran; melihat rekap cabang |
| Kasir | Website + Android | Mencatat walk-in dan galon baru; menginput COD manual; menugaskan kurir; membatalkan pesanan; memverifikasi canvassing; mereset password pelanggan; melihat daftar transaksi |
| Kurir/Petugas Lapangan | Android | Melihat tugas miliknya; memperbarui status COD; mengonfirmasi pembayaran (tunai atau bukti QR); mencatat dan memperbaiki canvassing |
| Pelanggan | Website responsif | Mendaftar/login (Google atau nomor HP + password); memesan antar-jemput; membatalkan pesanan yang masih diproses; melihat status, linimasa, dan riwayat; mengubah password; menghubungi depot lewat `wa.me` |

Data Admin Cabang, Kasir, dan Kurir dibatasi pada cabangnya, dan pembatasan ini diperiksa di backend. Super Admin memiliki akses lintas cabang. Akun staf **dinonaktifkan, tidak dihapus**, dan akun nonaktif tidak bisa login di web maupun Android.

## 5. Ruang lingkup

### Termasuk
- Landing page publik: info produk, harga, peta cabang (Leaflet.js + OpenStreetMap dengan atribusi), tombol Daftar/Masuk.
- Login staf berbasis email/password dan role.
- Login pelanggan via Google dan via nomor HP + password (tanpa OTP).
- Reset password pelanggan secara manual oleh Kasir/Admin Cabang.
- Transaksi walk-in dan penjualan galon kosong baru (walk-in saja).
- COD/antar-jemput: dibuat manual oleh Kasir atau online oleh pelanggan.
- Penugasan kurir, pembaruan status, pembatalan pesanan, riwayat status, dan linimasa pelanggan.
- Canvassing dicatat kurir dan diverifikasi kasir.
- Pembayaran tunai dan QR statis.
- Pengeluaran depot yang dicatat Admin Cabang.
- Dashboard, laporan, dan daftar transaksi dengan filter.
- Notifikasi dalam aplikasi dan tautan `wa.me` manual.
- Aplikasi Android untuk Kasir dan Kurir, dengan REST API Laravel + Sanctum.
- Pilot pada 2 cabang; rancangan mendukung 13 cabang.

### Tidak termasuk
- Aplikasi pelanggan terpisah dan aplikasi iOS.
- Manajemen stok dan peminjaman/pengembalian galon.
- Payment gateway, utang, dan pembayaran tempo.
- OTP untuk verifikasi nomor HP.
- Pengubahan harga lewat menu (harga tetap di file konfigurasi).
- Paket langganan, diskon, repeat order, dan rating.
- WhatsApp Business API atau pengiriman pesan otomatis.
- Hosting dan domain produksi (ditunda).
- Perluasan langsung ke 13 cabang sebelum pilot dievaluasi.

## 6. Kanal dan aturan bisnis

### Harga (*Disepakati*)
- Air isi ulang walk-in: **Rp6.000/galon**.
- Air isi ulang COD/antar-jemput: **Rp7.000/galon**.
- Canvassing: **Rp7.000/galon**.
- Galon kosong baru: hanya walk-in; nominal **belum ditetapkan** (*Terbuka*), sementara memakai nilai dummy.
- Semua harga sama di semua cabang dan disimpan di **satu file konfigurasi aplikasi** (bukan tabel, bukan menu). Mengubah harga berarti mengubah file lalu deploy ulang.
- `total_harga` disimpan di setiap transaksi agar histori tidak berubah ketika harga diubah.

### Transaksi
- Satu transaksi berisi satu jenis item (`isi_ulang` atau `galon_baru`), sesuai model data.
- Walk-in dan canvassing tidak menyimpan data pelanggan.
- COD membutuhkan nama pemesan, nomor HP, alamat antar, jumlah galon, cabang, dan metode bayar. COD manual boleh tanpa akun pelanggan (`pelanggan_id` kosong).
- Metode bayar: tunai atau QR statis, untuk semua kanal. Pembayaran QR tidak dianggap terverifikasi otomatis.
- COD: pembayaran dicatat saat status `selesai`. Untuk QR, kurir mengonfirmasi setelah melihat bukti bayar di ponsel pelanggan.
- Canvassing: kurir mencatat jumlah galon dan metode bayar (QR juga dikonfirmasi kurir). Catatan baru sah setelah diterima kasir.
- Pengeluaran hanya dicatat Admin Cabang.
- Laporan hanya menghitung walk-in, COD `selesai`, dan canvassing `tervalidasi`. Pesanan `dibatalkan` dan canvassing `ditolak` tidak dihitung.

### Status (*Disepakati*)
- **COD:** `diproses → dijemput → diantar → selesai`. Status hanya boleh maju sesuai urutan.
- **Pembatalan:** `diproses → dibatalkan`, hanya selama status masih `diproses`, oleh pelanggan, Kasir, atau Admin Cabang, disertai alasan.
- **Canvassing:** `menunggu_verifikasi → tervalidasi` atau `menunggu_verifikasi → ditolak` (wajib alasan). Catatan `ditolak` diperbaiki kurir lalu kembali ke `menunggu_verifikasi`.
- **Walk-in:** langsung `selesai`.
- Setiap perubahan status COD dan canvassing dicatat di tabel `RIWAYAT_STATUS`. Backend menolak transisi yang tidak tercantum di atas.
- Penanganan **gagal antar** (misalnya pelanggan tidak di tempat) belum ditetapkan (*Terbuka*).

### WhatsApp
- Hanya tautan `wa.me` dengan teks terisi, di detail pesanan dan halaman lupa password.
- Tautan tidak membuktikan pesan terkirim. Sistem tidak boleh menampilkan status "terkirim" untuk WhatsApp.

### Login dan akun pelanggan (*Disepakati*)
- **Nomor HP + password:** daftar dengan nama, nomor HP unik, dan password (di-hash). Tanpa OTP; nomor tidak diklaim terverifikasi. Login dibatasi dengan rate limiting.
- **Google (Laravel Socialite):** akun dibuat otomatis (google_id, email). Nomor HP dan alamat dilengkapi saat pesanan pertama.
- Dua jenis akun **tidak digabung otomatis**. Nomor HP yang sudah terdaftar ditolak dengan pesan: "Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot."
- **Lupa password (akun nomor HP):** pelanggan menghubungi depot lewat `wa.me`. Kasir/Admin Cabang memverifikasi identitas dan membuat password sementara, lalu pelanggan wajib menggantinya saat login.

## 7. Kebutuhan fungsional

### A. Autentikasi dan akun
- Staf login/logout dengan email/password; akses dibatasi role; akun nonaktif ditolak.
- Pelanggan login dengan Google atau nomor HP + password (§6).
- Super Admin mengelola data cabang dan akun Admin Cabang (tambah, ubah, nonaktifkan, reset password).
- Admin Cabang mengelola akun Kasir/Kurir pada cabangnya (tambah, ubah, nonaktifkan).
- Kasir/Admin Cabang mereset password pelanggan secara manual; pelanggan dapat mengubah password sendiri.

### B. Landing page
- Info layanan, harga isi ulang dan antar-jemput, tombol Daftar/Masuk.
- Peta cabang dengan marker, nama, dan alamat dari tabel `CABANG` (latitude/longitude).
- Leaflet + tile OpenStreetMap dengan atribusi wajib.

### C. Walk-in dan galon baru
- Kasir (web atau Android) memilih item, jumlah, dan metode bayar.
- Sistem menghitung total dari file konfigurasi harga dan menyimpan `dicatat_oleh`.

### D. COD manual dan online
- Kasir menginput pesanan dari telepon/WhatsApp (web atau Android).
- Pelanggan memilih cabang, jumlah, alamat, dan metode bayar di website.
- Pesanan masuk ke antrean cabang berstatus `diproses` dan memicu notifikasi.
- Kasir/Admin Cabang menugaskan kurir aktif dari cabang yang sama.
- Pelanggan, Kasir, atau Admin Cabang dapat membatalkan selama `diproses`.
- Pelanggan melihat riwayat, status terkini, dan linimasa tahap demi tahap.
- Bila memilih QR, halaman menampilkan gambar QR statis yang diunggah Super Admin.

### E. Pengiriman
- Kurir hanya melihat tugas yang ditugaskan kepadanya.
- Kurir memperbarui status sesuai transisi yang diizinkan.
- Kurir menandai `selesai` setelah mengantar dan mengonfirmasi pembayaran tunai atau bukti QR.

### F. Canvassing
- Kurir mencatat jumlah galon dan metode bayar di Android; total Rp7.000 × jumlah.
- Kasir (web atau Android) menerima atau menolak (dengan alasan) catatan `menunggu_verifikasi`.
- Kurir memperbaiki catatan yang ditolak. Satu penjualan dicatat satu kali.

### G. Pengeluaran
- Admin Cabang mencatat kategori, jumlah, tanggal, keterangan; penginput tersimpan.
- Pengeluaran dapat dilihat, diubah, dihapus, dan difilter per bulan, terikat pada cabang pengguna.

### H. Dashboard, laporan, dan daftar transaksi
- Dashboard cabang dan dashboard Super Admin (agregat lintas cabang, per cabang dan per kanal).
- Laporan harian, mingguan, bulanan: pendapatan per kanal, total pengeluaran, dan selisih (aturan perhitungan di §6).
- Daftar transaksi untuk Kasir dan Admin Cabang dengan filter kanal, status, tanggal, dan pagination.

### I. Notifikasi
- Notifikasi dalam aplikasi memakai tabel notifikasi bawaan Laravel.
- Pemicu: pesanan baru (Kasir/Admin Cabang), penugasan (Kurir), perubahan status dan pembatalan (Pelanggan).

### J. Android
- Satu aplikasi Flutter; menu menyesuaikan role; hanya Kasir dan Kurir yang bisa login.
- **Kasir:** walk-in + galon baru, COD manual, verifikasi canvassing, penugasan kurir.
- **Kurir:** daftar tugas, update status + konfirmasi pembayaran, input dan perbaikan canvassing.
- REST API Laravel + Sanctum; token disimpan aman dan dicabut bila akun dinonaktifkan.

## 8. Model data (*Disepakati*: 6 tabel inti)

| Tabel | Kolom |
|---|---|
| CABANG | id, nama_cabang, alamat, latitude, longitude |
| USER_STAFF | id, cabang_id (kosong untuk Super Admin), nama, email, password, role (super_admin, admin_cabang, kasir, kurir), aktif |
| PELANGGAN | id, nama, no_hp, email, google_id, password, alamat (no_hp dan email unik bila terisi) |
| TRANSAKSI | id, cabang_id, pelanggan_id, kurir_id, jenis_kanal (walk_in, cod, canvassing), jenis_item (isi_ulang, galon_baru), jumlah_galon, total_harga, metode_bayar (tunai, qr), status, tanggal, nama_pemesan, no_hp_pemesan, alamat_antar, dicatat_oleh, diverifikasi_oleh |
| RIWAYAT_STATUS | id, transaksi_id, status_lama, status_baru, diubah_oleh_staff, diubah_oleh_pelanggan, catatan, created_at |
| PENGELUARAN | id, cabang_id, user_id, kategori, jumlah, tanggal, keterangan |

Tidak ada tabel HARGA, PENGATURAN, atau NOTIFIKASI kustom. Tabel bawaan framework (notifikasi, token Sanctum, sesi) tidak digambar di ERD. Semua tabel memakai `created_at` dan `updated_at`.

## 9. Kebutuhan non-fungsional

- **Usability:** sederhana, mobile-friendly, form singkat, pesan error jelas, seluruh antarmuka Bahasa Indonesia.
- **Keamanan:** hash password, otorisasi server-side, validasi input, rate limiting login, perlindungan token, pembatasan akses cabang, HTTPS di produksi, kredensial di environment variables.
- **Keandalan:** transaksi tidak hilang/terduplikasi; gunakan validasi dan transaksi database saat operasi terkait (misalnya perubahan status + riwayat status).
- **Kinerja:** target numerik belum ditetapkan (*Terbuka*); tetapkan dan uji sebelum UAT.
- **Skalabilitas:** konfigurasi cabang, bukan duplikasi aplikasi per cabang.
- **Maintainability:** struktur kode, `.env.example`, migrasi, seeder, dokumentasi API (Postman), dan pengujian.
- **Aksesibilitas:** kontras cukup, label input, fokus keyboard terlihat, status tidak hanya dibedakan lewat warna.
- **Privasi:** data pelanggan hanya untuk COD dan dibatasi sesuai peran.

## 10. Arsitektur dan teknologi

- Backend dan website: Laravel + Livewire + Tailwind CSS.
- Database: MySQL.
- Android: Flutter (Kasir dan Kurir).
- API mobile: REST API dengan Laravel Sanctum.
- Login Google: Laravel Socialite.
- Peta: Leaflet.js + OpenStreetMap.
- Notifikasi: tabel notifikasi bawaan Laravel.
- WhatsApp: tautan `wa.me`, bukan pengiriman otomatis.
- Harga: satu file konfigurasi aplikasi.
- Website dan mobile memakai satu backend/database; web tidak memerlukan API terpisah untuk kebutuhan internal Livewire.
- Hosting/domain produksi ditunda; jangan menambahkan layanan berbayar tanpa persetujuan.

## 11. UX dan design system

Arah visual: **Aqua Professional** — bersih, modern, profesional, dengan warna utama Light Blue dan Cyan.

| Token | Warna | Penggunaan |
|---|---|---|
| Primary Cyan | `#0891B2` | Tombol utama, tautan, elemen aktif |
| Deep Blue | `#0C4A6E` | Judul dan navigasi penting |
| Bright Cyan Accent | `#22D3EE` | Aksen seperlunya |
| Light Cyan | `#CFFAFE` | Highlight lembut |
| Ice Blue Background | `#F0F9FF` | Latar halaman |
| White Surface | `#FFFFFF` | Kartu dan form |
| Primary Text | `#0F172A` | Teks utama |
| Secondary Text | `#64748B` | Teks penjelas |
| Border | `#E2E8F0` | Garis pembatas |
| Success / Warning / Error / Info | `#16A34A` / `#D97706` / `#DC2626` / `#2563EB` | Status |

- `docs/DESIGN_SYSTEM.md` harus diselaraskan dengan palet di atas (palet lama `#00647C` / `#FAF8FF` tidak dipakai lagi).
- Font: Plus Jakarta Sans (web dan mobile). Mode tampilan: terang saja.
- Referensi visual: `docs/design/README.md` (gambar di `docs/design/references/`) hanya untuk gaya. Fitur, harga, label, dan data di gambar yang tidak ada di PRD tidak diimplementasikan; bila bertentangan, PRD yang berlaku.
- Admin: sidebar, ringkasan, tabel, filter, aksi cepat.
- Kasir (web dan Android): form satu kolom, input jumlah jelas, total otomatis, tombol simpan besar.
- Kurir: kartu tugas, alamat dan status terlihat (teks + badge), tombol aksi besar.
- Pelanggan: landing page ringkas, pemesanan sederhana, riwayat dan linimasa status; portal pelanggan tidak menyerupai dashboard admin.
- Gunakan loading, empty, error state, konfirmasi aksi destruktif (misalnya pembatalan), dan toast feedback.
- Hindari animasi berat dan dekorasi yang menghambat operasional.

## 12. Scrum, sprint, dan tim

- Tim 5 orang: Reyhan Dwi Saputra (Scrum Master), Fikri Adiyofa (Product Owner), Velisa, Shalwa, Nurul.
- Sprint 2 minggu. Total rencana 89 SP. Rincian task ada di form J-01 Sprint Planning 1–4 (sudah direkonsiliasi).

| Sprint | Periode | User story | SP |
|---|---|---|---|
| 1 | 12–25 Okt 2026 | US-01 login staf, US-02 login Google, US-03 landing page + peta, US-04 dashboard Super Admin, US-05 dashboard Admin Cabang, US-06 data cabang + akun Admin Cabang, US-07 akun Kasir & Kurir | 24 |
| 2 | 26 Okt–8 Nov 2026 (mencakup UTS) | US-08 login nomor HP, US-09 walk-in + galon baru, US-10 COD manual, US-11 pesanan online, US-12 penugasan kurir + pembatalan + riwayat status, US-14 QR statis, US-15 daftar transaksi | 23 |
| 3 | 9–22 Nov 2026 | US-13 riwayat + linimasa pelanggan, US-16 verifikasi canvassing, US-19 pengeluaran, US-20 laporan, US-21 reset password + wa.me, US-22 API staf, US-30 notifikasi | 21 |
| 4 | 23 Nov–6 Des 2026 | US-23 login Android, US-24 walk-in Android, US-25 COD Android, US-26 tugas kurir, US-27 status + pembayaran, US-28 canvassing kurir, US-32 verifikasi + penugasan di Android, US-29 uji integrasi & UAT | 21 |

- US-17 dan US-18 (peminjaman galon) dihapus dari backlog.
- Pengujian dilakukan di setiap sprint; uji integrasi dan UAT di Sprint 4. Fase implementasi setelah 6 Desember 2026, jadwal menyusul.
- Scope dapat disesuaikan melalui Product Owner dan Sprint Planning; jangan menyisipkan story tanpa memperhatikan kapasitas.

## 13. Definisi Ready (DoR)

User story boleh masuk sprint jika:
1. Tujuan dan aktor jelas.
2. Acceptance criteria dapat diuji.
3. Aturan bisnis dan kasus gagal utama diketahui.
4. Dependensi telah diidentifikasi.
5. Story cukup kecil untuk diselesaikan dalam sprint.
6. Estimasi disepakati tim.
7. UI/API/data yang dibutuhkan cukup jelas.

## 14. Definition of Done (DoD)

User story baru boleh berstatus `DONE` jika:
1. Acceptance criteria terpenuhi.
2. Kode terintegrasi pada branch yang disepakati dan tidak hanya berada di komputer pribadi.
3. Pengujian relevan (unit/feature test) dijalankan dan hasilnya dicatat.
4. Tidak ada bug blocker/critical yang diketahui.
5. Validasi, otorisasi, dan pembatasan cabang di server sudah diperiksa.
6. UI memiliki loading, empty, success, dan error state yang relevan.
7. Dokumentasi/status story diperbarui.
8. Perubahan diperiksa anggota lain/reviewer yang ditunjuk.
9. Tidak ada commit otomatis oleh AI; usulan pesan commit disediakan untuk pengguna.

## 15. Git dan kerja lima anggota

- Gunakan satu repository bersama.
- `main` harus stabil; fitur dikembangkan pada branch masing-masing, misalnya `feature/US-01-staff-auth`.
- Hindari dua orang mengedit file yang sama tanpa koordinasi.
- Buat pull request atau proses review setara sebelum menggabungkan fitur.
- Satu story dapat memiliki beberapa commit manual; jangan memaksa satu commit per story.
- AI dilarang menjalankan `git commit`, `git push`, merge, rebase, atau reset tanpa instruksi eksplisit. AI boleh membuat dan berpindah ke branch story sesuai konvensi penamaan.
- Setelah menyelesaikan tugas, AI melaporkan file berubah, test yang dijalankan, hasil, hal belum selesai, dan menyarankan pesan commit saja.
- Status story diperbarui oleh penanggung jawab setelah bukti selesai diverifikasi.
- Jangan menandai `DONE` hanya karena implementasi ditulis; harus ada bukti tes/review.

## 16. Daftar keputusan

### Sudah diputuskan sejak v1.1-draft
| Pertanyaan lama | Keputusan |
|---|---|
| Harga galon kosong dikonfigurasi Super Admin? | Tidak. Semua harga tetap di file konfigurasi |
| Isi ulang dan galon kosong dalam satu transaksi? | Tidak. Satu transaksi satu jenis item |
| QR statis mulai kapan? | Sprint 2 (US-14) |
| Pemulihan akun nomor HP | Reset manual oleh Kasir/Admin Cabang lewat `wa.me` |
| Penautan akun Google dan nomor HP | Tidak digabung otomatis; nomor terdaftar ditolak |
| Pesanan batal | Status `dibatalkan`, hanya dari `diproses` |
| Pembayaran QR belum terverifikasi | Kurir mengonfirmasi bukti bayar (COD dan canvassing) |
| Notifikasi internal | Tabel bawaan Laravel; pemicu di §7.I |
| Rekonsiliasi J-01 Sprint 1 | Selesai: periode 12–25 Okt, US-01–US-07 diperbarui |
| Kasir perlu akses website? | Ya, Kasir memakai website dan Android |
| Riwayat status | Tabel `RIWAYAT_STATUS` + linimasa pelanggan |

### Masih terbuka
1. Berapa harga galon kosong baru? (konfirmasi mitra)
2. Bagaimana penanganan gagal antar (pelanggan tidak di tempat, alamat salah)?
3. Apakah pelanggan boleh memesan tanpa akun ("Pesan sebagai Tamu")? Sampai diputuskan: wajib login. (Konflik K3.)
4. Data mitra untuk konten publik: nama, alamat, koordinat 2 cabang pilot, jam operasional, nomor WhatsApp, dan klaim layanan/higienitas yang boleh ditampilkan. (Konflik K8–K9.)
5. Library grafik untuk dashboard/laporan (Flux versi gratis tidak menyediakan grafik).
6. Target performa numerik dan versi Android minimum.
7. Hosting/domain dan jadwal fase implementasi setelah 6 Desember 2026.
8. Kode US baru (US-08, US-21, US-30, US-32) perlu dicocokkan dengan Product Backlog tim.

## 17. Prioritas saat ini

Hari ini (25 Oktober 2026) adalah hari terakhir Sprint 1. Selesaikan sisa story Sprint 1 lebih dulu; story yang belum selesai dibawa ke Sprint 2 sesuai keputusan Sprint Planning 26 Oktober. Jangan mulai story Sprint 3–4 kecuali dibutuhkan sebagai dependensi yang disetujui. Sebelum coding tiap story, baca `CLAUDE.md`, `docs/sprints/sprint-<n>.md`, dan `docs/USER_STORIES_STATUS.md`; setelah coding, jalankan tes yang relevan dan laporkan saran commit tanpa melakukan commit.