<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Pages\SystemTools;
use App\Filament\Widgets\LatestLeads;
use App\Filament\Widgets\LeadStats;
use App\Models\Page;
use App\Models\Setting;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/** Site Settings (all tabs), System & Database tools, Dashboard widgets. */
class SettingsSystemTest extends AdminTestCase
{
    /* ---------------- Site Settings ---------------- */

    public function test_settings_save_without_any_change(): void
    {
        $before = Setting::allSettings();

        Livewire::test(SiteSettings::class)->call('save')->assertHasNoFormErrors()->assertNotified('Settings saved');

        Setting::flush();
        $after = Setting::allSettings();
        // empty 'options' on non-dropdown form fields is dropped on save (the website treats missing = empty)
        $clean = fn ($fields) => array_map(fn ($f) => array_filter($f, fn ($v, $k) => ! ($k === 'options' && $v === ''), ARRAY_FILTER_USE_BOTH), (array) $fields);
        $before['form_fields'] = $clean($before['form_fields'] ?? []);
        $after['form_fields'] = $clean($after['form_fields'] ?? []);
        foreach (['business_name', 'mobile', 'whatsapp_number', 'email', 'menu', 'form_fields', 'colors', 'floating', 'mail_to'] as $k) {
            $this->assertEquals($before[$k] ?? null, $after[$k] ?? null, "Setting '{$k}' changed although nothing was edited");
        }
    }

    public function test_phone_whatsapp_and_email_change_everywhere_on_the_website(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['mobile' => '90000 11111', 'whatsapp_number' => '919000011111', 'email' => 'care@example.com', 'business_name' => 'Sun Care'])
            ->call('save')
            ->assertHasNoFormErrors();

        foreach (['/', '/about', '/contact', '/home-care-services'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('90000 11111')
                ->assertSee('tel:+919000011111', false)
                ->assertSee('wa.me/919000011111', false)
                ->assertSee('care@example.com');
        }
    }

