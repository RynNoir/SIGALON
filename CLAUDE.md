# CLAUDE.md — Panduan AI Coding SIGALON

Baca file ini sebelum melakukan perubahan kode.

## 1. Sumber kebenaran
Urutan prioritas:
1. Instruksi terbaru yang eksplisit dari pengguna/Product Owner.
2. `docs/PRD.md` untuk kebutuhan produk dan aturan bisnis, dan `docs/ERD_SIGALON.md` untuk model data.
3. `docs/sprints/sprint-<n>.md` (isi J-01 per sprint) untuk scope sprint aktif.
4. `docs/USER_STORIES_STATUS.md` untuk status yang sudah diverifikasi.
5. `docs/DEVELOPMENT_WORKFLOW.md` dan `docs/DESIGN_SYSTEM.md`.
6. Kode yang sudah ada dan tesnya.
7. `docs/design/README.md` dan gambar `docs/design/references/` — referensi visual saja, prioritas terendah untuk urusan fitur.

Jika dua sumber bertentangan, jangan diam-diam memilih. Jelaskan konflik dan minta keputusan atau tandai sebagai keputusan terbuka.

## 2. Scope aktif
- Sprint aktif mengikuti PRD §17. Selesaikan sisa story sprint sebelumnya lebih dulu.
- Jangan mulai story sprint berikutnya tanpa permintaan eksplisit.
- Jangan menganggap story selesai hanya karena kode telah dibuat.
- Jangan mengubah dokumen proyek lama secara otomatis. Ubah dokumentasi repo hanya bila diminta atau bila pembaruan status story memang bagian dari tugas yang disetujui.

## 3. Larangan Git
**Jangan pernah melakukan commit otomatis per fitur atau per user story.**
- Dilarang menjalankan `git commit`, `git push`, merge, rebase, atau `git stash` tanpa instruksi eksplisit dari pengguna.
- AI **boleh** membuat branch baru dan berpindah ke branch story yang sedang dikerjakan (`git switch -c feature/US-XX-nama-singkat`, atau `fix/...` dan `docs/...` sesuai `docs/DEVELOPMENT_WORKFLOW.md`). Syaratnya:
  - Jalankan `git status` lebih dulu.
  - Jika ada perubahan lokal yang bukan milik story ini, laporkan dan tanyakan dulu sebelum pindah branch.
  - Jangan menghapus, me-rename, atau me-reset branch.
- Setelah menyelesaikan pekerjaan, berikan saran pesan commit, misalnya `feat(US-01): add staff role-based login`.
- Pengguna akan memeriksa diff, melakukan commit, dan push secara manual.
- Jangan melakukan reset, checkout yang membuang perubahan, atau menghapus perubahan pengguna.
- Jangan menyimpan secret/API key/password ke repository.

## 4. Cara memulai setiap tugas
1. Baca `docs/sprints/sprint-<n>.md` untuk sprint story tersebut dan `docs/USER_STORIES_STATUS.md`.
2. Identifikasi kode story, tujuan, acceptance criteria, status, pemilik, dan dependensi.
3. Periksa struktur proyek dan status Git sebelum mengubah file. Jika belum berada di branch story, buat atau pindah ke branch tersebut sesuai §3.
4. Buat rencana singkat dan daftar file yang kemungkinan berubah.
5. Implementasikan perubahan terkecil yang memenuhi acceptance criteria.
6. Jalankan tes, lint, formatter, atau build yang relevan dan tersedia.
7. Periksa `git diff` secara konseptual; jangan menghapus perubahan yang bukan milik tugas ini.
8. Laporkan hasil dengan format handoff di bawah.

## 5. Aturan implementasi
- Ikuti pola arsitektur dan konvensi yang sudah digunakan repo; jangan menambahkan framework/library baru tanpa alasan dan persetujuan.
- Validasi input di server, bukan hanya di UI.
- Otorisasi wajib ditegakkan di backend. Menyembunyikan tombol bukan mekanisme keamanan.
- Terapkan pembatasan `cabang_id` pada query dan operasi Admin Cabang/staf.
- Gunakan transaksi database untuk operasi multi-langkah yang harus atomik.
- Jangan membuat data contoh seolah-olah data produksi.
- Jangan mengklaim tes lulus jika belum dijalankan.
- Jika tes tidak bisa dijalankan, jelaskan alasannya.
- Untuk data pelanggan, kumpulkan seminimal mungkin dan jangan bocorkan data ke log.
- Jangan mengimplementasikan peminjaman galon atau WhatsApp API otomatis; fitur tersebut di luar scope terbaru.
- Semua harga ada di satu file konfigurasi `config/sigalon.php` (bukan tabel, bukan menu). Galon kosong baru hanya dijual melalui walk-in dan nominalnya masih nilai sementara. Satu transaksi berisi satu jenis item.
- Akun staf ada di tabel `users` (guard `web`, role `super_admin`/`admin_cabang`/`kasir`/`kurir`). Akun pelanggan ada di tabel `pelanggan` (guard `pelanggan`, model `App\Models\Pelanggan`). Jangan mencampur keduanya.

## 6. Design/UX
Ikuti `docs/DESIGN_SYSTEM.md`: Aqua Professional, responsive, minimalis, state loading/empty/error/success, form jelas, aksesibilitas dan ukuran sentuh yang nyaman.
- Gunakan token warna Tailwind dari `resources/css/app.css` (`primary`, `primary-strong`, `deep`, `ink`, `muted`, `surface`, dan seterusnya); jangan menulis hex langsung di view. Palet mengikuti PRD v1.2 §11; `brand` (`#0891B2`) hanya untuk elemen non-teks.
- Font Plus Jakarta Sans; hanya mode terang; komponen Flux UI versi gratis lebih dulu.
- Sebelum membuat UI, baca `docs/design/README.md` untuk layar yang relevan dengan story (pemetaan di `docs/sprints/sprint-<n>.md`). Ambil pola visualnya saja; jangan menyalin fitur, harga, label, atau data contoh dari gambar yang tidak ada di PRD. Jika gambar bertentangan dengan PRD, ikuti PRD dan laporkan konfliknya.

## 7. Status story dan handoff
Status yang diizinkan: `TODO`, `IN_PROGRESS`, `BLOCKED`, `REVIEW`, `DONE`.
AI boleh menyarankan perubahan status, tetapi jangan menyatakan `DONE` tanpa acceptance criteria teruji dan review. Status resmi hanya diperbarui setelah penanggung jawab/reviewer mengonfirmasi bukti.

Setelah tugas, laporkan:
- Story/task:
- Ringkasan perubahan:
- File yang berubah:
- Acceptance criteria: terpenuhi / belum / tidak diuji
- Tes yang dijalankan dan hasil:
- Risiko, bug, dependensi, atau keputusan terbuka:
- Langkah berikutnya / handoff:
- **Saran pesan commit (tidak dijalankan):**

## 8. Format pesan commit
Gunakan Conventional Commits sebagai saran saja:
- `feat(US-01): add staff role-based login`
- `fix(US-03): correct branch marker coordinates`
- `test(US-01): cover role authorization`
- `docs(US-01): document login acceptance criteria`

Jangan membuat commit. Tunggu pengguna melakukannya secara manual.

===

<laravel-boost-guidelines>
=== .ai/deployments rules ===

# Deployment

- Hosting dan domain produksi SIGALON ditunda (PRD §5, §9). Jangan menyiapkan deployment, layanan cloud, atau layanan berbayar apa pun tanpa persetujuan Product Owner.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
