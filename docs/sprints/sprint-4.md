# Sprint 4 — SIGALON

**Sumber:** Form J-01 Sprint Planning dan Sprint Backlog, Sprint ke-4  
**Periode sprint:** 23 November – 6 Desember 2026  
**Tanggal Sprint Planning:** 23 November 2026 · **Anggota hadir:** belum diisi  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa

## A. Sprint Goal
Menyediakan aplikasi Android bagi kasir dan kurir CV. Aufa agar transaksi, status pengantaran, pembayaran, dan canvassing tercatat langsung dari lapangan, lalu memastikan seluruh alur web dan mobile siap diuji di cabang pilot.

## B. Sprint Backlog

| Kode US | Judul user story | Acceptance criteria (ringkas) | Prioritas | SP | PIC utama |
|---|---|---|---|---:|---|
| US-23 | Login staf (kasir/kurir) di aplikasi Android | Login dengan akun staf; token tersimpan aman; role selain kasir/kurir dan akun nonaktif ditolak; menu menyesuaikan role | Must | 2 | Velisa |
| US-24 | Kasir mencatat transaksi walk-in & galon baru di aplikasi | Form item (isi ulang/galon baru), jumlah, metode bayar; tanpa data pelanggan; total dari backend; transaksi tampil di dashboard web cabang | Must | 3 | Shalwa |
| US-25 | Kasir mencatat pesanan COD manual di aplikasi | Form nama pemesan, no HP, alamat, jumlah galon, metode bayar; status awal "diproses"; pesanan tampil di web | Must | 3 | Nurul |
| US-26 | Kurir melihat daftar tugas pengiriman | Kurir hanya melihat tugas yang ditugaskan kepadanya; tampil alamat, jumlah galon, metode bayar, dan status (teks + badge) | Must | 3 | Fikri |
| US-27 | Kurir memperbarui status & mengonfirmasi pembayaran | Status berubah berurutan: diproses, dijemput, diantar, selesai; saat selesai kurir mengonfirmasi pembayaran tunai atau bukti bayar QR; perubahan tercatat di RIWAYAT_STATUS dan terlihat di web & linimasa pelanggan | Must | 3 | Shalwa |
| US-28 | Kurir mencatat & memperbaiki penjualan canvassing | Form jumlah galon & metode bayar (tunai/QR, gambar QR ditampilkan); tanpa data pelanggan; tersimpan "menunggu_verifikasi"; catatan yang ditolak dapat diperbaiki lalu kembali "menunggu_verifikasi" | Must | 3 | Velisa |
| US-32 | Kasir memverifikasi canvassing & menugaskan kurir di aplikasi | Kasir dapat menerima/menolak (dengan alasan) catatan canvassing dan memilih kurir aktif untuk pesanan COD dari aplikasi; hasil sama dengan versi web | Should | 2 | Reyhan |
| US-29 | Uji integrasi web dan mobile | Data dari aplikasi muncul di dashboard, linimasa & laporan web; skenario prioritas Dokumentasi v1.1 lulus; UAT bersama cabang pilot; bug tercatat dan diperbaiki | Should | 2 | Reyhan |

**Total story point terpilih:** 21 · **Kapasitas/velocity acuan:** 21 SP (sementara, dari rencana Sprint 3; ganti dengan velocity aktual setelah Sprint 3 selesai) · **Selisih:** 0 SP

## C. Breakdown task

