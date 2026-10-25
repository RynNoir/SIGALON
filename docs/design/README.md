# Referensi Desain Visual — SIGALON

**Status:** referensi visual, **bukan** spesifikasi fitur.
**Ditambahkan:** 19 Oktober 2026.

## Aturan pakai (wajib dibaca AI agent dan developer)
1. Gambar di `references/` hanya menunjukkan **gaya visual dan pola tata letak**: tipografi, kartu, tabel, badge, dan susunan halaman. **Warna di gambar tidak dipakai lagi**; palet resmi mengikuti PRD v1.2 §11 dan `docs/DESIGN_SYSTEM.md`.
2. **PRD (`docs/PRD.md`) selalu menang.** Fitur, angka, label, harga, atau alur di gambar yang tidak ada di PRD **tidak boleh diimplementasikan**, sekalipun terlihat di gambar.
3. Semua angka, nama orang, alamat, nomor telepon, dan nama cabang di gambar adalah **data contoh fiktif**. Jangan disalin ke seeder, landing page, atau tes sebagai data asli.
4. Jangan menyalin seluruh isi satu layar. Ambil hanya pola yang dibutuhkan story yang sedang dikerjakan. Hasil akhir harus lebih ringkas daripada gambar.
5. Token warna, font, dan aturan komponen ada di `docs/DESIGN_SYSTEM.md`. Gunakan token itu, bukan hex yang dibaca dari gambar.
6. Jika gambar bertentangan dengan PRD dan belum ada keputusan, ikuti PRD dan catat konfliknya di laporan handoff. Lihat tabel "Konflik" di bawah.

## Daftar layar

| File | Platform / peran | Story terkait | Ringkasan |
|---|---|---|---|
| `references/01-pelanggan-landing.jpg` | Website publik | US-03 | Landing page, harga, cara pesan |
| `references/02-pelanggan-masuk-daftar.jpg` | Website pelanggan | US-02 | Masuk/daftar pelanggan |
| `references/03-pelanggan-pemesanan.jpg` | Website pelanggan | Sprint berikutnya (pemesanan online) | Form pesanan COD |
| `references/04-pelanggan-status-riwayat.jpg` | Website pelanggan | Sprint berikutnya (riwayat/status) | Status pesanan dan riwayat |
| `references/05-admin-dasbor-cabang.jpg` | Website admin | US-01 (shell admin), US-04, US-05 | Kerangka admin dan dasbor |
| `references/06-admin-transaksi-kasir.jpg` | Website Kasir (dan acuan Android) | US-09, US-24 | Input transaksi walk-in |
| `references/07-admin-pesanan-pengantaran.jpg` | Website admin | Sprint berikutnya (COD dan penugasan) | Antrean pesanan dan penugasan kurir |
| `references/08-admin-canvassing.jpg` | Lihat konflik K2 | Sprint berikutnya (canvassing) | Verifikasi setoran canvassing |
| `references/09-admin-laporan.jpg` | Website admin | Sprint berikutnya (laporan) | Laporan pendapatan/pengeluaran |
| `references/10-admin-pengaturan-cabang-staf.jpg` | Website admin | US-06, US-07 | Profil cabang dan daftar staf |

Layar manajemen armada/kendaraan dan profil multi-alamat pelanggan dari template asli **sengaja tidak disertakan** karena fiturnya tidak ada di PRD.

## Pola global yang diadopsi
- Latar halaman `surface` (Ice Blue) dengan **kartu putih** berradius besar dan bayangan halus, tanpa border tebal.
- **Eyebrow:** label kecil huruf kapital berwarna `primary` di atas judul halaman atau seksi.
- **Header halaman:** judul, satu kalimat deskripsi di kiri, dan maksimal 1–2 tombol aksi di kanan (aksi utama `primary`, aksi lain `primary-soft`).
- **Kartu ringkasan (stat card):** label kecil, angka besar, keterangan satu baris, dan ikon dalam kotak `primary-soft` di kanan atas.
- **Badge status berbentuk pill** yang selalu berisi teks, tidak hanya warna. Pemetaan nada warna ada di `DESIGN_SYSTEM.md`.
- **Tabel:** header huruf kapital kecil di atas latar `surface-low`, baris longgar, dan aksi di kolom terakhir.
- **Stepper jumlah** (− angka +) dengan tombol pintasan (+1, +2, +5) untuk input jumlah galon.
- **Pilihan bersegmen** seperti Tunai / QR statis berupa dua tombol besar, yang terpilih berwarna `primary`.
- **Ringkasan total** di panel terpisah dengan angka total besar berwarna `primary`, terlihat sebelum tombol simpan.

## Panduan per layar

