<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_customer_can_register(): void
    {
        $response = $this->post(route('customer.register'), [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $pelanggan = Pelanggan::where('no_hp', '6281234567890')->firstOrFail();
        $this->assertSame('Budi Santoso', $pelanggan->nama);
        $this->assertTrue(Hash::check('password123', (string) $pelanggan->password));
        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_requires_valid_fields(): void
    {
        $response = $this->post(route('customer.register'), [
            'name' => '',
            'phone' => '123',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'phone', 'password']);
        $this->assertGuest('pelanggan');
    }

    public function test_registration_rejects_duplicate_phone_with_prd_message(): void
    {
        Pelanggan::factory()->create(['no_hp' => '6281234567890']);

        $response = $this->post(route('customer.register'), [
            'name' => 'Pengguna Lain',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'phone' => 'Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot.',
        ]);
        $this->assertGuest('pelanggan');
        $this->assertDatabaseCount('pelanggan', 1);
    }
}
