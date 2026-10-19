# Development Workflow — SIGALON (Tim 5 Orang)

## 1. Struktur peran
- **Product Owner — Fikri Adiyofa:** prioritas backlog, acceptance criteria bisnis, keputusan scope.
- **Scrum Master — Reyhan Dwi Saputra:** fasilitasi Scrum, hambatan, menjaga proses.
- **Developer — Velisa, Shalwa, Nurul, dan anggota tim sesuai pembagian story:** implementasi, tes, dokumentasi, review silang.
- Penanggung jawab utama dari J-01 adalah titik koordinasi, bukan berarti satu orang mengerjakan semua bagian story sendirian.

## 2. Sumber status yang direkomendasikan
Gunakan satu sumber status yang disepakati tim:
1. Issue/Project board GitHub, GitLab, atau papan kerja yang digunakan tim; dan
2. `docs/USER_STORIES_STATUS.md` sebagai ringkasan yang disimpan bersama kode.

Jangan mengelola status terpisah di banyak spreadsheet/chat tanpa sinkronisasi. Jika tim belum memakai issue tracker, mulai dari Markdown + papan sederhana, tetapi tunjuk satu orang yang memperbarui status resmi.

## 3. Alur kerja per story
1. PO memastikan story memenuhi DoR.
2. Story diberi ID stabil, acceptance criteria, PIC, reviewer, dan dependensi.
3. PIC membuat branch dari `main` terbaru, contoh `feature/US-01-staff-auth`. AI boleh membuatkan branch atas perintah PIC, tetapi `git pull` tetap dilakukan PIC.
4. PIC mengerjakan story/task dan menambahkan tes.
5. Developer menjalankan formatter, tes relevan, dan memeriksa diff.
6. PIC membuka PR atau meminta review melalui mekanisme repo.
7. Reviewer memeriksa acceptance criteria, keamanan, desain, dan tes.
8. Jika lolos, merge dilakukan oleh manusia yang berwenang sesuai kebijakan tim.
9. Status diperbarui menjadi `DONE` hanya setelah bukti dan review tersedia.
10. AI membuat ringkasan handoff dan saran pesan commit; AI tidak melakukan commit/push/merge.

## 4. Branch dan perubahan paralel
- `main`: branch stabil.
- Branch fitur: `feature/US-XX-short-name`.
- Perbaikan: `fix/US-XX-short-name`.
- Dokumentasi: `docs/short-name`.
- Satu story dapat dipecah menjadi beberapa task, tetapi tentukan PIC tiap task.
- Hindari dua developer/AI mengedit file yang sama secara bersamaan.
- Sebelum mulai, periksa status Git dan perubahan lokal; jangan menimpa pekerjaan orang lain.
- Merge kecil dan sering setelah review mengurangi konflik.

## 5. Bagaimana AI mengetahui pekerjaan sebelumnya selesai?
AI tidak otomatis tahu kondisi repository atau percakapan tim jika informasinya tidak tersedia. Gunakan bukti yang eksplisit:
- `docs/USER_STORIES_STATUS.md` berisi status dan link PR/commit/test (bila tersedia).
- Setiap story memiliki acceptance criteria yang bisa dicentang.
- PR/issue menyimpan reviewer, hasil tes, dan keputusan.
- Handoff ditambahkan ke `docs/sprints/sprint-1.md` atau issue terkait.
- Minta AI membaca file tersebut pada awal setiap sesi.
- Jangan menandai `DONE` hanya berdasarkan pernyataan “sudah dibuat”; catat tes dan review.

## 6. Status yang disarankan
- `TODO`: belum dimulai.
- `IN_PROGRESS`: sedang dikerjakan.
- `BLOCKED`: menunggu keputusan/dependensi.
- `REVIEW`: siap diperiksa.
- `DONE`: AC lulus, tes relevan lulus, review disetujui.
- `DEFERRED`: sengaja ditunda oleh PO.

## 7. Daily Scrum 15 menit
Setiap anggota menjawab:
1. Apa yang selesai sejak pertemuan terakhir?
2. Apa yang dikerjakan berikutnya?
3. Ada blocker/dependensi apa?
4. Apakah ada risiko terhadap Sprint Goal?

Catat blocker dan pemilik tindak lanjut. Jangan jadikan Daily Scrum sebagai laporan panjang kepada Scrum Master.

## 8. Template handoff
```md
## Handoff — [US-ID / task]
- Tanggal:
- PIC:
- Branch/PR:
- Status: TODO | IN_PROGRESS | BLOCKED | REVIEW | DONE
- Ringkasan:
- Acceptance criteria lulus:
- Tes yang dijalankan dan hasil:
- File yang berubah:
- Keputusan yang dibutuhkan:
- Risiko/bug yang tersisa:
- Langkah berikutnya:
- Reviewer:
- Saran commit (tidak dijalankan):
```

## 9. Kebijakan Git wajib
AI tidak boleh menjalankan `git commit`, `git push`, merge, rebase, reset, stash, atau tindakan Git destruktif tanpa instruksi eksplisit. AI boleh membuat dan berpindah ke branch story (`git switch -c feature/US-XX-...`) setelah memeriksa `git status`. AI tidak boleh menghapus atau me-reset branch. AI hanya memberi saran pesan commit. Setiap developer melakukan commit manual setelah memeriksa diff dan tes.
