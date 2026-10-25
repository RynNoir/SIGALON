# User Story Status — SIGALON Sprint 1

**Terakhir diperbarui:** 19 Oktober 2026  
**Penting:** Status di bawah adalah status awal dokumentasi, bukan hasil pemeriksaan repository. Semua story berstatus konfirmasi/TODO sampai tim memvalidasi.

| ID | Ringkasan | PIC J-01 | Status | Tes/bukti | Blocker/keputusan |
|---|---|---|---|---|---|
| US-01 | Login staf web dengan role | Reyhan | REVIEW | `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 50/50 (19 Okt 2026). Tes: `tests/Feature/Auth/StaffWebAccessTest.php`, `tests/Unit/RoleTest.php`. Smoke test HTTP lulus | Reviewer belum ditunjuk (DoD no. 8). Cek visual di browser belum dilakukan. Branch `feature/US-01-login-staf-web-with-role` |
| US-02 | Login pelanggan | Velisa | REVIEW | Pint lulus, PHPStan 0 error, PHPUnit 74/74 (20 Okt 2026). Tes: `tests/Feature/Auth/CustomerLoginTest.php`, `tests/Feature/Auth/CustomerRegistrationTest.php`, `tests/Feature/Auth/CustomerAccessTest.php`, `tests/Feature/Auth/GoogleAuthTest.php`. | Reviewer belum ditunjuk (DoD no. 8). Kredensial asli Google OAuth & nomor WA admin asli menyusul dari tim. Branch `feature/US-02-login-pelanggan` |
| US-03 | Landing page dan peta cabang | Shalwa | REVIEW | Pint lulus, PHPStan 0 error, PHPUnit 87/87 (20 Okt 2026). Tes: `tests/Feature/LandingPageTest.php`. Build Vite lulus | Reviewer belum ditunjuk (DoD no. 8). Koordinat Cabang Dadok Tunggul Hitam & Siteba di `CabangSeeder` adalah perkiraan, butuh verifikasi PO via Maps/OSM. Branch `feature/US-03-landing-page-dan-peta-cabang` |
| US-04 | Dashboard Super Admin | Reyhan | TODO — konfirmasi | Belum dicatat | Sumber data transaksi dan definisi pendapatan |
| US-05 | Dashboard Admin Cabang | Velisa | TODO — konfirmasi | Belum dicatat | Isolasi data per cabang |
| US-06 | Pengelolaan Admin Cabang | Shalwa | TODO — konfirmasi | Belum dicatat | Hapus vs nonaktifkan akun |
| US-07 | Pengelolaan Kasir/Kurir | Nurul | TODO — konfirmasi | Belum dicatat | Role schema dan batas akses |

## Cara memperbarui status
Setiap perubahan status wajib memiliki:
- nama/ID story;
- PIC;
- acceptance criteria yang sudah diuji;
- perintah tes dan hasilnya;
- reviewer atau tautan PR;
- blocker yang tersisa.

**Jangan mengubah status menjadi `DONE` hanya karena kode sudah ditulis.** Jangan mengarang nomor PR, commit hash, hasil tes, atau tanggal penyelesaian.