# Sprint 3 — SIGALON

**Sumber:** Form J-01 Sprint Planning dan Sprint Backlog, Sprint ke-3  
**Periode sprint:** 9–22 November 2026  
**Tanggal Sprint Planning:** 9 November 2026 · **Anggota hadir:** belum diisi  
**Scrum Master:** Reyhan Dwi Saputra · **Product Owner:** Fikri Adiyofa

## A. Sprint Goal
Melengkapi verifikasi canvassing, pencatatan pengeluaran, laporan otomatis, dan linimasa pesanan pelanggan, sehingga pemilik dan Admin Cabang mendapat gambaran pendapatan dan pengeluaran yang akurat tanpa rekap manual.

## B. Sprint Backlog

| Kode US | Judul user story | Acceptance criteria (ringkas) | Prioritas | SP | PIC utama |
|---|---|---|---|---:|---|
| US-13 | Pelanggan melihat riwayat, status, dan linimasa pesanan | Pelanggan hanya melihat pesanan miliknya; linimasa tahap demi tahap dari RIWAYAT_STATUS; status terbaru tampil tanpa muat ulang manual; pelanggan dapat membatalkan pesanan berstatus "diproses" | Should | 3 | Nurul |
| US-16 | Kasir memverifikasi catatan canvassing | Daftar catatan berstatus "menunggu_verifikasi" di cabangnya; tanpa data pelanggan; Rp7.000 × jumlah, tunai/QR; terima → "tervalidasi" (diverifikasi_oleh terisi); tolak wajib alasan → "ditolak"; setiap perubahan tercatat di RIWAYAT_STATUS | Must | 3 | Shalwa |
| US-19 | Admin Cabang mencatat pengeluaran depot | Form kategori, jumlah, tanggal, keterangan; dapat ditambah, diubah, dihapus, dan difilter per bulan; hanya data cabangnya | Must | 3 | Reyhan |
| US-20 | Laporan penjualan otomatis harian/mingguan/bulanan | Admin Cabang melihat pendapatan per periode & per kanal, total pengeluaran, dan selisihnya; hanya COD selesai dan canvassing tervalidasi yang dihitung; Super Admin melihat gabungan 2 cabang pilot | Must | 5 | Fikri |
| US-21 | Reset password pelanggan manual & tautan WhatsApp | Kasir/Admin Cabang membuat password sementara untuk akun nomor HP setelah verifikasi identitas; pelanggan wajib menggantinya saat login; pelanggan dapat mengubah password; tautan wa.me tersedia di halaman lupa password & detail pesanan | Should | 2 | Velisa |
| US-22 | API autentikasi staf & endpoint transaksi dasar untuk aplikasi Flutter | Kasir & kurir login mendapat token Sanctum; akun nonaktif ditolak; endpoint simpan walk-in, COD manual, dan canvassing memvalidasi role & cabang; terdokumentasi | Should | 3 | Shalwa |
| US-30 | Notifikasi dalam aplikasi | Notifikasi (tabel bawaan Laravel) untuk pesanan baru (Kasir/Admin Cabang), penugasan (Kurir), perubahan status & pembatalan (Pelanggan); tidak menampilkan status "terkirim" untuk WhatsApp | Should | 2 | Reyhan |

**Total story point terpilih:** 21 · **Kapasitas/velocity acuan:** 23 SP (sementara, dari rencana Sprint 2; ganti dengan velocity aktual setelah Sprint 2 selesai) · **Selisih:** +2 SP

## C. Breakdown task

