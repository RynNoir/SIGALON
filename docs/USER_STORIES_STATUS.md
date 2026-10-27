# User Story Status — SIGALON Sprint 1

**Terakhir diperbarui:** 25 Oktober 2026  
**Penting:** Status diperbarui berdasarkan bukti tes. Status `DONE` hanya setelah review oleh anggota lain (DoD no. 8).

| ID | Ringkasan | PIC J-01 | Status | Tes/bukti | Blocker/keputusan |
|---|---|---|---|---|---|
| US-01 | Login staf (Super Admin, Admin Cabang, Kasir) ke website | Reyhan | REVIEW | 25 Okt 2026, branch `fix/sprint-1-penyesuaian-prd-v1.2`: `composer test` Pint lulus, PHPStan 0 error, PHPUnit 94/94. Tes: `tests/Feature/Auth/StaffWebAccessTest.php`, `tests/Unit/RoleTest.php`. Kasir bisa masuk; Kurir diarahkan ke halaman akses ditolak; akun nonaktif ditolak | Reviewer belum ditunjuk (DoD no. 8). Cek visual di browser belum dilakukan |
| US-02 | Login Pelanggan dengan Google | Velisa | REVIEW | 25 Okt 2026, branch yang sama: PHPUnit 94/94. Tes: `tests/Feature/Auth/GoogleAuthTest.php`, `CustomerLoginTest.php`, `CustomerRegistrationTest.php`, `CustomerAccessTest.php`. Pelanggan di tabel `pelanggan` (ERD); akun Google tersimpan dengan `no_hp` dan `alamat` kosong; `state` OAuth wajib valid | Reviewer belum ditunjuk (DoD no. 8). Kredensial asli Google OAuth dan nomor WA admin diisi di `.env` masing-masing |
| US-03 | Landing page: info produk, harga, peta cabang | Shalwa | REVIEW | 25 Okt 2026, branch yang sama: PHPUnit 94/94. Tes: `tests/Feature/LandingPageTest.php` (termasuk escaping nama/alamat cabang). Build Vite lulus | Reviewer belum ditunjuk (DoD no. 8). Popup peta kini dibangun dengan `textContent`; verifikasi visual popup di browser belum dilakukan |
| US-04 | Dashboard Super Admin | Reyhan | REVIEW | 25 Okt 2026, branch `feature/US-04-dashboard-super-admin`: `composer test` Pint lulus, PHPStan 0 error, PHPUnit 105/105; `npm run build` lulus. Tes: `tests/Feature/Dashboard/SuperAdminDashboardTest.php`, `tests/Feature/Foundation/SchemaTest.php`. Query ringkasan diuji juga di MariaDB | Reviewer belum ditunjuk (DoD no. 8). Cek visual di browser belum dilakukan. Data nyata baru ada setelah Sprint 2 (US-09–US-11) |
| US-05 | Dashboard Admin Cabang | Velisa | REVIEW | 25 Okt 2026, branch `feature/US-05-dashboard-admin-cabang`: `php artisan test` 132/132 lulus, `vendor\bin\phpstan analyse --memory-limit=1G` 0 error, `vendor\bin\pint --test` lulus; `npm run build` lulus. Tes: `tests/Feature/Dashboard/AdminCabangDashboardTest.php` (24 tes). Isolasi data cabang teruji; route `/dashboard/cabang/{cabang}` terlindungi otorisasi server-side | Keputusan final PO: Kasir dan Kurir DITOLAK dari dashboard cabang (403/redirect), hanya Admin Cabang (cabang sendiri) dan Super Admin (cabang aktif mana pun). Reviewer belum ditunjuk (DoD no. 8). Cek visual di browser belum dilakukan |
| US-06 | Super Admin mengelola data cabang dan akun Admin Cabang | Shalwa | REVIEW | 25 Okt 2026, branch `feature/US-06-super-admin-mengelola-data-cabang-dan-akun-admin-cabang`: `composer test` Pint lulus, PHPStan 0 error, PHPUnit 129/129 lulus (488 assertions); `npm run build` lulus. Tes: `tests/Feature/Admin/CabangManagementTest.php`, `tests/Feature/Admin/AdminCabangManagementTest.php` | Reviewer belum ditunjuk (DoD no. 8). Sesuai keputusan PO: reset password langsung diisi Super Admin, validasi koordinat nullable numeric, status aktif/nonaktif ditampilkan tanpa toggle cabang, akun Admin Cabang dinonaktifkan tanpa hapus |
| US-07 | Admin Cabang mengelola akun Kasir & Kurir | Nurul | REVIEW | 25 Okt 2026, branch `feature/US-07-kelola-kasir-kurir`: `composer test` Pint lulus, PHPStan 0 error, PHPUnit 153/153 (586 assertions); `npm run build` lulus. Tes: `tests/Feature/Cabang/StafManagementTest.php` (24 tes, 98 assertions). Isolasi `cabang_id` dan pembatasan role diuji | Reviewer belum ditunjuk (DoD no. 8). Sesuai aturan: Admin Cabang hanya mengelola Kasir dan Kurir di cabangnya sendiri; pembatasan cabang_id ditegakkan di backend (403); akun dinonaktifkan tanpa hapus; reset password staf disediakan |

## Cara memperbarui status
Setiap perubahan status wajib memiliki:
- nama/ID story;
- PIC;
- acceptance criteria yang sudah diuji;
- perintah tes dan hasilnya;
- reviewer atau tautan PR;
- blocker yang tersisa.

**Jangan mengubah status menjadi `DONE` hanya karena kode sudah ditulis.** Jangan mengarang nomor PR, commit hash, hasil tes, atau tanggal penyelesaian.
