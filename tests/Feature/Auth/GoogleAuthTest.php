<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
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

    public function test_google_callback_creates_new_customer(): void
    {
        $this->mockGoogleUser('google-id-12345', 'budi@gmail.com', 'Budi Santoso');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Berhasil masuk dengan akun Google!');

        $this->assertAuthenticated();

        $user = User::where('google_id', 'google-id-12345')->first();
        $this->assertNotNull($user);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('budi@gmail.com', $user->email);
        $this->assertSame(Role::Pelanggan, $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_google_callback_logs_in_existing_customer(): void
    {
        $customer = User::factory()->pelanggan()->create([
            'google_id' => 'google-id-existing',
            'email' => 'existing@gmail.com',
            'name' => 'Existing Pelanggan',
        ]);

        $this->mockGoogleUser('google-id-existing', 'existing@gmail.com', 'Existing Pelanggan');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_google_callback_rejects_inactive_google_account(): void
    {
        User::factory()->pelanggan()->inactive()->create([
            'google_id' => 'google-id-inactive',
            'email' => 'inactive@gmail.com',
        ]);

        $this->mockGoogleUser('google-id-inactive', 'inactive@gmail.com', 'Inactive User');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone']);

        $this->assertGuest();
    }

    public function test_google_callback_rejects_when_email_already_used_by_staff(): void
    {
        // Staf sudah terdaftar dengan email ini
        $staff = User::factory()->superAdmin()->create([
            'email' => 'admin@aufadepot.com',
            'google_id' => null,
        ]);

        // Akun Google baru mencoba masuk dengan email yang sama
        $this->mockGoogleUser('google-id-diff', $staff->email, 'Impostor User');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone']);

        $this->assertGuest();

        // Pastikan tidak ada akun baru yang dibuat atau akun staf ditimpa
        $this->assertDatabaseMissing('users', ['google_id' => 'google-id-diff']);
    }

    public function test_google_callback_handles_oauth_exception(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andThrow(new Exception('OAuth failed'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors(['phone']);

        $this->assertGuest();
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
