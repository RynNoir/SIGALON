# Design System — SIGALON

## Arah visual
**Aqua Professional:** minimalis, bersih, profesional, ringan, dan berorientasi pada tugas operasional.

## Token warna
| Token | Hex | Kelas Tailwind | Penggunaan |
|---|---|---|---|
| Navy | `#123B5D` | `navy` | Navigasi, heading, teks utama |
| Teal | `#0EA5A4` | `teal` | Aksen dekoratif (ikon, garis, highlight). **Bukan** untuk teks atau latar tombol bertulisan putih |
| Teal Strong | `#0F766E` | `teal-strong` / `accent` | Aksi utama, tombol, link aktif |
| Ice Blue | `#E8F6F7` | `ice` | Latar panel informasi |
| Cloud | `#F7F9FC` | `cloud` | Latar halaman |
| White | `#FFFFFF` | `white` | Kartu dan form |
| Success | `#15803D` | `success` | Selesai/berhasil |
| Warning | `#F59E0B` | `warning` | Perlu perhatian/menunggu. Teks di atasnya wajib Navy, bukan putih |
| Error | `#DC2626` | `danger` | Error, tindakan gagal |
| Border | `#DCE5ED` | `line` | Border halus |

Token didefinisikan di `resources/css/app.css` (`@theme`). Warna aksen komponen Flux (`--color-accent`) memakai Teal Strong.

### Kontras (WCAG AA, teks normal ≥ 4.5:1)
Disetujui tim pada 19 Oktober 2026 karena Teal `#0EA5A4` dan Success `#16A34A` lama gagal kontras dengan teks putih.

| Kombinasi | Rasio | Status |
|---|---|---|
| Putih di atas Teal Strong `#0F766E` | 5.47 | Lulus |
| Putih di atas Success `#15803D` | 5.02 | Lulus |
| Putih di atas Error `#DC2626` | 4.83 | Lulus |
| Navy di atas Warning `#F59E0B` | 5.40 | Lulus |
| Navy di atas putih | 11.61 | Lulus |
| Putih di atas Teal `#0EA5A4` | 3.03 | Gagal — hanya untuk aksen dekoratif |
| Putih di atas Warning `#F59E0B` | 2.15 | Gagal — jangan dipakai |

## Tipografi dan bentuk
- Font: Inter (diunduh dari Bunny Fonts saat `npm run build` lewat `vite.config.js`, lalu di-host sendiri di `public/build`); gunakan Inter juga di Flutter agar konsisten.
- Radius: 8–10 px untuk input/button; 10–14 px untuk card.
- Tombol sentuh: target tinggi sekitar 44–48 px.
- Shadow tipis; hindari glassmorphism berat, gradien berlebihan, dan animasi dekoratif.
- Ikon: satu keluarga ikon konsisten, misalnya Lucide untuk web; gunakan padanan konsisten di Flutter.

## Pola per platform
### Website admin
- Sidebar pada desktop; navigasi responsif pada layar kecil.
- Header: nama halaman, cabang aktif, notifikasi, profil.
- Ringkasan metrik di atas; tabel/filter di bawah.
- Super Admin dapat membandingkan atau memfilter cabang; Admin Cabang hanya melihat cabangnya.

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
- Success feedback setelah simpan.
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
