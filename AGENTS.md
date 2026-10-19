# AGENTS.md — Petunjuk untuk AI coding agents

Instruksi ini berlaku untuk AI coding tools yang membaca `AGENTS.md`. Untuk Claude Code, baca juga `CLAUDE.md`.

- Mulai dari `CLAUDE.md`, `docs/PRD.md`, `docs/sprints/sprint-1.md`, `docs/USER_STORIES_STATUS.md`, dan `docs/DESIGN_SYSTEM.md`.
- Scope aktif hanya Sprint 1 kecuali pengguna meminta lain.
- Jangan commit, push, merge, rebase, reset, atau stash tanpa instruksi eksplisit. Berikan saran commit saja.
- Boleh membuat dan berpindah ke branch story (`feature/US-XX-nama-singkat`) setelah memeriksa `git status`. Jangan menghapus atau me-reset branch. Jika ada perubahan lokal yang bukan milik story, tanyakan dulu.
- Jangan menandai story DONE tanpa bukti acceptance criteria, tes, dan review.
- Jangan mengubah dokumen lama di luar repo atau mengklaim telah memperbaruinya.
- Jangan mengarang hasil tes, status, PR, commit hash, data cabang, harga galon kosong, atau konfigurasi.
- Periksa status Git dan perubahan lokal sebelum mengedit. Jangan menimpa pekerjaan orang lain.
- Implementasikan otorisasi server-side, validasi, isolasi cabang, dan perlindungan data pelanggan.
- Gunakan pola kode dan dependensi yang sudah ada; minta persetujuan sebelum menambah dependensi besar.
- Untuk UI: ikuti `docs/DESIGN_SYSTEM.md` (token warna Tailwind, Plus Jakarta Sans, mode terang) dan baca `docs/design/README.md` sebelum membuka gambar di `docs/design/references/`. Gambar hanya referensi gaya visual; jangan menyalin fitur, harga, atau data contoh yang tidak ada di PRD. Jika bertentangan, PRD yang berlaku.
- Selesaikan tugas dengan laporan handoff dan saran pesan commit yang tidak dijalankan.