### 01 — Landing page (US-03)
**Ambil:**
- Navbar: logo, Beranda, Layanan & Harga, Cara Pesan, Bantuan WhatsApp (tautan `wa.me`), Masuk/Daftar, dan tombol CTA "Pesan Air Galon".
- Hero dengan satu CTA utama dan satu CTA sekunder.
- Kartu harga: Walk-in Rp6.000/galon dan COD Rp7.000/galon. Semua harga, termasuk galon kosong baru, diambil dari file konfigurasi `config/sigalon.php` (PRD §6); jangan menulis angka harga langsung di view.
- Seksi "3 langkah pemesanan" dan footer.

**Tambahkan (tidak ada di gambar, tetapi wajib menurut PRD):** seksi **peta cabang Leaflet/OpenStreetMap** dengan marker (nama dan alamat saat diklik) serta atribusi OSM.

**Jangan ambil:**
- Klaim higienitas atau laboratorium (uji lab, SNI, TDS, UV/ozon, "teruji"), kecuali sudah dikonfirmasi mitra. Lihat K8.
- Foto stok sebagai foto depot asli.
- "Radius 5 km" dan "tukar galon kosong".
- Jam operasional, alamat, dan nomor WhatsApp dari gambar. Data ini harus berasal dari mitra (K9).

### 02 — Masuk/Daftar pelanggan (US-02)
**Ambil:** kartu dua kolom (panel info dan form), tab Masuk Akun / Daftar Baru, input nomor HP dengan prefiks +62, input kata sandi dengan tombol tampilkan, opsi "Ingat saya", dan tombol utama lebar penuh.

**Tambahkan:** tombol **"Masuk dengan Google"** (PRD §6, §7.A).

**Jangan ambil:**
- "Pesan sebagai Tamu" (K3).
- Label "WhatsApp Terverifikasi" atau klaim nomor terverifikasi, karena belum ada mekanisme verifikasi (PRD §6).
- "Pantau kurir real-time" dan "Pesan ulang 1-klik".

**Catatan:** login staf (US-01) memakai email + password dengan gaya kartu yang sama, tanpa panel promosi.

### 03 — Pemesanan (sprint berikutnya)
**Ambil:**
- Indikator langkah: Rincian → Alamat → Konfirmasi.
- Stepper jumlah galon.
- Data penerima: nama, nomor HP, cabang, alamat, catatan opsional.
- Metode bayar Tunai / QR statis.
- Panel ringkasan dengan total.

**Jangan ambil:**
- Opsi "Ambil langsung ke depot", karena walk-in bukan pesanan online.
- **Galon baru di pesanan online**, karena galon kosong hanya dijual lewat walk-in (PRD §5).
- Produk tutup galon/tisu, pilihan slot waktu antar, "Gratis ongkir", estimasi 20–30 menit, dan "konfirmasi instan otomatis".

### 04 — Status & riwayat pesanan (sprint berikutnya)
**Ambil:** kartu pesanan aktif dengan timeline 4 langkah, nama kurir, rincian pembayaran, dan daftar riwayat dengan badge status.

**Wajib:** label timeline mengikuti status PRD, yaitu **Diproses → Dijemput → Diantar → Selesai**, bukan label gambar seperti "Filtrasi & Ozon" (K5).

**Jangan ambil:** ETA/estimasi tiba, rating kurir, tombol Pesan Ulang, unduh nota, dan pembaruan otomatis tiap 30 detik (kecuali diputuskan).

### 05 — Kerangka admin dan dasbor (US-01, US-04, US-05)
**Ambil:**
- **Sidebar:** logo, blok "Lokasi aktif" (Super Admin: pemilih cabang; Admin Cabang dan Kasir: label cabang tetap tanpa dropdown), dan menu dengan item aktif berlatar `primary`.
- **Topbar:** tanggal hari ini, lonceng notifikasi internal, dan profil pengguna dengan label peran.
- Header halaman dan 3–4 stat card.

**Menu sesuai PRD, ditampilkan menurut peran (diterapkan juga di backend):** Dasbor, Transaksi, Pesanan & Pengantaran, Canvassing, Pengeluaran, Laporan, Pengguna, serta Cabang dan Gambar QR statis (khusus Super Admin). Tidak ada menu pengaturan harga (PRD §5). Menu untuk fitur yang belum dibangun tidak perlu ditampilkan.

**Stat card dasbor Sprint 1:** hanya metrik yang datanya ada, yaitu jumlah transaksi dan pendapatan hari ini (yang `dibayar_pada` terisi), dengan empty state bila belum ada data.

**Jangan ambil:**
- Pencarian global, "Status Depot", "Terminal Kasir #01 Online", dan foto profil.
- Widget kasir cepat di dasbor.
- Rincian "Galon terjual 142" yang membutuhkan data dari kanal lain yang belum ada.

