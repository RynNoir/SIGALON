<?php

/*
| Terjemahan aturan validasi yang dipakai aplikasi. Aturan lain yang belum ada di sini
| akan jatuh ke bahasa Inggris (fallback_locale); tambahkan bila dibutuhkan story.
*/

return [

    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'lowercase' => ':Attribute harus huruf kecil.',
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',

    'attributes' => [
        'email' => 'email',
        'password' => 'kata sandi',
        'current_password' => 'kata sandi saat ini',
        'name' => 'nama',
    ],

];
