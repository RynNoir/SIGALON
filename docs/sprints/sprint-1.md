# Sprint 1 — SIGALON

**Periode acuan terbaru:** 12–25 Oktober 2026  
**Goal:** Membangun fondasi SIGALON agar staf dapat mengakses sistem sesuai peran, cabang dapat ditampilkan pada peta, dan transaksi/dashboard dasar mulai beralih dari pencatatan manual ke digital.  
**Status dokumen:** Perlu rekonsiliasi dengan J-01 yang diunggah.

> J-01 yang tersedia mencantumkan periode 5–11 Oktober 2026 dan US-01 sampai US-07 dengan total 22 story point. Jadwal proyek terbaru menyatakan sprint dua minggu mulai 12 Oktober. Jangan menganggap daftar lama sudah otomatis disetujui sebagai backlog final Sprint 1.

## Aturan sprint
- Fokus hanya pada story yang dikonfirmasi masuk Sprint 1 oleh Product Owner.
- Story belum `DONE` sampai acceptance criteria, tes, dan review terpenuhi.
- AI tidak melakukan commit/push/merge. AI hanya memberikan saran pesan commit.
- Semua tugas harus muncul pada papan tugas tim yang disepakati.
- Status harus diperbarui oleh pemilik/reviewer berdasarkan bukti.

## Kandidat user story dari J-01 (belum otomatis dianggap final)

| ID | User story | AC ringkas dari J-01 | Prioritas J-01 | SP | PIC utama pada J-01 | Status awal |
|---|---|---|---|---:|---|---|
| US-01 | Login Super Admin/Admin Cabang ke website | Login email/password sesuai role; role salah ditolak | Must | 3 | Reyhan | TODO — konfirmasi |
| US-02 | Login pelanggan menggunakan akun Google | Login Google; akun baru tersimpan | Must | 2 | Velisa | TODO — perlu diselaraskan dengan opsi nomor HP + password |
| US-03 | Landing page menampilkan lokasi cabang di peta | Peta menampilkan 13 titik; nama/alamat muncul saat marker diklik | Should | 3 | Shalwa | TODO — konfirmasi cakupan data |
| US-04 | Dashboard Super Admin | Ringkasan penjualan/transaksi seluruh cabang | Must | 5 | Reyhan | TODO — perlu sumber data transaksi |
| US-05 | Dashboard Admin Cabang | Hanya data cabang admin yang login | Must | 3 | Velisa | TODO — perlu uji isolasi cabang |
| US-06 | Super Admin mengelola akun Admin Cabang | Tambah/edit/hapus akun | Should | 3 | Shalwa | TODO — periksa kebijakan hapus/nonaktif |
| US-07 | Admin Cabang mengelola akun Kasir & Kurir | Tambah/edit/hapus akun pada cabangnya | Should | 3 | Nurul | TODO — role Kasir dipisah dan perlu konsisten di schema |

**Total pada J-01 yang diunggah:** 22 SP. Ini bukan konfirmasi kapasitas final untuk sprint dua minggu.

## Perubahan/keputusan terbaru yang harus tercermin saat refinement
- Role Kasir terpisah dari Kurir.
- Pelanggan dapat memilih Google atau nomor HP + password (tanpa OTP) — perlu penambahan/penyesuaian AC.
- Peminjaman galon dihapus dari scope.
- Galon kosong baru dijual hanya melalui walk-in; harga dapat diatur Super Admin.
- WhatsApp menggunakan tautan `wa.me`, bukan API otomatis.
- Kasir dapat mencatat walk-in dan COD.
- Pengeluaran hanya dicatat Admin Cabang.
- Canvassing Rp7.000/galon, jumlah saja, diverifikasi Kasir; kemungkinan masuk sprint berikutnya sesuai backlog.
- Hosting/domain ditunda.
- Sistem pilot 2 cabang, rancangan mendukung 13 cabang.

## Definition of Ready
- [ ] Tujuan story dan aktor jelas
- [ ] Acceptance criteria bisa diuji
- [ ] Dependensi teridentifikasi
- [ ] Estimasi disepakati tim
- [ ] UI/data/API cukup jelas
- [ ] Story cukup kecil untuk sprint

## Definition of Done
- [ ] Acceptance criteria terpenuhi
- [ ] Tes relevan dijalankan dan hasil dicatat
- [ ] Otorisasi dan batas cabang diuji bila relevan
- [ ] Loading/empty/error/success state ditangani bila relevan
- [ ] Tidak ada bug blocker/critical yang diketahui
- [ ] Perubahan direview
- [ ] Status dan bukti diperbarui
- [ ] AI memberikan saran commit saja; commit dilakukan manual oleh developer

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

## Keputusan sebelum mengunci backlog
1. Konfirmasi daftar US-01 sampai US-07 masih menjadi scope Sprint 1.
2. Tentukan apakah US-02 mencakup login Google saja atau Google + nomor HP/password.
3. Konfirmasi data cabang dan koordinat yang tersedia.
4. Tentukan definisi metrik dashboard jika transaksi belum sepenuhnya dibuat.
5. Pastikan skema role mencakup `super_admin`, `admin_cabang`, `kasir`, `kurir`.
6. Sepakati apakah story tertentu harus ditunda karena ketergantungan data transaksi.
