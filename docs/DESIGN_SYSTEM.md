# Design System — SIGALON

## Arah visual
**Aqua Professional:** bersih, modern, profesional, dengan warna utama Light Blue dan Cyan (PRD v1.2 §11). Latar Ice Blue, kartu putih berradius besar dengan bayangan halus.

Referensi visual ada di `docs/design/README.md` dan `docs/design/references/`. Gambar referensi **hanya untuk gaya visual**; warnanya tidak dipakai lagi. Fitur yang tidak ada di PRD tidak boleh ditiru, dan bila bertentangan, PRD yang berlaku.

## Token warna
Sumber: palet PRD v1.2 §11 (25 Oktober 2026). Nilai bertanda * digelapkan dari palet PRD karena warna aslinya gagal kontras teks WCAG AA (PRD §9 mewajibkan kontras cukup). Warna asli PRD tetap tersedia untuk elemen non-teks.
Token didefinisikan di `resources/css/app.css` (`@theme`). Gunakan nama kelas Tailwind di tabel ini, misalnya `bg-primary`, `text-muted`, `bg-surface-low`, dan jangan menulis hex langsung di Blade atau Flutter.

| Token | Hex | Kelas Tailwind | Penggunaan |
|---|---|---|---|
| Brand (PRD Primary Cyan) | `#0891B2` | `brand` | Ikon, garis aksen, cincin fokus, marker. **Bukan** untuk teks kecil atau latar tombol bertulisan putih (3.68:1) |
| Brand Bright (PRD Bright Cyan Accent) | `#22D3EE` | `brand-bright` | Aksen dekoratif seperlunya; tidak untuk teks |
| Primary* | `#0E7490` | `primary` / `accent` | Tombol utama, tautan, angka penting (total, pendapatan), teks eyebrow |
| Primary Strong* | `#155E75` | `primary-strong` | Hover tombol utama, item menu sidebar aktif |
| Primary Soft (PRD Light Cyan) | `#CFFAFE` | `primary-soft` | Tombol sekunder, kotak ikon pada stat card, highlight lembut |
| Deep (PRD Deep Blue) | `#0C4A6E` | `deep` | Judul dan navigasi penting |
| Ink (PRD Primary Text) | `#0F172A` | `ink` | Teks utama |
| Muted* | `#5B6779` | `muted` | Teks sekunder dan keterangan (PRD `#64748B` gagal di atas Ice Blue: 4.46:1) |
| Surface (PRD Ice Blue) | `#F0F9FF` | `surface` | Latar halaman |
| Surface Low | `#E0F2FE` | `surface-low` | Header tabel, panel di dalam kartu, latar ringkasan |
| White | `#FFFFFF` | `white` | Kartu, form, dan input |
| Line (PRD Border) | `#E2E8F0` | `line` | Garis pemisah dan border kartu |
| Outline | `#64748B` | `outline` | Border elemen interaktif yang wajib terlihat (checkbox, radio) |
| Info* | `#1D4ED8` di atas `#DBEAFE` | `info` / `info-soft` | Badge status proses (PRD `#2563EB`) |
| Success* | `#15803D` di atas `#DCFCE7` | `success` / `success-soft` | Selesai, aktif, berhasil (PRD `#16A34A` hanya untuk titik/ikon) |
| Warning* | `#B45309` di atas `#FEF3C7` | `warning` / `warning-soft` | Menunggu, perlu verifikasi (PRD `#D97706` hanya untuk ikon) |
| Danger* | `#B91C1C` di atas `#FEE2E2` | `danger` / `danger-soft` | Error, gagal, nilai pengeluaran, aksi destruktif (PRD `#DC2626`) |

Warna aksen komponen Flux (`--color-accent`) memakai **Primary**.

### Kontras (WCAG AA, teks normal ≥ 4.5:1, komponen UI ≥ 3:1)
| Kombinasi | Rasio | Status |
|---|---|---|
| Putih di atas Primary | 5.36 | Lulus |
| Putih di atas Primary Strong | 7.27 | Lulus |
| Primary di atas Surface / Surface Low / Primary Soft | 5.03 / 4.67 / 4.79 | Lulus |
| Deep di atas putih | 9.46 | Lulus |
| Ink di atas Surface | 16.75 | Lulus |
| Muted di atas putih / Surface / Surface Low / Primary Soft | 5.74 / 5.38 / 5.00 / 5.12 | Lulus |
| Info di atas putih / Info Soft | 6.70 / 5.49 | Lulus |
| Success di atas putih / Success Soft | 5.02 / 4.57 | Lulus |
| Warning di atas putih / Warning Soft | 5.02 / 4.51 | Lulus |
| Danger di atas putih / Danger Soft | 6.47 / 5.30 | Lulus |
| Outline di atas putih / Surface (komponen UI) | 4.76 / 4.46 | Lulus |
| Brand di atas Surface (komponen UI, bukan teks) | 3.45 | Lulus untuk ikon/garis saja |
| PRD Primary Cyan `#0891B2` sebagai teks atau di bawah teks putih | 3.68 | Gagal — pakai Primary |
| PRD Success `#16A34A` / Warning `#D97706` dengan teks putih | 3.30 / 3.19 | Gagal — pakai versi * |

### Nada badge status
Badge selalu berisi teks. Warna hanya sebagai penguat.

