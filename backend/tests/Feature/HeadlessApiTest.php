<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Setting;
use App\Services\SiteRenderer;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/** Backend connected to the Next.js website: JSON API, redirects, refresh after saving. */
class HeadlessApiTest extends AdminTestCase
{
    private const FRONT = 'https://front.example.test';

    private const SECRET = 'test-frontend-secret';

    /** false = the Next.js website is down */
    private bool $frontUp = true;

    protected function setUp(): void
    {
        parent::setUp();
        config(['frontend.url' => self::FRONT, 'frontend.secret' => self::SECRET]);
        Setting::putMany(['frontend_url' => self::FRONT]);
        Setting::flush();
        Http::fake([self::FRONT.'/*' => fn () => $this->frontUp ? Http::response(['ok' => true]) : Http::response('down', 500)]);
    }

    private function api(string $method, string $uri, array $data = [], ?string $secret = self::SECRET)
    {
        $headers = ['Accept' => 'application/json'] + ($secret !== null ? ['X-Api-Secret' => $secret] : []);

        return $this->json($method, '/api'.$uri, $data, $headers);
    }

    private function token(int $age = 10): string
    {
        SiteRenderer::boot();
        $t = time() - $age;

        return $t.'.'.substr(hash_hmac('sha256', (string) $t, hc_secret()), 0, 24);
    }

    /* ---------------- security ---------------- */

    public function test_api_needs_the_secret_key(): void
    {
        foreach (['/site', '/pages', '/pages/home'] as $uri) {
            $this->api('GET', $uri, secret: null)->assertUnauthorized();
            $this->api('GET', $uri, secret: 'wrong-key')->assertUnauthorized();
            $this->api('GET', $uri)->assertOk();
        }
        $this->api('POST', '/enquiry', [], secret: null)->assertUnauthorized();

        // no secret configured at all → nobody gets in
        config(['frontend.secret' => '']);
        $this->api('GET', '/site', secret: '')->assertUnauthorized();
    }

    public function test_site_data_never_contains_email_passwords(): void
    {
        Setting::putMany(['smtp_password' => 'super-secret-app-pass', 'smtp_username' => 'me@example.com']);
        Setting::flush();

        $res = $this->api('GET', '/site')->assertOk()
            ->assertJsonStructure(['settings', 'links' => ['tel', 'whatsapp', 'site_url'], 'form_fields', 'services', 'updated_at']);

        $body = $res->getContent();
        foreach (['super-secret-app-pass', 'smtp_password', 'smtp_username', 'mail_to', 'owner@example.test', 'me@example.com'] as $private) {
            $this->assertStringNotContainsString($private, $body, "API leaked '{$private}'");
        }
        $this->assertNotEmpty($res->json('services'));
        $this->assertContains('mobile', array_column($res->json('form_fields'), 'name'));
    }

    /* ---------------- pages ---------------- */

    public function test_page_list_for_sitemap(): void
    {
        $slugs = array_column($this->api('GET', '/pages')->assertOk()->json('pages'), 'slug');

        $this->assertSame('home', $slugs[0]);
        $this->assertContains('about', $slugs);
        $this->assertNotContains('thank-you', $slugs);
    }

    public function test_every_published_page_has_content_and_seo(): void
    {
        foreach (Page::where('is_published', true)->get() as $page) {
            $this->api('GET', '/pages/'.$page->slug)->assertOk()
                ->assertJsonPath('slug', $page->slug)
                ->assertJsonPath('template', $page->template)
                ->assertJsonStructure(['content', 'seo' => ['title', 'description', 'canonical', 'index', 'follow', 'og_title', 'og_image', 'jsonld']]);
        }
        $this->api('GET', '/pages/no-such-page')->assertNotFound();
    }

    public function test_unpublished_page_only_with_preview(): void
    {
        Page::where('slug', 'about')->update(['is_published' => false]);

        $this->api('GET', '/pages/about')->assertNotFound();
        $this->api('GET', '/pages/about?preview=1')->assertOk()->assertJsonPath('slug', 'about');
    }

    public function test_builder_page_content_is_made_safe_for_the_website(): void
    {
        Page::create([
            'title' => 'Test', 'slug' => 'test-builder', 'template' => 'builder', 'is_published' => true,
            'content' => ['blocks' => [
                ['type' => 'rich_text', 'data' => ['show' => true, 'body' => '<p>Hi <a href="javascript:alert(1)" onclick="x()">x</a></p><script>alert(1)</script>']],
                ['type' => 'video', 'data' => ['show' => true, 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
            ]],
        ]);

        $res = $this->api('GET', '/pages/test-builder')->assertOk();
        $body = $res->json('content.blocks.0.data.body');
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertSame('dQw4w9WgXcQ', $res->json('content.blocks.1.data.youtube_id'));
    }

    public function test_page_changes_from_admin_reach_the_api(): void
    {
        $about = Page::where('slug', 'about')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $about->getRouteKey()])
            ->fillForm(['t_about.section1_heading' => 'Our story since 2015', 'seo_title' => 'About Sun Care'])
            ->call('save')->assertHasNoFormErrors();

        $this->api('GET', '/pages/about')->assertOk()
            ->assertJsonPath('content.section1_heading', 'Our story since 2015')
            ->assertJsonPath('seo.title', 'About Sun Care');
    }

    /* ---------------- refresh the website after saving ---------------- */

    public function test_saving_tells_the_website_to_refresh(): void
    {
        Page::where('slug', 'about')->firstOrFail()->update(['title' => 'About us']);
        app()->terminate(); // the refresh is sent after the admin response

        Http::assertSent(fn (Request $r) => $r->url() === self::FRONT.'/api/revalidate'
            && $r->method() === 'POST'
            && $r->header('X-Api-Secret')[0] === self::SECRET);
    }

    public function test_refresh_website_button(): void
    {
        $section = 'connection-to-the-nextjs-website::data::section';

        Livewire::test(SiteSettings::class)
            ->callAction(TestAction::make('refreshSite')->schemaComponent($section))
            ->assertNotified('Website refreshed');

        $this->frontUp = false;
        Livewire::test(SiteSettings::class)
            ->callAction(TestAction::make('refreshSite')->schemaComponent($section))
            ->assertNotified('Could not reach the website');
    }

    /* ---------------- visitors and enquiries ---------------- */

    public function test_visitors_are_sent_to_the_nextjs_website(): void
    {
        auth()->logout();
        $this->get('/')->assertStatus(301)->assertRedirect(self::FRONT.'/');
        $this->get('/about?utm_source=google')->assertStatus(301)->assertRedirect(self::FRONT.'/about?utm_source=google');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /'); // the backend itself stays out of Google
    }

    public function test_enquiry_through_the_api(): void
    {
        $data = [
            'name' => 'Rahul Sharma', 'mobile' => '9876543210', 'service' => 'Home Nursing', 'area' => 'Noida',
            'form_name' => 'Next.js – Hero Form', 'hc_ts' => $this->token(), 'website' => '',
        ];

        $this->api('POST', '/enquiry', $data)->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonPath('redirect', fn ($url) => str_starts_with($url, '/thank-you') && str_contains($url, 'Rahul'));
        $this->assertSame('Rahul Sharma', Lead::firstOrFail()->name);

        $this->api('POST', '/enquiry', ['mobile' => '123'] + $data)->assertOk()->assertJson(['ok' => false]);
        $this->assertSame(1, Lead::count());
    }
}
