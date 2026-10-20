<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_UMUM = 'Nomor HP atau kata sandi salah.';

    private const PESAN_STAF = 'Akun staf tidak dapat masuk di sini. Silakan masuk melalui portal staf di /login.';

    public function test_customer_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertOk()
            ->assertSee('Portal Pelanggan Resmi')
            ->assertSee('Masuk dengan Google')
            ->assertSee('Lupa Kata Sandi? Bantuan Admin')
            ->assertSee('wa.me/6281200000000');
    }

    public function test_authenticated_customer_visiting_login_page_is_redirected_to_home(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
            ->get(route('customer.login'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_can_login_with_valid_phone_and_password(): void
    {
        $customer = User::factory()->pelanggan()->create([
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_can_login_with_different_phone_formats(): void
    {
        $customer = User::factory()->pelanggan()->create([
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        // Format +62
        $this->post(route('customer.login.store'), [
            'phone' => '+6281234567890',
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_wrong_password_gets_generic_error_message(): void
    {
        $customer = User::factory()->pelanggan()->create([
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);
        $this->assertGuest();
    }

    public function test_unknown_phone_gets_same_generic_error_message(): void
    {
        $response = $this->post(route('customer.login.store'), [
            'phone' => '089999999999',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);
        $this->assertGuest();
    }

    public function test_inactive_customer_cannot_log_in(): void
    {
        $customer = User::factory()->pelanggan()->inactive()->create([
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_UMUM]);
        $this->assertGuest();
    }

    public function test_staff_logging_in_via_customer_form_is_rejected(): void
    {
        $staff = User::factory()->adminCabang()->create([
            'phone' => '6281234567890',
            'password' => 'password123',
        ]);

        $response = $this->post(route('customer.login.store'), [
            'phone' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone' => self::PESAN_STAF]);
        $this->assertGuest();
    }

    public function test_customer_login_is_throttled_after_five_failed_attempts(): void
    {
        User::factory()->pelanggan()->create([
            'phone' => '6281234567890',
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

        $this->assertGuest();
    }

    public function test_customer_can_log_out(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
            ->post(route('customer.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
