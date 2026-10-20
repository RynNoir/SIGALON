<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Cabang;
use App\Models\User;
use Database\Seeders\CabangSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_landing_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('SIGALON');
        $response->assertSee('Depot Air Minum CV. Aufa');
    }

    public function test_landing_page_displays_official_prices(): void
    {
        config(['sigalon.harga.walk_in' => 6000]);
        config(['sigalon.harga.cod' => 7000]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Rp6.000');
        $response->assertSee('Rp7.000');
        $response->assertSee('Walk-in');
        $response->assertSee('COD');
    }

    public function test_landing_page_hides_galon_baru_card_when_unconfigured(): void
    {
        config(['sigalon.harga.galon_baru' => null]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('data-test="card-galon-baru"', false);
    }

    public function test_landing_page_shows_galon_baru_card_when_configured(): void
    {
        config(['sigalon.harga.galon_baru' => 45000]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-test="card-galon-baru"', false);
        $response->assertSee('Rp45.000');
        $response->assertSee('Galon Kosong Baru');
    }

    public function test_landing_page_includes_active_branches_in_map_data(): void
    {
        $activeBranch1 = Cabang::factory()->create([
            'nama' => 'Cabang Test A',
            'alamat' => 'Jl. Test No. 1, Padang',
            'latitude' => -0.8715000,
            'longitude' => 100.3640000,
            'is_active' => true,
        ]);

        $activeBranch2 = Cabang::factory()->create([
            'nama' => 'Cabang Test B',
            'alamat' => 'Jl. Test No. 2, Padang',
            'latitude' => -0.8980000,
            'longitude' => 100.3680000,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Cabang Test A');
        $response->assertSee('Jl. Test No. 1, Padang');
        $response->assertSee('Cabang Test B');
        $response->assertSee('Jl. Test No. 2, Padang');
        $response->assertSee('-0.8715');
        $response->assertSee('100.364');
    }

    public function test_landing_page_excludes_inactive_branches(): void
    {
        $inactiveBranch = Cabang::factory()->inactive()->create([
            'nama' => 'Cabang Nonaktif Rahasia',
            'alamat' => 'Jl. Tidak Boleh Muncul, Padang',
            'latitude' => -0.9000000,
            'longitude' => 100.3700000,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee($inactiveBranch->nama);
        $response->assertDontSee($inactiveBranch->alamat);
    }

    public function test_landing_page_excludes_branches_without_coordinates_from_map(): void
    {
        $branchWithoutCoords = Cabang::factory()->create([
            'nama' => 'Cabang Tanpa Koordinat',
            'alamat' => 'Jl. Belum Ada Titik, Padang',
            'latitude' => null,
            'longitude' => null,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Cabang Tanpa Koordinat');
    }

    public function test_landing_page_handles_empty_branches_gracefully(): void
    {
        Cabang::query()->delete();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Belum ada cabang aktif yang terdaftar');
        $response->assertSee('0 Cabang');
    }

    public function test_landing_page_includes_mandatory_openstreetmap_attribution(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('OpenStreetMap');
        $response->assertSee('https://www.openstreetmap.org/copyright', false);
    }

    public function test_landing_page_contains_whatsapp_contact_with_configured_number(): void
    {
        config(['services.admin_whatsapp_number' => '6281234567890']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://wa.me/6281234567890', false);
    }

    public function test_authenticated_customer_can_access_landing_page_and_sees_profile_in_navbar(): void
    {
        $customer = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => Role::Pelanggan,
            'phone' => '081299998888',
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('data-test="customer-logout-button"', false);
        $response->assertDontSee('data-test="customer-login-link"', false);
    }

    public function test_authenticated_staff_sees_staff_dashboard_link_in_navbar(): void
    {
        $cabang = Cabang::factory()->create();
        $staff = User::factory()->create([
            'name' => 'Admin Cabang 1',
            'role' => Role::AdminCabang,
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Dasbor Staf');
        $response->assertSee(route('dashboard'), false);
    }

    public function test_cabang_seeder_is_idempotent_and_seeds_expected_branches(): void
    {
        $this->seed(CabangSeeder::class);

        $this->assertDatabaseCount('cabang', 2);
        $this->assertDatabaseHas('cabang', [
            'nama' => 'Cabang Dadok Tunggul Hitam',
            'alamat' => 'Jl. Utama Dadok Tunggul Hitam, Padang',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('cabang', [
            'nama' => 'Cabang Siteba',
            'alamat' => 'Jl. Siteba, Padang',
            'is_active' => true,
        ]);

        // Re-run to verify idempotency
        $this->seed(CabangSeeder::class);
        $this->assertDatabaseCount('cabang', 2);
    }

    public function test_landing_page_navbar_has_section_anchors_and_initial_active_state_on_beranda(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        // Verifikasi tautan navigasi seksi dengan atribut data-nav-section
        $response->assertSee('data-nav-section="hero"', false);
        $response->assertSee('data-nav-section="harga"', false);
        $response->assertSee('data-nav-section="cara-pesan"', false);
        $response->assertSee('data-nav-section="lokasi-cabang"', false);

        // Verifikasi seksi awal (Beranda) memiliki aria-current="page"
        $response->assertSee('aria-current="page"', false);

        // Verifikasi tombol non-seksi tidak memiliki data-nav-section
        $response->assertDontSee('data-nav-section="whatsapp"', false);
        $response->assertDontSee('data-nav-section="pesan"', false);
        $response->assertDontSee('data-nav-section="login"', false);
    }

    public function test_landing_page_sections_have_scroll_margin_offsets_for_navbar_clearance(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        // Pastikan masing-masing seksi memiliki ID dan class scroll-mt untuk clearance navbar sticky
        $response->assertSee('id="hero"', false);
        $response->assertSee('id="harga"', false);
        $response->assertSee('id="cara-pesan"', false);
        $response->assertSee('id="lokasi-cabang"', false);
        $response->assertSee('scroll-mt-20', false);
    }

    public function test_customer_navbar_component_renders_independently_without_error(): void
    {
        $view = $this->blade('<x-customer-navbar />');

        $view->assertSee('SIGALON');
        $view->assertSee('data-nav-section="hero"', false);
        $view->assertSee('data-nav-section="lokasi-cabang"', false);
    }
}