| Status | Nada |
|---|---|
| COD: Diproses, Dijemput | Info |
| COD: Diantar | Primary Soft dengan teks Primary |
| COD: Selesai; akun Aktif; setoran Terverifikasi | Success |
| Menunggu kurir/verifikasi; belum dibayar | Warning |
| Gagal/ditolak | Danger |
| Akun Nonaktif | Surface Low dengan teks Muted |

## Tipografi dan bentuk
- **Font:** Plus Jakarta Sans untuk seluruh teks, dengan bobot 400/500/600/700. Font dimuat lewat `vite.config.js` (diunduh dari Bunny Fonts saat build lalu di-host sendiri). Gunakan font yang sama di Flutter.
- **Skala teks:**
  - Judul halaman: 28–32 px tebal.
  - Judul seksi/kartu: 18–20 px semibold.
  - Angka stat: 28–32 px tebal.
  - Isi: 14–16 px.
  - Label/keterangan: 12–13 px.
  - Eyebrow: 11–12 px huruf kapital, semibold, `primary`, tracking lebar.
- **Radius:** 8 px untuk input/tombol, 16 px untuk kartu, dan pill (`rounded-full`) untuk badge.
- **Bayangan:** satu tingkat halus untuk kartu (`shadow-card`). Hindari bayangan tebal, glassmorphism, gradien berlebihan, dan animasi dekoratif.
- **Input:** pakai input Flux bawaan (latar putih dengan border). Jangan meniru input tanpa border dari gambar referensi, karena batas input harus tetap terlihat.
- **Tombol sentuh:** target tinggi sekitar 44–48 px.
- **Ikon:** Heroicons (bawaan Flux). Jika ikon tidak tersedia, impor dari Lucide lewat `php artisan flux:icon <nama>`. Di Flutter, gunakan satu keluarga ikon yang padanannya konsisten.
- **Mode tampilan:** hanya mode terang. Template tidak memiliki mode gelap, jadi jangan menambah varian `dark:` baru sampai diputuskan.

## Pola komponen
- **Header halaman:** eyebrow (opsional), judul, satu kalimat deskripsi, dan maksimal 1–2 tombol aksi di kanan.
- **Stat card:** kartu putih berisi label, angka besar, keterangan satu baris, dan ikon dalam kotak `primary-soft` di kanan atas. Maksimal 4 kartu per baris.
- **Tabel:** header huruf kapital kecil di atas `surface-low`, aksi di kolom terakhir, pagination di bawah, dan empty state di dalam tabel. Di ponsel, ubah menjadi daftar kartu.
- **Input jumlah:** stepper (− angka +) dengan tombol pintasan.
- **Pilihan bersegmen:** misalnya Tunai / QR statis. Yang terpilih `primary` dengan teks putih, yang lain `primary-soft`.
- **Ringkasan total:** panel `surface-low` dengan total besar berwarna `primary`, selalu terlihat sebelum tombol simpan.

## Pola per platform
### Website admin
- Sidebar di desktop: logo, blok "Lokasi aktif" (Super Admin: pemilih cabang; Admin Cabang dan Kasir: label tetap), dan menu dengan item aktif berlatar `primary-strong`. Di layar kecil, sidebar menjadi menu yang bisa dibuka-tutup.
- Topbar: tanggal hari ini, notifikasi internal, dan profil dengan label peran.
- Menu hanya untuk fitur yang sudah ada dan sesuai peran. Pembatasan juga wajib di backend.
- Ringkasan metrik di atas; tabel dan filter di bawah.
- Super Admin dapat memfilter cabang; Admin Cabang hanya melihat cabangnya.

### Kasir Android
- Form satu kolom dan urutan input logis.
- Pilihan Walk-in/COD jelas.
- Total otomatis dan terlihat sebelum simpan.
- Pesan validasi spesifik, tombol simpan jelas, cegah double-submit.
- Galon kosong baru hanya tersedia di Walk-in.

### Kurir Android
- Kartu tugas, bukan tabel lebar.
- Alamat, jumlah, status, dan tindakan utama terlihat.
- Tampilkan transisi status yang sah saja.
- Canvassing meminta jumlah galon tanpa data pelanggan.
- Konfirmasi tindakan yang berdampak dan feedback sukses/gagal.

### Website pelanggan
- Landing page sederhana dengan CTA pemesanan.
- Harga dan cara layanan jelas.
- Peta cabang dan alamat mudah ditemukan.
- Form pesanan ringkas, riwayat/status dalam timeline.
- Login Google atau nomor HP + password.
- Tautan WhatsApp dengan teks terisi; jangan klaim pesan terkirim otomatis.

## State wajib
- Loading/skeleton jika menunggu data.
- Empty state dengan instruksi tindakan berikutnya.
- Error state dengan pesan yang bisa ditindaklanjuti.
- Success feedback (toast) setelah simpan.
- Disabled/loading state saat submit agar mencegah duplikasi.
- Validasi form dan konfirmasi aksi destruktif.
- Status tidak boleh dibedakan dengan warna saja; selalu sertakan label teks.
- Responsif pada ponsel, tablet, dan desktop.

## Prinsip UX
1. Task-first, bukan dekorasi-first.
2. Kurangi input berulang.
3. Tampilkan status dan total secara eksplisit.
4. Konsistensi komponen dan bahasa.
5. Aksesibilitas dan kontras yang memadai.
6. Animasi ringan dan tidak menghambat kerja.
7. Jangan menampilkan data cabang lain kepada staf cabang.
8. Lebih ringkas daripada referensi: tampilkan hanya informasi yang dibutuhkan tugas dan didukung data nyata.
