<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PelangganFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * Akun pelanggan website (ERD: PELANGGAN), terpisah dari akun staf di `users`.
 * Login memakai guard `pelanggan`.
 *
 * @property int $id
 * @property string $nama
 * @property string|null $no_hp
 * @property string|null $email
 * @property string|null $google_id
 * @property string|null $google_avatar
 * @property string|null $password
 * @property string|null $alamat
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Table('pelanggan')]
#[Fillable(['nama', 'no_hp', 'email', 'alamat'])]
#[Hidden(['password', 'remember_token'])]
class Pelanggan extends Authenticatable
{
    /** @use HasFactory<PelangganFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Huruf pertama nama untuk avatar inisial.
     */
    public function avatarInitial(): string
    {
        return Str::upper(Str::substr(trim($this->nama), 0, 1)) ?: '?';
    }

    /**
     * URL foto profil Google, atau null jika login bukan via Google.
     */
    public function googleAvatarUrl(): ?string
    {
        return $this->google_avatar ?: null;
    }
}
