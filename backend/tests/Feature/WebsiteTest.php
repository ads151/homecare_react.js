<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Page;
use App\Services\SiteRenderer;

/** The public website: every page, SEO files, the installer lock and the enquiry form. */
class WebsiteTest extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        auth()->logout(); // visitors are not logged in
    }

    /** A valid spam-check token, as printed in every form (made $age seconds ago). */
    private function token(int $age = 10): string
    {
        SiteRenderer::boot();
        $t = time() - $age;

        return $t.'.'.substr(hash_hmac('sha256', (string) $t, hc_secret()), 0, 24);
    }

    private function enquiry(array $override = []): array
    {
        return array_merge([
            'name' => 'Rahul Sharma',
            'mobile' => '+91 98765-43210',
            'service' => 'Home Nursing',
            'area' => 'Laxmi Nagar',
            'message' => 'Need a nurse from Monday',
            'form_name' => 'Home Page – Hero Form',
            'page_url' => 'http://localhost/',
            'hc_ts' => $this->token(),
            'website' => '',
        ], $override);
    }

    public function test_every_published_page_opens(): void
    {
        foreach (Page::where('is_published', true)->get() as $page) {
            $url = $page->slug === 'home' ? '/' : '/'.$page->slug;
            $this->get($url)->assertOk()->assertSee('<html', false)->assertSee('</html>', false);
        }
    }

    public function test_old_and_duplicate_urls_redirect_to_home(): void
    {
        foreach (['/home', '/index', '/index.php'] as $url) {
            $this->get($url)->assertRedirect('/');
        }
    }

    public function test_unknown_page_is_404_and_not_indexed(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex');
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee('<loc>'.url('about').'</loc>', false)
            ->assertDontSee('thank-you');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: '.url('sitemap.xml'));
    }

    public function test_installer_is_locked_after_installation(): void
    {
        $this->get('/install')->assertNotFound();
        $this->post('/install', [])->assertNotFound();
    }

    public function test_thank_you_page_is_not_indexed_and_greets_the_customer(): void
    {
        $this->get('/thank-you?name=Rahul+Sharma&item=Home+Nursing')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertSee('Rahul');
    }

    public function test_enquiry_form_saves_a_lead_and_goes_to_thank_you(): void
    {
        $this->post('/send', $this->enquiry())->assertRedirect();

        $lead = Lead::firstOrFail();
        $this->assertSame('Rahul Sharma', $lead->name);
        $this->assertSame('9876543210', $lead->mobile); // +91, spaces and dash cleaned
        $this->assertSame('Home Nursing', $lead->item);
        $this->assertSame('new', $lead->status);
        $this->assertSame('Laxmi Nagar', $lead->data['area']['value']);
        $this->assertSame('Need a nurse from Monday', $lead->data['message']['value']);
        $this->assertFalse($lead->mail_sent); // no mail server in tests, but the lead is never lost
        $this->assertNotEmpty($lead->mail_error);
    }

    public function test_enquiry_form_ajax_reply(): void
    {
        $this->post('/send', $this->enquiry(['ajax' => '1']))
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonPath('redirect', fn ($url) => str_contains($url, 'thank-you') && str_contains($url, 'Rahul'));
        $this->assertSame(1, Lead::count());
    }

    public function test_old_send_php_link_still_works(): void
    {
        $this->post('/send.php', $this->enquiry(['ajax' => '1']))->assertJson(['ok' => true]);
        $this->assertSame(1, Lead::count());
    }

    public function test_enquiry_form_checks_the_answers(): void
    {
        $cases = [
            'bad mobile' => [['mobile' => '12345'], '10-digit mobile number'],
            'no name' => [['name' => ''], 'Please fill: Patient / Your Name'],
            'no service' => [['service' => ''], 'Please fill: Service Needed'],
        ];
        foreach ($cases as $label => [$override, $message]) {
            $this->post('/send', $this->enquiry($override + ['ajax' => '1']))
                ->assertJson(['ok' => false])
                ->assertJsonPath('message', fn ($m) => str_contains($m, $message));
        }
        $this->assertSame(0, Lead::count());
    }

    public function test_spam_protection(): void
    {
        // hidden "website" box filled by a bot → pretend OK, save nothing
        $this->post('/send', $this->enquiry(['website' => 'http://spam.example', 'ajax' => '1']))->assertJson(['ok' => true]);
        // no / fake / expired token
        $this->post('/send', $this->enquiry(['hc_ts' => '', 'ajax' => '1']))->assertJson(['ok' => false]);
        $this->post('/send', $this->enquiry(['hc_ts' => time().'.fake', 'ajax' => '1']))->assertJson(['ok' => false]);
        $this->post('/send', $this->enquiry(['hc_ts' => $this->token(3 * 86400), 'ajax' => '1']))->assertJson(['ok' => false]);
        // sent faster than a human can type
        $this->post('/send', $this->enquiry(['hc_ts' => $this->token(0), 'ajax' => '1']))->assertJson(['ok' => false]);

        $this->assertSame(0, Lead::count());
    }

    public function test_form_on_page_has_working_token(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="hc_ts" value="\d+\.[0-9a-f]{24}"/', $html);
        $this->assertStringContainsString('name="website"', $html); // honeypot box present
    }

    public function test_html_in_form_answers_is_removed(): void
    {
        $this->post('/send', $this->enquiry(['name' => '<script>alert(1)</script>Rahul', 'ajax' => '1']))->assertJson(['ok' => true]);
        $this->assertSame('alert(1)Rahul', Lead::firstOrFail()->name);
    }
}