| No | Kode US | Uraian task | Estimasi (jam) | PIC | Status awal |
|---:|---|---|---:|---|---|
| 1 | US-13 | Halaman riwayat pesanan milik pelanggan | 3 | Nurul | To Do |
| 2 | US-13 | Komponen linimasa dari RIWAYAT_STATUS + pembaruan otomatis (Livewire polling) | 3 | Shalwa | To Do |
| 3 | US-13 | Tombol batalkan pesanan oleh pelanggan (hanya status diproses) | 2 | Velisa | To Do |
| 4 | US-16 | Aturan data canvassing (status menunggu_verifikasi, harga dari file konfigurasi, tunai/QR) | 2 | Reyhan | To Do |
| 5 | US-16 | Halaman daftar verifikasi canvassing + tombol terima/tolak dengan alasan | 3 | Shalwa | To Do |
| 6 | US-16 | Feature test verifikasi canvassing & isolasi cabang | 2 | Fikri | To Do |
| 7 | US-19 | Buat form tambah/edit pengeluaran (kategori, jumlah, tanggal, keterangan) | 3 | Reyhan | To Do |
| 8 | US-19 | Daftar pengeluaran + hapus + filter per bulan | 3 | Shalwa | To Do |
| 9 | US-20 | Desain UI halaman laporan (pilihan periode harian/mingguan/bulanan) | 3 | Nurul | To Do |
| 10 | US-20 | Query agregasi pendapatan per periode & per kanal (tanpa pesanan dibatalkan/ditolak) | 4 | Fikri | To Do |
| 11 | US-20 | Hitung total pengeluaran & selisih pada laporan | 3 | Velisa | To Do |
| 12 | US-20 | Tampilan laporan gabungan 2 cabang untuk Super Admin | 3 | Reyhan | To Do |
| 13 | US-21 | Fitur reset password sementara oleh Kasir/Admin Cabang | 2 | Velisa | To Do |
| 14 | US-21 | Halaman ubah password pelanggan + wajib ganti password sementara | 2 | Nurul | To Do |
| 15 | US-21 | Tautan wa.me ke cabang di halaman lupa password & detail pesanan | 1 | Reyhan | To Do |
| 16 | US-22 | Setup rute API login staf (kasir/kurir) & token Sanctum; tolak akun nonaktif | 3 | Fikri | To Do |
| 17 | US-22 | Endpoint simpan transaksi walk-in/COD manual/canvassing + validasi role & cabang | 4 | Shalwa | To Do |
| 18 | US-22 | Dokumentasi endpoint (Postman collection) & uji | 2 | Nurul | To Do |
| 19 | US-30 | Setup notifikasi Laravel & kirim pada event pesanan baru, penugasan, status, pembatalan | 3 | Reyhan | To Do |
| 20 | US-30 | Ikon & daftar notifikasi di website (staf dan pelanggan) | 2 | Velisa | To Do |

**Total estimasi:** 53 jam

## D. Kesepakatan tambahan sprint ini

| No | Hal yang disepakati | PIC |
|---:|---|---|
| 1 | Daily Scrum setiap pagi pukul 08.00 via WhatsApp Group | Seluruh tim |
| 2 | Sprint 3 bergantung pada Sprint 1–2 (tabel, akun kurir, data walk-in/COD, RIWAYAT_STATUS); item yang belum selesai dikerjakan lebih dulu | Reyhan |
| 3 | Canvassing dicatat kurir lewat Android (Sprint 4); di Sprint 3 verifikasi kasir diuji dengan data dari endpoint API/seeder | Seluruh tim |
| 4 | Canvassing tidak memerlukan data pelanggan (sudah diputuskan) | Fikri |
| 5 | Peminjaman galon (US-17, US-18 versi lama) dikeluarkan dari ruang lingkup | Fikri |
| 6 | Setiap user story diuji (unit/feature test) sebelum dinyatakan Done | Seluruh tim |
| 7 | Scope data tetap 2 cabang pilot | Seluruh tim |

## Dependensi dari Sprint 1–2
- US-13 dan US-16 membutuhkan tabel RIWAYAT_STATUS dan service transisi status dari US-12.
- US-20 membutuhkan data TRANSAKSI (US-09–US-11) dan PENGELUARAN (US-19).
- US-21 membutuhkan akun nomor HP (US-08) dan akses web Kasir (US-01).
- US-22 menambah Laravel Sanctum, yang sudah disetujui di PRD §10.

## Referensi desain per story

| Story | Layar referensi (`docs/design/references/`) |
|---|---|
| US-13 | `04-pelanggan-status-riwayat.jpg` (label linimasa wajib memakai status PRD) |
| US-16 | `08-admin-canvassing.jpg` |
| US-20 | `09-admin-laporan.jpg` |
| US-19, US-21, US-30 | Tidak ada layar khusus; pakai pola form/tabel dari design system |

## Catatan rekonsiliasi J-01 dengan PRD, ERD, dan kode (25 Oktober 2026)
Bagian ini bukan isi J-01.
- **US-20 butuh library grafik.** Flux versi gratis tidak menyediakan grafik, dan pilihan library masih terbuka (PRD §16 terbuka no. 5).
- **US-21 butuh penanda "wajib ganti password".** ERD tidak punya kolom untuk itu, jadi cara penyimpanannya perlu disepakati sebelum coding.
- **US-30 membuat tabel `notifications`** bawaan Laravel lewat migration. Ini sesuai PRD §8, karena tabel bawaan tidak digambar di ERD.

## Definition of Done
Mengikuti PRD v1.2 §14. Tambahan sprint ini: setiap story diuji dengan unit/feature test sebelum Done (kesepakatan no. 6).

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
