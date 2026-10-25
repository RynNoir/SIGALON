# User Story Status — SIGALON Sprint 1

**Terakhir diperbarui:** 19 Oktober 2026  
**Penting:** Status di bawah adalah status awal dokumentasi, bukan hasil pemeriksaan repository. Semua story berstatus konfirmasi/TODO sampai tim memvalidasi.

| ID | Ringkasan | PIC J-01 | Status | Tes/bukti | Blocker/keputusan |
|---|---|---|---|---|---|
| US-01 | Login staf web dengan role | Reyhan | REVIEW | `composer test`: Pint lulus, PHPStan 0 error, PHPUnit 50/50 (19 Okt 2026). Tes: `tests/Feature/Auth/StaffWebAccessTest.php`, `tests/Unit/RoleTest.php`. Smoke test HTTP lulus | Reviewer belum ditunjuk (DoD no. 8). Cek visual di browser belum dilakukan. Branch `feature/US-01-login-staf-web-with-role` |
| US-02 | Login pelanggan | Velisa | TODO — konfirmasi | Belum dicatat | Google + nomor HP/password; cara mengaitkan akun |
| US-03 | Landing page dan peta cabang | Shalwa | TODO — konfirmasi | Belum dicatat | Koordinat/nama/alamat cabang |
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