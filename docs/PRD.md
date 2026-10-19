# Product Requirements Document (PRD) — SIGALON

**Versi:** 1.0-draft  
**Tanggal acuan:** 19 Oktober 2026  
**Status:** Draft kerja; bukan pengganti validasi Product Owner dan mitra  
**Proyek formal:** Digitalisasi Proses Transaksi dan Distribusi Air Galon pada CV. Aufa  
**Mata kuliah:** Proyek Utama (ISY3215), D3 Manajemen Informatika, Politeknik Negeri Padang  
**Kelas:** MI3B · **Kelompok:** 5  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa  
**Mitra:** CV. Aufa · **Pilot:** 2 cabang · **Target arsitektur:** 13 cabang

> **Aturan dokumen:** Ini adalah dokumentasi kerja yang merangkum kesepakatan yang tersedia. Jangan menganggap hal berlabel *Terbuka* sudah diputuskan. Dokumen yang pernah dibuat sebelumnya tidak otomatis berubah karena PRD ini dibuat; selaraskan secara manual setelah disetujui tim.

## 1. Ringkasan produk

SIGALON adalah sistem digital untuk mendukung pencatatan transaksi, pemesanan, distribusi air galon isi ulang, penjualan galon kosong baru, dan pelaporan operasional CV. Aufa. Sistem menggantikan pencatatan utama di buku besar dengan alur digital untuk transaksi walk-in, COD/antar-jemput, dan canvassing.

SIGALON terdiri dari website publik dan website admin, serta aplikasi Android untuk Kasir dan Kurir. Pelanggan menggunakan website responsif, bukan aplikasi pelanggan tersendiri.

## 2. Latar belakang dan masalah

CV. Aufa memiliki 13 cabang. Proses berjalan saat ini masih banyak mengandalkan buku besar, telepon/WhatsApp, dan ingatan kurir. Masalah utama:
- Pencatatan manual rawan salah dan tidak menghasilkan laporan otomatis.
- Pesanan telepon/WhatsApp dapat terlewat ketika staf sibuk.
- Penugasan kurir dan pelacakan status belum terstruktur.
- Pelanggan belum memiliki website pemesanan dan riwayat status.
- Pencatatan pengeluaran depot belum terintegrasi dengan laporan.
- Data perlu dibatasi per cabang, tetapi Super Admin membutuhkan gambaran gabungan.

Peminjaman galon **dihapus dari ruang lingkup terbaru**, sehingga tidak boleh dikembangkan kecuali diputuskan kembali secara eksplisit.

## 3. Tujuan dan indikator keberhasilan

### Tujuan
1. Mendigitalisasi pencatatan transaksi tiga kanal.
2. Menyediakan pemesanan online melalui website.
3. Menyediakan antarmuka mobile Android untuk Kasir dan Kurir.
4. Menghasilkan laporan penjualan dan pengeluaran per cabang serta gabungan.
5. Memudahkan pemantauan pesanan dan penugasan kurir.
6. Mendukung pengelolaan banyak cabang dari satu sistem.

### Indikator yang perlu diukur saat UAT
- Transaksi yang diuji tersimpan satu kali dan nilai totalnya benar.
- Pengguna tidak dapat mengakses data cabang lain tanpa izin.
- Status pesanan mengikuti transisi yang ditetapkan.
- Ringkasan dashboard cocok dengan data transaksi uji.
- Pengguna mitra dapat menyelesaikan tugas utama tanpa bantuan berulang.

Target angka kuantitatif (misalnya waktu respons) **belum ditetapkan**.

## 4. Pengguna dan hak akses

| Peran | Platform | Hak/kebutuhan utama |
|---|---|---|
| Super Admin | Website | Mengelola Admin Cabang dan data cabang; melihat dashboard/laporan gabungan; mengatur harga galon kosong; mengelola konfigurasi QR bila fitur QR digunakan |
| Admin Cabang | Website | Mengelola Kasir/Kurir di cabangnya; melihat data cabang; mengelola pesanan dan penugasan kurir; mencatat pengeluaran depot |
| Kasir | Android | Mencatat transaksi walk-in dan pesanan COD; memverifikasi setoran canvassing |
| Kurir/Petugas Lapangan | Android | Melihat tugas miliknya; memperbarui status COD; mencatat penjualan canvassing berdasarkan jumlah galon |
| Pelanggan | Website responsif | Login, memesan, melihat riwayat/status, menggunakan tautan WhatsApp untuk konfirmasi |

Data Admin Cabang, Kasir, dan Kurir dibatasi pada cabangnya. Super Admin memiliki akses lintas cabang sesuai kebutuhan tugasnya.

## 5. Ruang lingkup

