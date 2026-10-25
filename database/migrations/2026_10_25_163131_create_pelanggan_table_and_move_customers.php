<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memisahkan akun pelanggan dari tabel staf sesuai ERD v1.1 (tabel PELANGGAN).
 *
 * Pelanggan yang sebelumnya tersimpan di `users` (role = pelanggan) dipindahkan ke `pelanggan`,
 * lalu kolom khusus pelanggan dihapus dari `users`.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_hp', 20)->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('google_id')->nullable()->unique();
            $table->string('google_avatar')->nullable();
            $table->string('password')->nullable();
            $table->text('alamat')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $pelangganIds = [];

        DB::table('users')->where('role', 'pelanggan')->orderBy('id')->chunk(100, function ($users) use (&$pelangganIds) {
            foreach ($users as $user) {
                DB::table('pelanggan')->insert([
                    'nama' => $user->name,
                    'no_hp' => $user->phone,
                    'email' => $user->email,
                    'google_id' => $user->google_id,
                    'google_avatar' => $user->google_avatar,
                    'password' => $user->password,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);

                $pelangganIds[] = $user->id;
            }
        });

        if ($pelangganIds !== []) {
            DB::table('sessions')->whereIn('user_id', $pelangganIds)->delete();
            DB::table('users')->whereIn('id', $pelangganIds)->delete();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['google_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'google_id', 'google_avatar']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('google_id')->nullable()->unique()->after('phone');
            $table->string('google_avatar')->nullable()->after('google_id');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        DB::table('pelanggan')->orderBy('id')->chunk(100, function ($pelanggan) {
            foreach ($pelanggan as $row) {
                DB::table('users')->insert([
                    'name' => $row->nama,
                    'email' => $row->email,
                    'phone' => $row->no_hp,
                    'google_id' => $row->google_id,
                    'google_avatar' => $row->google_avatar,
                    'password' => $row->password,
                    'role' => 'pelanggan',
                    'cabang_id' => null,
                    'is_active' => true,
                    'remember_token' => $row->remember_token,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        });

        Schema::dropIfExists('pelanggan');
    }
};