| No | Kode US | Uraian task | Estimasi (jam) | PIC | Status awal |
|---:|---|---|---:|---|---|
| 1 | US-23 | Setup project Flutter, struktur folder & koneksi ke API Laravel | 3 | Fikri | To Do |
| 2 | US-23 | Halaman login + simpan token aman + arahkan menu per role (kasir/kurir) | 3 | Velisa | To Do |
| 3 | US-24 | Buat UI form walk-in (item isi ulang/galon baru, jumlah, metode bayar) | 2 | Nurul | To Do |
| 4 | US-24 | Integrasi form walk-in ke endpoint simpan transaksi | 3 | Shalwa | To Do |
| 5 | US-25 | Buat UI form COD manual (nama pemesan, no HP, alamat, jumlah, metode bayar) | 3 | Nurul | To Do |
| 6 | US-25 | Integrasi form COD ke endpoint & tampilkan hasil/error | 2 | Reyhan | To Do |
| 7 | US-26 | Endpoint API daftar tugas pengiriman milik kurir login (Laravel) | 3 | Fikri | To Do |
| 8 | US-26 | Buat UI daftar tugas dengan label status teks + badge | 3 | Velisa | To Do |
| 9 | US-27 | Endpoint API ubah status berurutan + simpan RIWAYAT_STATUS + kirim notifikasi | 2 | Shalwa | To Do |
| 10 | US-27 | Tombol update status + konfirmasi pembayaran tunai/QR saat selesai + penanganan error | 3 | Shalwa | To Do |
| 11 | US-28 | Buat UI form canvassing (jumlah, metode bayar, tampilan gambar QR) | 2 | Nurul | To Do |
| 12 | US-28 | Integrasi canvassing + daftar catatan ditolak & form perbaikan | 3 | Velisa | To Do |
| 13 | US-32 | Endpoint API verifikasi canvassing & penugasan kurir untuk kasir | 2 | Fikri | To Do |
| 14 | US-32 | UI daftar verifikasi canvassing & pilihan kurir di aplikasi kasir | 3 | Reyhan | To Do |
| 15 | US-29 | Susun & jalankan skenario uji end-to-end (aplikasi → dashboard, linimasa, laporan web) | 3 | Reyhan | To Do |
| 16 | US-29 | UAT bersama cabang pilot & perbaikan bug hasil uji | 3 | Fikri | To Do |

**Total estimasi:** 43 jam

## D. Kesepakatan tambahan sprint ini

| No | Hal yang disepakati | PIC |
|---:|---|---|
| 1 | Daily Scrum setiap pagi pukul 08.00 via WhatsApp Group | Seluruh tim |
| 2 | Sprint 4 bergantung pada API Sprint 3 (US-22); jika belum selesai, diselesaikan lebih dulu. Endpoint tugas kurir, ubah status, dan verifikasi dibuat di sprint ini | Fikri |
| 3 | Platform aplikasi: Android saja; iOS di luar ruang lingkup (sudah diputuskan) | Fikri |
| 4 | Kasir adalah peran terpisah dari Admin Cabang pada kolom role (sudah diputuskan) | Reyhan |
| 5 | Fase implementasi/deployment setelah 6 Desember 2026; jadwal menyusul | Reyhan |
| 6 | Scope data tetap 2 cabang pilot | Seluruh tim |

## Dependensi dari Sprint 1–3
- Seluruh story Android bergantung pada API dan token Sanctum dari US-22.
- US-27 dan US-28 bergantung pada service transisi status dan RIWAYAT_STATUS (US-12, US-16).
- US-29 membutuhkan dashboard (US-04/US-05), linimasa (US-13), dan laporan (US-20) yang sudah berjalan.

## Referensi desain
Aplikasi Flutter memakai palet dan font yang sama dengan web (PRD v1.2 §11). Pola form Kasir mengacu `06-admin-transaksi-kasir.jpg`; daftar tugas Kurir memakai kartu tugas, bukan tabel (`docs/DESIGN_SYSTEM.md`, bagian Kurir Android).

## Catatan rekonsiliasi J-01 dengan PRD dan kondisi proyek (25 Oktober 2026)
Bagian ini bukan isi J-01.
- **Lokasi kode Flutter belum ditentukan.** Belum diputuskan apakah dipisah di repository sendiri atau ditaruh di folder dalam repository ini.
- **Versi Android minimum belum ditetapkan** (PRD §16 terbuka no. 6).
- **Flutter SDK belum terpasang** di komputer pengembang yang dipakai sejauh ini. Siapkan sebelum task no. 1.
- **Token dicabut saat akun dinonaktifkan.** PRD §7.J mewajibkannya, jadi US-23 dan US-22 perlu tes untuk kasus ini.

## Definition of Done
Mengikuti PRD v1.2 §14.

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
