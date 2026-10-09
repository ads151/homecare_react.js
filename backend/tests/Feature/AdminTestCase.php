<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SiteSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Base for admin + website tests:
 * fresh database, the original website content (SiteSeeder) and a logged-in admin.
 */
abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    private static bool $createdLock = false;

    protected function setUp(): void
    {
        parent::setUp();

        // The site only works after installation (EnsureInstalled middleware)
        $lock = storage_path('app/installed.lock');
        if (! is_file($lock)) {
            @mkdir(dirname($lock), 0775, true);
            file_put_contents($lock, 'tests');
            self::$createdLock = true;
        }

        // No real HTTP calls from tests (e.g. "refresh website" to the Next.js frontend).
        // By default the frontend is NOT connected, so this backend shows the website itself;
        // HeadlessApiTest connects it.
        Http::preventStrayRequests();
        config(['frontend.url' => '', 'frontend.secret' => 'test-frontend-secret']);

        Setting::flush();
        $this->seed(SiteSeeder::class);
        // Tests must never send a real email (the defaults point at the real inbox)
        Setting::putMany(['smtp_on' => false, 'smtp_host' => '', 'smtp_username' => '', 'mail_to' => 'owner@example.test', 'mail_cc' => '', 'autoreply_on' => false, 'frontend_url' => '']);
        Setting::flush();

        $this->admin = User::factory()->create(['name' => 'Test Admin']);
        $this->actingAs($this->admin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$createdLock) {
            @unlink(storage_path('app/installed.lock'));
            self::$createdLock = false;
        }
        parent::tearDownAfterClass();
    }

    /** One enquiry, like the website form saves it. */
    protected function makeLead(array $attrs = []): Lead
    {
        static $n = 0;
        $n++;

        $name = $attrs['name'] ?? "Customer {$n}";
        $mobile = array_key_exists('mobile', $attrs) ? $attrs['mobile'] : '98765432'.str_pad((string) ($n % 100), 2, '0', STR_PAD_LEFT);

        return Lead::query()->forceCreate(array_merge([
            'name' => $name,
            'mobile' => $mobile,
            'email' => "customer{$n}@example.com",
            'item' => 'Home Nursing',
            'form_name' => 'Home Page – Hero Form',
            'page_url' => 'https://example.com/',
            // the website form stores every answer here too (used by the CSV export)
            'data' => [
                'name' => ['label' => 'Patient / Your Name', 'value' => $name],
                'mobile' => ['label' => 'Mobile Number', 'value' => (string) $mobile],
                'area' => ['label' => 'Area / Locality', 'value' => 'Noida'],
            ],
            'ip' => '127.0.0.1',
            'status' => 'new',
            'mail_sent' => false,
        ], $attrs));
    }
}
