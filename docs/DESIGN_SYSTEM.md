# Design System — SIGALON

## Arah visual
**Aqua Professional:** minimalis, bersih, profesional, ringan, dan berorientasi pada tugas operasional. Latar keunguan sangat muda, kartu putih berradius besar dengan bayangan halus, dan aksen biru-petrol.

Referensi visual ada di `docs/design/README.md` dan `docs/design/references/`. Gambar referensi **hanya untuk gaya visual**. Fitur yang tidak ada di PRD tidak boleh ditiru, dan bila bertentangan, PRD yang berlaku.

## Token warna
Sumber: palet template referensi (19 Oktober 2026), dengan penyesuaian kontras yang ditandai *.
Token didefinisikan di `resources/css/app.css` (`@theme`). Gunakan nama kelas Tailwind di tabel ini, misalnya `bg-primary`, `text-muted`, `bg-surface-low`, dan jangan menulis hex langsung di Blade atau Flutter.

| Token | Hex | Kelas Tailwind | Penggunaan |
|---|---|---|---|
| Primary | `#00647C` | `primary` | Tombol utama, link, angka penting (total, pendapatan), teks eyebrow |
| Primary Bright | `#007F9D` | `primary-bright` | Item menu sidebar aktif, hover tombol utama |
| Primary Soft | `#E2E7FF` | `primary-soft` | Tombol sekunder, kotak ikon pada stat card, pilihan yang tidak aktif |
| Ink | `#131B2E` | `ink` | Teks utama dan heading |
| Muted* | `#5B666B` | `muted` | Teks sekunder dan keterangan (template `#6E797E` digelapkan agar lulus 4.5:1) |
| Surface | `#FAF8FF` | `surface` | Latar halaman |
| Surface Low | `#F2F3FF` | `surface-low` | Header tabel, panel di dalam kartu, latar ringkasan |
| White | `#FFFFFF` | `white` | Kartu, form, dan input |
| Line | `#E2E7FF` | `line` | Garis pemisah dan border kartu bila diperlukan |
| Outline | `#6E797E` | `outline` | Border elemen interaktif yang wajib terlihat (checkbox, radio, fokus) |
| Info | `#275C81` di atas `#D6EBFF` | `info` / `info-soft` | Badge status proses |
| Success* | `#047857` di atas `#DCFCE7` | `success` / `success-soft` | Selesai, aktif, berhasil (hijau template `#059669` hanya untuk titik dekoratif) |
| Warning | `#92400E` di atas `#FEF3C7` | `warning` / `warning-soft` | Menunggu, perlu verifikasi, belum dibayar |
| Danger | `#BA1A1A` di atas `#FFF0EF` | `danger` / `danger-soft` | Error, gagal, nilai pengeluaran, aksi destruktif |

Warna aksen komponen Flux (`--color-accent`) memakai **Primary**.

### Kontras (WCAG AA, teks normal ≥ 4.5:1, komponen UI ≥ 3:1)
| Kombinasi | Rasio | Status |
|---|---|---|
| Putih di atas Primary | 6.74 | Lulus |
| Putih di atas Primary Bright | 4.64 | Lulus |
| Primary di atas Primary Soft | 5.49 | Lulus |
| Ink di atas Surface | 16.29 | Lulus |
| Muted di atas putih / Surface / Surface Low | 5.90 / 5.60 / 5.35 | Lulus |
| Info di atas Info Soft | 5.86 | Lulus |
| Success di atas Success Soft | 4.99 | Lulus |
| Warning di atas Warning Soft | 6.37 | Lulus |
| Danger di atas putih / Danger Soft | 6.46 / 5.83 | Lulus |
| Outline di atas putih (komponen UI) | 4.47 | Lulus |
| Muted template `#6E797E` di atas Surface | 4.24 | Gagal — jangan dipakai untuk teks |
| Hijau template `#059669` di atas putih | 3.77 | Gagal — hanya untuk titik dekoratif |

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
- Sidebar di desktop: logo, blok "Lokasi aktif" (Super Admin: pemilih cabang; Admin Cabang: label tetap), dan menu dengan item aktif berlatar `primary-bright`. Di layar kecil, sidebar menjadi menu yang bisa dibuka-tutup.
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