### Termasuk
- Landing page publik dan peta cabang dengan Leaflet.js/OpenStreetMap.
- Login staf berbasis email/password dan role.
- Login pelanggan via Google serta opsi nomor HP + password.
- Transaksi walk-in.
- COD/antar-jemput yang dibuat manual oleh staf maupun melalui pemesanan online.
- Penugasan kurir dan pembaruan status.
- Pencatatan canvassing dan verifikasi setoran.
- Penjualan galon kosong baru melalui walk-in saja.
- Harga galon kosong yang dapat dikonfigurasi Super Admin.
- Pengeluaran depot yang dicatat Admin Cabang.
- Dashboard dan laporan transaksi/pengeluaran.
- Notifikasi internal di sistem dan tautan `wa.me` untuk membuka WhatsApp dengan pesan terisi.
- API Laravel untuk aplikasi Android menggunakan Sanctum.
- Pilot pada 2 cabang; rancangan mendukung 13 cabang.

### Tidak termasuk
- Aplikasi pelanggan terpisah.
- Manajemen stok.
- Payment gateway aktif.
- Utang atau pembayaran tempo.
- Paket langganan, diskon, repeat order, dan rating.
- Peminjaman/pengembalian galon (dihapus dari scope terbaru).
- Pengiriman pesan WhatsApp otomatis melalui WhatsApp Business API pada tahap awal.
- Hosting dan domain produksi (ditunda).

## 6. Kanal dan aturan bisnis

### Harga
- Air isi ulang walk-in: **Rp6.000/galon**.
- Air isi ulang COD/antar-jemput: **Rp7.000/galon**.
- Canvassing: **Rp7.000/galon**.
- Galon kosong baru: harga dikonfigurasi Super Admin; nilai harga belum ditetapkan dalam informasi yang tersedia; hanya dijual melalui walk-in.

### Transaksi
- Walk-in tidak membutuhkan data pelanggan.
- COD/online membutuhkan nama, nomor HP, alamat, jumlah galon, cabang, dan metode pembayaran.
- Canvassing cukup mencatat jumlah galon; tidak wajib menyimpan data pelanggan.
- Total transaksi dihitung otomatis dan nilai harga satuan saat transaksi dibuat harus disimpan agar histori tidak berubah ketika konfigurasi harga berubah.
- Metode pembayaran yang disepakati: tunai dan QR statis. Tidak ada payment gateway aktif.
- Untuk COD, pembayaran dianggap diterima ketika kurir menyelesaikan pengantaran dan menerima uang.
- Kasir mencatat transaksi walk-in dan COD.
- Kurir mencatat penjualan canvassing; Kasir memverifikasi setoran sebelum pencatatan dianggap final untuk pelaporan. Hindari menghitung satu penjualan dua kali.
- Pengeluaran hanya dicatat Admin Cabang.
- Tidak ada perhitungan stok.

### Status pesanan COD
Urutan utama: `diproses → dijemput → diantar → selesai`. Status hanya boleh maju sesuai alur. Aturan untuk pembatalan/gagal antar belum ditetapkan dan perlu keputusan sebelum implementasi status tersebut.

### WhatsApp
- Pada tahap awal gunakan tautan `wa.me` dengan teks pesan yang telah diisi.
- Tautan tidak membuktikan pesan terkirim; pelanggan harus menekan Kirim.
- Notifikasi internal dashboard dan konfirmasi di website tetap diperlukan.
- Notifikasi WhatsApp otomatis berbayar/berintegrasi pihak ketiga tidak termasuk tahap awal.

### Login pelanggan
- Opsi: Google atau nomor HP + password.
- Untuk login nomor HP + password, jangan mengklaim nomor sudah terverifikasi tanpa mekanisme verifikasi.
- Sediakan pembatasan percobaan login, hash password yang aman, validasi input, dan mekanisme pemulihan akun yang diputuskan tim.
- Pengaitan akun Google dengan akun nomor HP harus mencegah pengambilalihan akun dan duplikasi riwayat.

## 7. Kebutuhan fungsional

### A. Autentikasi dan akun
- Staf login/logout dengan email/password dan role.
- Tolak akses jika role tidak berhak.
- Pelanggan login dengan Google dan nomor HP + password.
- Super Admin mengelola akun Admin Cabang.
- Admin Cabang mengelola akun Kasir/Kurir pada cabangnya.

### B. Landing page
- Menampilkan informasi layanan dan harga yang telah disepakati.
- Menampilkan lokasi cabang, nama, alamat, dan marker.
- Menggunakan Leaflet dan tile OpenStreetMap dengan atribusi wajib.
- Data cabang menyimpan latitude/longitude.

