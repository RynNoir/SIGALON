<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_access_home_page(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee('Keluar');
    }

    public function test_customer_visiting_staff_login_portal_is_redirected_to_home(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_cannot_access_staff_dashboard(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_customer_cannot_access_settings_pages(): void
    {
        $customer = User::factory()->pelanggan()->create();

        $this->actingAs($customer)
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
