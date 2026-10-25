<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_access_home_page(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('home'))
            ->assertOk()
            ->assertSee($pelanggan->nama)
            ->assertSee('Keluar');
    }

    public function test_customer_session_is_not_a_staff_session(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('home'))
            ->assertOk();

        $this->assertGuest('web');
    }

    public function test_customer_visiting_staff_login_portal_is_redirected_to_home(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_gets_access_denied_page_on_staff_dashboard(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('dashboard'))
            ->assertForbidden()
            ->assertSee('Anda tidak memiliki akses ke halaman ini');
    }

    public function test_customer_cannot_access_settings_pages(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get('/settings/profile')
            ->assertForbidden();
    }

    public function test_staff_login_still_requires_email_and_password(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }
}