### C. Walk-in
- Kasir mencatat jumlah air isi ulang dan metode pembayaran.
- Sistem menghitung jumlah × Rp6.000.
- Galon kosong baru dapat ditambahkan pada kanal walk-in dengan harga konfigurasi Super Admin.
- Keputusan apakah air isi ulang dan galon kosong dapat berada pada satu transaksi gabungan **masih perlu dikonfirmasi**. Sampai diputuskan, jangan mengasumsikan transaksi gabungan.

### D. COD manual dan online
- Admin/Kasir dapat memasukkan pesanan dari telepon/WhatsApp.
- Pelanggan dapat memilih cabang, jumlah, alamat, dan metode pembayaran pada website.
- Pesanan masuk ke antrean cabang yang dipilih.
- Admin menugaskan Kurir dari cabang yang sama.
- Pelanggan melihat riwayat dan status pesanannya.
- Bila memilih QR, halaman menampilkan QR statis yang sesuai konfigurasi.

### E. Pengiriman
- Kurir hanya melihat tugas yang ditugaskan kepadanya.
- Kurir memperbarui status sesuai transisi yang diizinkan.
- Kurir menandai selesai setelah pengantaran dan penerimaan pembayaran COD.

### F. Canvassing
- Kurir mencatat jumlah galon yang dijual.
- Total dihitung dengan harga Rp7.000 per galon.
- Kurir menyetorkan hasil ke Kasir.
- Kasir memverifikasi jumlah dan nominal setoran; sistem menghindari duplikasi transaksi.

### G. Pengeluaran
- Admin Cabang mencatat kategori, jumlah, tanggal, keterangan, dan pengguna penginput.
- Admin dapat melihat, mengubah, menghapus, dan memfilter pengeluaran per bulan.
- Pengeluaran terikat pada cabang pengguna.

### H. Dashboard dan laporan
- Dashboard cabang menampilkan transaksi/pendapatan hari ini dan pengeluaran bulan ini.
- Dashboard Super Admin menampilkan agregat lintas cabang dan filter cabang bila diperlukan.
- Laporan harian, mingguan, bulanan; per kanal; pendapatan, pengeluaran, dan selisih.
- Definisi “pendapatan” harus menggunakan transaksi dengan pembayaran diterima, bukan hanya pesanan dibuat.
- Transaksi dapat difilter berdasarkan kanal dan tanggal.

### I. Android
- Satu aplikasi Flutter dengan tampilan menyesuaikan role.
- Kasir: walk-in dan COD.
- Kurir: daftar tugas, update status, input canvassing.
- Gunakan Laravel REST API dan Sanctum; token disimpan secara aman dan dapat dicabut.

## 8. Kebutuhan non-fungsional

- **Usability:** sederhana, mobile-friendly, form singkat, pesan error jelas.
- **Keamanan:** hash password, otorisasi server-side, validasi, rate limiting login, perlindungan token, pembatasan akses cabang.
- **Keandalan:** transaksi tidak hilang/terduplikasi; gunakan validasi dan transaksi database saat operasi terkait.
- **Kinerja:** target numerik belum ditetapkan; tetapkan dan uji sebelum UAT.
- **Skalabilitas:** konfigurasi cabang, bukan duplikasi aplikasi per cabang.
- **Maintainability:** struktur kode, env example, migrasi, seeder, dokumentasi, dan pengujian.
- **Aksesibilitas:** kontras yang cukup, label input, status tidak hanya dibedakan lewat warna.
- **Privasi:** kumpulkan data pelanggan seminimal mungkin dan batasi akses sesuai peran.

## 9. Arsitektur dan teknologi

- Backend dan website: Laravel + Livewire + Tailwind CSS.
- Database: MySQL.
- Android: Flutter.
- API mobile: REST API dengan Laravel Sanctum.
- Login Google: Laravel Socialite.
- Peta: Leaflet.js + OpenStreetMap.
- WhatsApp: tautan `wa.me`, bukan pengiriman otomatis.
- Website dan mobile menggunakan satu backend/database; web tidak perlu API terpisah untuk kebutuhan internal Livewire.
- Hosting/domain produksi ditunda; jangan menambahkan layanan berbayar tanpa persetujuan.

## 10. UX dan design system

Arah visual: **Aqua Professional** — minimalis, bersih, profesional, dengan navy sebagai struktur dan teal sebagai aksen.

- Navy `#123B5D`, teal `#0EA5A4`, ice blue `#E8F6F7`, latar `#F7F9FC`.
- Font yang disarankan: Inter atau Plus Jakarta Sans.
- Admin: sidebar, ringkasan, tabel, filter, aksi cepat.
- Kasir: form satu kolom, input jumlah jelas, total otomatis, tombol simpan besar.
- Kurir: kartu tugas, alamat/status terlihat, tombol aksi besar.
- Pelanggan: landing page ringkas, pemesanan sederhana, riwayat dan timeline status.
- Gunakan responsive states, loading, empty states, error states, konfirmasi aksi destruktif, dan toast feedback.
- Hindari animasi berat dan dekorasi yang menghambat operasional.

