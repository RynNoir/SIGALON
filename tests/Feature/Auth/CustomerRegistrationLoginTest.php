<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use App\Services\CustomerPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerRegistrationLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PRD_DUPLICATE_MESSAGE = CustomerPhoneService::DUPLICATE_PHONE_MESSAGE;

    private const GENERIC_LOGIN_ERROR = 'Nomor HP atau kata sandi salah.';

    public function test_customer_can_register_with_name_unique_phone_and_hashed_password(): void
    {
        $response = $this->post(route('customer.register'), [
            'name' => 'Siti Nurhaliza',
            'phone' => '081298765432',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Pendaftaran berhasil. Selamat datang di SIGALON!');

        $pelanggan = Pelanggan::where('no_hp', '6281298765432')->firstOrFail();
        $this->assertSame('Siti Nurhaliza', $pelanggan->nama);
        $this->assertTrue(Hash::check('rahasia123', (string) $pelanggan->password));
        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
        $this->assertGuest('web');
    }

    public function test_customer_registration_normalizes_various_phone_formats(): void
    {
        // Format dengan +62
        $this->post(route('customer.register'), [
            'name' => 'Format Plus',
            'phone' => '+6281211112222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pelanggan', ['no_hp' => '6281211112222']);

        // Logout
        $this->post(route('customer.logout'));

        // Format dengan 8xx (tanpa 0 atau 62)
        $this->post(route('customer.register'), [
            'name' => 'Format Delapan',
            'phone' => '81333334444',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pelanggan', ['no_hp' => '6281333334444']);
    }

    public function test_customer_registration_rejects_duplicate_phone_with_exact_prd_message(): void
    {
        Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
        ]);

        $response = $this->post(route('customer.register'), [
            'name' => 'Pendaftar Duplikat',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'phone' => self::PRD_DUPLICATE_MESSAGE,
        ]);

        $this->assertGuest('pelanggan');
        $this->assertDatabaseCount('pelanggan', 1);
    }

    public function test_customer_registration_validates_required_fields_and_password_confirmation(): void
    {
        $response = $this->post(route('customer.register'), [
            'name' => '',
            'phone' => '12',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'phone', 'password']);
        $this->assertGuest('pelanggan');
    }

    public function test_customer_can_login_with_phone_and_password_without_otp(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
        $this->assertGuest('web');
    }

    public function test_customer_login_fails_with_generic_message_on_wrong_password_or_unregistered_phone(): void
    {
        Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
            'password' => 'password123',
        ]);

        // Password salah
        $responseWrongPass = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password-keliru',
        ]);
        $responseWrongPass->assertSessionHasErrors(['phone' => self::GENERIC_LOGIN_ERROR]);
        $this->assertGuest('pelanggan');

        // Nomor tidak terdaftar
        $responseUnknownPhone = $this->post(route('customer.login.store'), [
            'phone' => '089999999999',
            'password' => 'password123',
        ]);
        $responseUnknownPhone->assertSessionHasErrors(['phone' => self::GENERIC_LOGIN_ERROR]);
        $this->assertGuest('pelanggan');
    }

    public function test_customer_login_is_throttled_after_five_failed_attempts(): void
    {
        Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
            'password' => 'password123',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('customer.login.store'), [
                'phone' => '081234567890',
                'password' => 'salah',
            ]);
        }

        // Percobaan ke-6 harus 429
        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(429);
        $this->assertGuest('pelanggan');
    }

    public function test_staff_account_cannot_login_as_customer(): void
    {
        $staff = User::factory()->kasir()->create();

        $response = $this->post(route('customer.login.store'), [
            'phone' => $staff->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['phone' => self::GENERIC_LOGIN_ERROR]);
        $this->assertGuest('pelanggan');
        $this->assertGuest('web');
    }

    public function test_guest_cannot_access_complete_phone_page(): void
    {
        $this->get(route('customer.phone.edit'))
            ->assertRedirect(route('customer.login'));
    }

    public function test_google_customer_can_view_complete_phone_page(): void
    {
        $googleCustomer = Pelanggan::factory()->google()->create([
            'nama' => 'Pengguna Google',
            'no_hp' => null,
        ]);

        $response = $this->actingAs($googleCustomer, 'pelanggan')
            ->get(route('customer.phone.edit'));

        $response->assertOk()
            ->assertSee('Nomor WhatsApp untuk Pengantaran')
            ->assertSee('Pengguna Google')
            ->assertSee('Simpan Nomor WhatsApp');
    }

    public function test_google_customer_completing_phone_with_already_registered_number_is_rejected(): void
    {
        // Nomor sudah dipakai oleh pelanggan lain
        Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
        ]);

        $googleCustomer = Pelanggan::factory()->google()->create([
            'no_hp' => null,
        ]);

        $response = $this->actingAs($googleCustomer, 'pelanggan')
            ->post(route('customer.phone.update'), [
                'phone' => '081234567890',
            ]);

        $response->assertSessionHasErrors([
            'phone' => self::PRD_DUPLICATE_MESSAGE,
        ]);

        $this->assertNull($googleCustomer->fresh()?->no_hp);
    }

    public function test_google_customer_can_successfully_complete_phone_with_unique_number(): void
    {
        $googleCustomer = Pelanggan::factory()->google()->create([
            'no_hp' => null,
        ]);

        $response = $this->actingAs($googleCustomer, 'pelanggan')
            ->post(route('customer.phone.update'), [
                'phone' => '081299998888',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Nomor WhatsApp berhasil disimpan!');

        $this->assertSame('6281299998888', $googleCustomer->fresh()?->no_hp);
    }

    public function test_google_customer_updating_own_existing_phone_does_not_trigger_duplicate_error(): void
    {
        $customer = Pelanggan::factory()->create([
            'no_hp' => '6281233334444',
        ]);

        // Menyimpan nomor yang sama untuk akun sendiri tidak boleh dianggap duplikat
        $response = $this->actingAs($customer, 'pelanggan')
            ->post(route('customer.phone.update'), [
                'phone' => '081233334444',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertSame('6281233334444', $customer->fresh()?->no_hp);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nomorTidakSah(): array
    {
        return [
            'berisi huruf saja' => ['abcdefghij'],
            'campuran angka dan huruf' => ['0812abc34567'],
            '8 digit' => ['08123456'],
            '8 digit dengan spasi' => ['0812 3456'],
            'lebih dari 13 digit setelah awalan' => ['081234567890123'],
            'bukan awalan Indonesia' => ['12345678901'],
        ];
    }

    #[DataProvider('nomorTidakSah')]
    public function test_registration_rejects_invalid_phone_without_creating_account(string $nomor): void
    {
        $this->post(route('customer.register'), [
            'name' => 'Pendaftar Uji',
            'phone' => $nomor,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors(['phone' => CustomerPhoneService::INVALID_FORMAT_MESSAGE]);

        $this->assertGuest('pelanggan');
        $this->assertDatabaseCount('pelanggan', 0);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function nomorSahDiBatas(): array
    {
        return [
            '9 digit (batas bawah)' => ['081234567', '6281234567'],
            '14 digit (batas atas)' => ['08123456789012', '628123456789012'],
            'dengan spasi dan tanda hubung' => ['+62 812-3456-7890', '6281234567890'],
        ];
    }

    #[DataProvider('nomorSahDiBatas')]
    public function test_registration_accepts_valid_phone_at_the_boundaries(string $nomor, string $tersimpan): void
    {
        $this->post(route('customer.register'), [
            'name' => 'Pendaftar Uji',
            'phone' => $nomor,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pelanggan', ['no_hp' => $tersimpan]);
    }

    public function test_google_customer_cannot_complete_phone_with_invalid_number(): void
    {
        $customer = Pelanggan::factory()->google()->create(['no_hp' => null]);

        $this->actingAs($customer, 'pelanggan')
            ->post(route('customer.phone.update'), ['phone' => 'abcdefghij'])
            ->assertSessionHasErrors(['phone' => CustomerPhoneService::INVALID_FORMAT_MESSAGE]);

        $this->assertNull($customer->fresh()?->no_hp);
    }

    public function test_customer_name_with_html_is_properly_escaped(): void
    {
        $customer = Pelanggan::factory()->create([
            'nama' => '<b>Nama XSS</b>',
            'no_hp' => null,
        ]);

        $response = $this->actingAs($customer, 'pelanggan')
            ->get(route('customer.phone.edit'));

        $response->assertOk()
            ->assertDontSee('<b>Nama XSS</b>', false)
            ->assertSee('&lt;b&gt;Nama XSS&lt;/b&gt;', false);
    }
}
