<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_UMUM = 'Nomor HP atau kata sandi salah.';

    public function test_customer_login_screen_can_be_rendered(): void
    {
        config(['services.admin_whatsapp_number' => '6281200000000']);

        $response = $this->get(route('customer.login'));

        $response->assertOk()
            ->assertSee('Portal Pelanggan Resmi')
            ->assertSee('Masuk dengan Google')
            ->assertSee('Lupa Kata Sandi? Bantuan Admin')
            ->assertSee('wa.me/6281200000000');
    }

    public function test_authenticated_customer_visiting_login_page_is_redirected_to_home(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('customer.login'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_can_login_with_valid_phone_and_password(): void
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

    public function test_customer_can_login_with_different_phone_formats(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
            'password' => 'password123',
        ]);

        $this->post(route('customer.login.store'), [
            'phone' => '+6281234567890',
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
    }

    public function test_wrong_password_gets_generic_error_message(): void
    {
        Pelanggan::factory()->create([
            'no_hp' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);
        $this->assertGuest('pelanggan');
    }

    public function test_unknown_phone_gets_same_generic_error_message(): void
    {
        $response = $this->post(route('customer.login.store'), [
            'phone' => '089999999999',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);
        $this->assertGuest('pelanggan');
    }

    public function test_google_account_without_password_cannot_log_in_with_phone_form(): void
    {
        Pelanggan::factory()->google()->create(['no_hp' => '6281234567890']);

        $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ])->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);

        $this->assertGuest('pelanggan');
    }

    public function test_staff_account_cannot_log_in_with_customer_form(): void
    {
        $staff = User::factory()->adminCabang()->create();

        $this->post(route('customer.login.store'), [
            'phone' => $staff->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);

        $this->assertGuest('pelanggan');
        $this->assertGuest('web');
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
                'password' => 'passwordsalah',
            ]);
        }

        $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ])->assertStatus(429);

        $this->assertGuest('pelanggan');
    }

    public function test_customer_can_log_out(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->post(route('customer.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest('pelanggan');
    }
}