    public function test_settings_validation(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['business_name' => '', 'whatsapp_number' => '+91 98765', 'email' => 'not-an-email', 'mail_to' => ''])
            ->call('save')
            ->assertHasFormErrors(['business_name' => 'required', 'whatsapp_number', 'email', 'mail_to' => 'required']);
    }

    public function test_every_tab_saves_its_values(): void
    {
        $values = [
            'tagline' => 'Care you can trust',
            'topbar_text' => 'Nurses available today',
            'header_button_text' => 'Book a Nurse Now',
            'menu' => [['label' => 'Home', 'link' => ''], ['label' => 'Blog', 'link' => 'https://example.com/blog']],
            'footer_about' => 'Footer about text',
            'copyright' => '© {year} Sun Care',
            'social.facebook' => 'https://facebook.com/suncare',
            'colors.primary' => '#123456',
            'buttons.main_text' => 'Book Today',
            'popup_heading' => 'Get a call back',
            'cta_heading' => 'Need a nurse today?',
            'floating.animation' => 'bounce',
            'floating.position_side' => 'left',
            'mobile_bar.enabled' => true,
            'privacy_note' => 'We never share your number.',
            'mail_cc' => 'cc@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '465',
            'tracking.head_code_on' => true,
            'tracking.head_code' => '<!-- test-head-code -->',
            'city' => 'Noida',
            'google_site_verification' => 'abc123verification',
            'robots_extra' => 'Disallow: /private',
        ];

        Livewire::test(SiteSettings::class)->fillForm($values)->call('save')->assertHasNoFormErrors();

        Setting::flush();
        foreach ($values as $key => $value) {
            $this->assertEquals($value, data_get(Setting::allSettings(), $key), "Setting '{$key}' was not saved");
        }

        $this->get('/')->assertOk()
            ->assertSee('Care you can trust')->assertSee('Book a Nurse Now')->assertSee('https://example.com/blog', false)
            ->assertSee('#123456', false)->assertSee('<!-- test-head-code -->', false)
            ->assertSee('abc123verification', false)->assertSee('facebook.com/suncare', false)
            ->assertSee('We never share your number.');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /private');
    }

    public function test_smtp_password_is_encrypted_and_empty_field_keeps_it(): void
    {
        Livewire::test(SiteSettings::class)->fillForm(['smtp_password' => 'app-password-1234'])->call('save')->assertHasNoFormErrors();

        $raw = DB::table('settings')->where('key', 'smtp_password')->value('value');
        $this->assertStringNotContainsString('app-password-1234', (string) $raw);
        Setting::flush();
        $this->assertSame('app-password-1234', Setting::get('smtp_password'));

        // the form never shows the password, and saving with the box empty keeps it
        Livewire::test(SiteSettings::class)->assertSchemaStateSet(['smtp_password' => ''])->call('save');
        Setting::flush();
        $this->assertSame('app-password-1234', Setting::get('smtp_password'));
    }

    public function test_enquiry_form_fields_can_be_changed(): void
    {
        $fields = Setting::get('form_fields');
        $fields[] = ['label' => 'Patient age', 'name' => 'patient_age', 'type' => 'text', 'placeholder' => 'e.g. 70', 'required' => false, 'short_form' => true];

        Livewire::test(SiteSettings::class)->fillForm(['form_fields' => $fields])->call('save')->assertHasNoFormErrors();

        $this->get('/contact')->assertOk()->assertSee('Patient age')->assertSee('name="patient_age"', false);

        $bad = $fields;
        $bad[] = ['label' => 'Bad', 'name' => 'has space', 'type' => 'text'];
        Livewire::test(SiteSettings::class)->fillForm(['form_fields' => $bad])->call('save')->assertHasFormErrors();
    }

    public function test_send_test_email_button_never_crashes(): void
    {
        // No real mail server in tests: the button must show a message, not an error page
        Livewire::test(SiteSettings::class)
            ->fillForm(['smtp_on' => true, 'smtp_host' => '127.0.0.1', 'smtp_port' => '1', 'smtp_secure' => 'none'])
            ->callAction(TestAction::make('testEmail')->schemaComponent('smtp-sending-server::data::section'), data: ['to' => 'owner@example.com'])
            ->assertNotified();
    }

    /* ---------------- System & Database ---------------- */

    public function test_system_page_buttons(): void
    {
        $this->assertSame([], SystemTools::pendingMigrations());

        Livewire::test(SystemTools::class)
            ->assertOk()
            ->assertSee('Laravel version')
            ->callAction('migrate')->assertNotified('Database updated')
            ->callAction('cache')->assertNotified('Cache cleared');

        // "Restore missing default content" brings back a deleted page and keeps edited ones
        Page::where('slug', 'about')->delete();
        Page::where('slug', 'contact')->update(['title' => 'Edited contact']);
        Livewire::test(SystemTools::class)->callAction('seed')->assertNotified('Default content checked');
        $this->assertNotNull(Page::where('slug', 'about')->first());
        $this->assertSame('Edited contact', Page::where('slug', 'contact')->value('title'));
    }

    public function test_backup_download_has_all_content(): void
    {
        $this->makeLead(['name' => 'Backup Person']);

        Livewire::test(SystemTools::class)->callAction('backup')->assertFileDownloaded();

        // what is inside the backup file
        $response = (fn () => $this->backup())->call(new SystemTools);
        ob_start();
        $response->sendContent();
        $json = json_decode(ob_get_clean(), true);
        $this->assertCount(Page::count(), $json['pages']);
        $this->assertSame('Backup Person', $json['leads'][0]['name']);
        $this->assertNotEmpty($json['settings']);
        $this->assertNotEmpty($json['services']);
    }

    /* ---------------- Dashboard ---------------- */

    public function test_dashboard_widgets_show_lead_numbers(): void
    {
        $this->makeLead(['name' => 'Widget Person']);
        $this->makeLead(['status' => 'converted']);

        Livewire::test(LeadStats::class)->assertOk()->assertSee('New enquiries');
        Livewire::test(LatestLeads::class)->assertOk()->assertSee('Widget Person');
    }
}