## 11. Scrum, sprint, dan tim

- Tim 5 orang: Reyhan Dwi Saputra (Scrum Master), Fikri Adiyofa (Product Owner), Velisa, Shalwa, Nurul.
- Sprint berlangsung 2 minggu.
- Sprint 1: 12–25 Oktober 2026.
- Sprint 2: 26 Oktober–8 November 2026.
- Sprint 3: 9–22 November 2026.
- Sprint 4: 23 November–6 Desember 2026.
- Scope pengembangan dapat disesuaikan melalui Product Owner dan Sprint Planning; jangan menyisipkan story tanpa memperhatikan kapasitas.
- Sprint 1 perlu direkonsiliasi dengan form J-01 lama yang menyebut periode 5–11 Oktober dan user story US-01 sampai US-07. PRD ini tidak otomatis mengubah J-01.

## 12. Definisi Ready (DoR)

User story boleh masuk sprint jika:
1. Tujuan dan aktor jelas.
2. Acceptance criteria dapat diuji.
3. Aturan bisnis dan kasus gagal utama diketahui.
4. Dependensi telah diidentifikasi.
5. Story cukup kecil untuk diselesaikan dalam sprint.
6. Estimasi disepakati tim.
7. UI/API/data yang dibutuhkan cukup jelas.

## 13. Definition of Done (DoD)

User story baru boleh berstatus `DONE` jika:
1. Acceptance criteria terpenuhi.
2. Kode terintegrasi pada branch yang disepakati dan tidak hanya berada di komputer pribadi.
3. Pengujian relevan dijalankan dan hasilnya dicatat.
4. Tidak ada bug blocker/critical yang diketahui.
5. Validasi dan otorisasi server-side sudah diperiksa.
6. UI memiliki loading, empty, success, dan error state yang relevan.
7. Dokumentasi/status story diperbarui.
8. Perubahan diperiksa anggota lain/ reviewer yang ditunjuk.
9. Tidak ada commit otomatis oleh AI; usulan pesan commit disediakan untuk pengguna.

## 14. Git dan kerja lima anggota

- Gunakan satu repository bersama.
- `main` harus stabil; fitur dikembangkan pada branch masing-masing, misalnya `feature/US-01-staff-auth`.
- Hindari dua orang mengedit file yang sama tanpa koordinasi.
- Buat pull request atau proses review setara sebelum menggabungkan fitur.
- Satu story dapat memiliki beberapa commit manual; jangan memaksa satu commit per story.
- AI dilarang menjalankan `git commit`, `git push`, merge, atau mengubah branch tanpa instruksi eksplisit.
- Setelah menyelesaikan tugas, AI melaporkan file berubah, test yang dijalankan, hasil, hal belum selesai, dan menyarankan pesan commit saja.
- Status story diperbarui oleh penanggung jawab setelah bukti selesai diverifikasi.
- Jangan menandai `DONE` hanya karena implementasi ditulis; harus ada bukti tes/review.

## 15. Daftar keputusan terbuka

1. Apakah air isi ulang dan galon kosong dapat digabung dalam satu transaksi walk-in?
2. Berapa harga awal galon kosong baru?
3. Apakah metode QR statis dibutuhkan sejak Sprint 1 atau sprint berikutnya?
4. Apa aturan pemulihan akun pelanggan yang login nomor HP + password?
5. Bagaimana akun Google dan nomor HP yang sama akan ditautkan dengan aman?
6. Apa penanganan pesanan batal/gagal antar dan pembayaran QR yang belum diverifikasi?
7. Bagaimana format dan waktu notifikasi internal untuk Admin dan pelanggan?
8. Target performa numerik dan perangkat Android minimum belum ditetapkan.
9. Hosting/domain dan jadwal pengujian/UAT/implementasi perlu direncanakan kemudian.
10. Sprint 1 backlog dan J-01 perlu direkonsiliasi dengan tanggal sprint 2 minggu dan keputusan terbaru.

## 16. Prioritas saat ini

Fokus hanya pada Sprint 1. Jangan mulai mengimplementasikan story Sprint 2–4 kecuali dibutuhkan sebagai dependensi yang disetujui. Sebelum coding tiap story, baca `CLAUDE.md`, `docs/sprints/sprint-1.md`, dan `docs/USER_STORIES_STATUS.md`; setelah coding, jalankan tes yang relevan dan laporkan saran commit tanpa melakukan commit.