### 06 — Transaksi kasir (US-09 website, US-24 Android)
**Ambil (sebagai acuan Flutter atau form walk-in):** kartu produk dengan harga, stepper jumlah, panel rincian dengan total besar, pilihan Tunai/QR, dan tombol simpan lebar dengan state loading untuk mencegah double-submit.

**Jangan ambil:**
- Kas awal laci, sesi shift, "Cetak struk", diskon, dan F10.
- Produk tutup/segel.
- Galon baru Rp45.000 (harga belum ditetapkan, PRD §15 no. 2).
- Gabungan isi ulang + galon baru dalam satu transaksi (PRD §15 no. 1).

### 07 — Pesanan & pengantaran (sprint berikutnya)
**Ambil:** stat card ringkas, tab filter status dengan jumlah, tabel pesanan (ID/waktu, pelanggan, alamat/catatan, rincian dan tagihan, kurir, status, aksi), aksi "Pilih Kurir" (kurir dari cabang yang sama), dan tombol "Tambah Pesanan Manual".

**Jangan ambil:** kartu armada dengan GPS dan muatan, "Galon kosong kembali/dipinjamkan" (peminjaman dihapus dari scope), peta radius, rating, dan ekspor (belum diputuskan).

### 08 — Canvassing (K2, sprint berikutnya)
**Ambil:** tabel per kurir berisi jumlah galon terjual, setoran (jumlah × Rp7.000), badge kesesuaian, dan tombol verifikasi.

**Jangan ambil:** "Awal keluar", "Kosong kembali", dan "Sisa isi", karena itu pencatatan stok, sedangkan PRD hanya mencatat jumlah. Rute/wilayah juga tidak diambil.

### 09 — Laporan (sprint berikutnya)
**Ambil:** filter periode dan cabang (cabang hanya untuk Super Admin), stat card pendapatan, pengeluaran, dan **selisih**, volume per kanal, grafik batang per kanal, serta tabel rekap harian.

**Catatan:** Flux versi gratis tidak punya komponen grafik. Library grafik butuh persetujuan tim sebelum ditambahkan.

**Jangan ambil:** sirkulasi/retensi galon, laba dan margin %, persentase kenaikan, kinerja dan rating kurir, audit higienitas, catatan manajemen, "sinkronisasi realtime", dan ekspor CSV/PDF (belum diputuskan).

### 10 — Pengaturan cabang & staf (US-06, US-07)
**Ambil:**
- Tab Profil Cabang / Staf.
- Form identitas cabang: nama, alamat, latitude/longitude.
- Tabel staf: nama, email, badge peran, status Aktif/Nonaktif, aksi ubah dan nonaktifkan.
- Tombol "Tambah Staf".

**Aturan:** Admin Cabang hanya melihat dan mengelola Kasir/Kurir di cabangnya. Super Admin mengelola Admin Cabang.

**Jangan ambil:** shift kerja, perangkat/armada, kebijakan bon, WhatsApp bot, radius dan SLA, mesin filtrasi, unduh absensi, kepala cabang, dan jam buka. Tidak ada pengaturan tarif di layar ini: semua harga ada di file konfigurasi (PRD §5–§6).

## Konflik dengan PRD (menunggu keputusan PO)

| ID | Template | PRD saat ini | Sampai diputuskan |
|---|---|---|---|
| K1 | Kasir bekerja di website (layar 06) | **Selesai (PRD v1.2):** Kasir memakai website dan Android | Pola layar 06 dipakai di website Kasir dan menjadi acuan Android |
| K2 | Setoran canvassing diverifikasi "Admin" | Diverifikasi Kasir (PRD §6, §7.F) | Ikuti PRD: Kasir |
| K3 | "Pesan sebagai Tamu" tanpa akun | Pelanggan login lalu memesan (PRD §4) | Tidak ada pemesanan tamu |
| K4 | Login pelanggan tanpa Google | Google + nomor HP/password | Tampilkan keduanya |
| K5 | Timeline "Pesanan Diterima → Filtrasi & Ozon → Dalam Perjalanan → Diterima & COD" | `diproses → dijemput → diantar → selesai` | Pakai status PRD |
| K6 | Galon baru Rp45.000 dan transaksi gabungan | **Sebagian selesai (PRD v1.2 §6):** satu transaksi satu jenis item; harga di file konfigurasi; nominal galon baru masih nilai sementara | Jangan gabungkan item; ambil harga dari `config/sigalon.php` |
| K7 | Produk tutup galon/tisu Rp1.000 | Tidak ada di PRD | Tidak dibuat |
| K8 | Klaim higienitas/uji lab/SNI | Tidak ada data | Jangan tampilkan klaim yang belum dikonfirmasi mitra |
| K9 | Jam operasional, alamat, nomor WhatsApp, radius | Data mitra belum tersedia | Ambil dari data mitra/konfigurasi, bukan dari gambar |
