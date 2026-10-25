<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_initiates_oauth(): void
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', (string) $response->getTargetUrl());
    }

    public function test_google_callback_creates_new_customer_with_empty_phone_and_address(): void
    {
        $this->mockGoogleUser('google-id-12345', 'budi@gmail.com', 'Budi Santoso');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Berhasil masuk dengan akun Google!');

        $pelanggan = Pelanggan::where('google_id', 'google-id-12345')->firstOrFail();
        $this->assertSame('Budi Santoso', $pelanggan->nama);
        $this->assertSame('budi@gmail.com', $pelanggan->email);
        $this->assertNull($pelanggan->no_hp);
        $this->assertNull($pelanggan->alamat);
        $this->assertNull($pelanggan->password);
        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
        $this->assertGuest('web');
    }

    public function test_google_callback_logs_in_existing_customer(): void
    {
        $pelanggan = Pelanggan::factory()->google()->create([
            'google_id' => 'google-id-existing',
            'email' => 'existing@gmail.com',
        ]);

        $this->mockGoogleUser('google-id-existing', 'existing@gmail.com', 'Existing Pelanggan');

        $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($pelanggan, 'pelanggan');
        $this->assertDatabaseCount('pelanggan', 1);
    }

    public function test_google_callback_rejects_email_already_used_by_another_customer(): void
    {
        Pelanggan::factory()->google()->create([
            'google_id' => 'google-id-lama',
            'email' => 'budi@gmail.com',
        ]);

        $this->mockGoogleUser('google-id-baru', 'budi@gmail.com', 'Akun Lain');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone' => 'Email dari akun Google ini sudah terdaftar pada akun pelanggan lain.']);

        $this->assertGuest('pelanggan');
        $this->assertDatabaseMissing('pelanggan', ['google_id' => 'google-id-baru']);
    }

    public function test_staff_email_does_not_block_or_touch_staff_account(): void
    {
        $staff = User::factory()->superAdmin()->create(['email' => 'admin@aufadepot.com']);

        $this->mockGoogleUser('google-id-staf', 'admin@aufadepot.com', 'Admin Pribadi');

        $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

        $this->assertGuest('web');
        $this->assertSame('admin@aufadepot.com', $staff->fresh()?->email);
        $this->assertDatabaseHas('pelanggan', ['google_id' => 'google-id-staf']);
    }

    public function test_invalid_oauth_state_is_rejected_without_stateless_fallback(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        $provider->shouldNotReceive('stateless');
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone' => 'Sesi masuk Google sudah kedaluwarsa. Silakan coba lagi.']);

        $this->assertGuest('pelanggan');
        $this->assertDatabaseCount('pelanggan', 0);
    }

    public function test_oauth_error_details_are_not_shown_to_customer(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andThrow(new Exception('cURL error 60: SSL certificate problem'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone' => 'Gagal masuk dengan akun Google. Silakan coba lagi.']);

        $this->followRedirects($response)->assertDontSee('cURL error 60');
        $this->assertGuest('pelanggan');
    }

    private function mockGoogleUser(string $id, string $email, string $name): void
    {
        $googleUser = new SocialiteUser;
        $googleUser->id = $id;
        $googleUser->email = $email;
        $googleUser->name = $name;

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
