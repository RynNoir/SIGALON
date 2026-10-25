<?php

namespace Tests\Feature\Auth;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffWebAccessTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_UMUM = 'Email atau kata sandi salah.';

    public function test_super_admin_can_log_in(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_cabang_can_log_in(): void
    {
        $user = User::factory()->adminCabang()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_kasir_can_log_in(): void
    {
        $user = User::factory()->kasir()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_kurir_is_redirected_to_access_denied_page(): void
    {
        $user = User::factory()->kurir()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('akses-ditolak'));

        $this->assertGuest();

        $this->get(route('akses-ditolak'))
            ->assertForbidden()
            ->assertSee('Anda tidak memiliki akses ke halaman ini')
            ->assertSee('Kurir menggunakan aplikasi Android SIGALON.');
    }

    public function test_inactive_account_gets_the_generic_message(): void
    {
        $user = User::factory()->adminCabang()->inactive()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->assertGuest();
    }

    public function test_inactive_kurir_does_not_reveal_its_role(): void
    {
        $user = User::factory()->kurir()->inactive()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->assertGuest();
    }

    public function test_admin_cabang_without_cabang_is_rejected(): void
    {
        $user = User::factory()->adminCabang()->create(['cabang_id' => null]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->assertGuest();
    }

    public function test_wrong_password_and_unknown_email_get_the_same_generic_message(): void
    {
        $user = User::factory()->adminCabang()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->post(route('login.store'), ['email' => 'tidak-ada@contoh.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->adminCabang()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee('Terlalu banyak percobaan');

        $this->assertGuest();
    }

    public function test_kasir_without_cabang_is_rejected(): void
    {
        $user = User::factory()->kasir()->create(['cabang_id' => null]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::PESAN_UMUM]);

        $this->assertGuest();
    }

    public function test_kurir_session_is_sent_to_access_denied_page(): void
    {
        $this->actingAs(User::factory()->kurir()->create());

        $this->get(route('dashboard'))->assertRedirect(route('akses-ditolak'));

        $this->assertGuest();
    }

    public function test_account_deactivated_mid_session_is_logged_out(): void
    {
        $user = User::factory()->adminCabang()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_sidebar_shows_own_cabang_and_role_only(): void
    {
        $cabangSendiri = Cabang::factory()->create(['nama' => 'Cabang Uji Satu']);
        Cabang::factory()->create(['nama' => 'Cabang Uji Dua']);
        $user = User::factory()->adminCabang()->for($cabangSendiri)->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cabang Uji Satu')
            ->assertDontSee('Cabang Uji Dua')
            ->assertSee('Admin Cabang');
    }

    public function test_super_admin_sees_all_cabang_label(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Semua cabang')
            ->assertSee('Super Admin');
    }

    public function test_removed_starter_kit_features_are_not_reachable(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/register')->assertNotFound();

        $this->actingAs(User::factory()->adminCabang()->create())
            ->get('/settings/appearance')
            ->assertNotFound();
    }
}
