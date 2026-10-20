<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertAuthenticated();

        $user = User::where('phone', '6281234567890')->first();
        $this->assertNotNull($user);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame(Role::Pelanggan, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->cabang_id);
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
        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_phone(): void
    {
        User::factory()->pelanggan()->create([
            'phone' => '6281234567890',
        ]);

        $response = $this->post(route('customer.register'), [
            'name' => 'Pengguna Lain',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertGuest();
    }
}
