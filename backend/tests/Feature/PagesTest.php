<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/** Pages: create, edit + save every page design, Page Builder blocks, copy, delete, SEO, publish. */
class PagesTest extends AdminTestCase
{
    /** One block of every Page Builder type, with realistic content. */
    public static function allBlocks(): array
    {
        $b = fn (string $type, array $data) => ['type' => $type, 'data' => $data + ['show' => true]];

        return [
            $b('heading_text', ['small' => 'Welcome', 'heading' => 'ICU at Home in [East Delhi]', 'text' => "First paragraph.\n\nSecond paragraph.", 'align' => 'center', 'button_text' => 'Book', 'button_link' => 'popup']),
            $b('rich_text', ['heading' => 'Article', 'body' => '<p>Hello <strong>world</strong></p><ul><li>One</li></ul>']),
            $b('image_text', ['image' => 'images/pages/about.jpg', 'image_alt' => 'Nurse at home', 'image_position' => 'right', 'heading' => 'Why ICU at home', 'text' => 'Text', 'points' => ['One', 'Two'], 'button_text' => 'Call', 'button_link' => 'contact']),
            $b('services', ['heading' => 'Our [Services]', 'filter' => true, 'limit' => 3]),
            $b('stats', ['items' => [['value' => '24x7', 'label' => 'Support'], ['value' => '100%', 'label' => 'Verified']]]),
            $b('icon_boxes', ['heading' => 'Values', 'items' => [['icon' => '🎯', 'title' => 'Mission', 'text' => 'x']]]),
            $b('steps', ['heading' => 'Steps', 'items' => [['icon' => '📞', 'title' => 'Call us', 'text' => 'x']]]),
            $b('why', ['image' => 'images/pages/why.jpg', 'badge_big' => '24x7', 'badge_text' => 'care', 'heading' => 'Why us', 'items' => [['icon' => '🛡️', 'title' => 'Safe', 'text' => 'x']]]),
            $b('photo_cards', ['heading' => 'Cards', 'items' => [['image' => 'images/services/elder-care.jpg', 'title' => 'Elder', 'text' => 'x', 'link' => 'popup']]]),
            $b('faq', ['heading' => 'FAQ', 'items' => [['question' => 'Is ICU at home safe?', 'answer' => 'Yes, with trained staff.']]]),
            $b('testimonials', ['heading' => 'Reviews', 'items' => [['name' => 'Ravi Kumar', 'area' => 'Delhi', 'text' => 'Great service']]]),
            $b('areas', ['heading' => 'Areas', 'items' => ['Noida', 'Laxmi Nagar']]),
            $b('cta', ['heading' => '', 'text' => '']),
            $b('form', ['heading' => 'Talk to us', 'form_heading' => 'Request', 'show_contact' => true]),
            $b('contact', ['heading' => 'Contact', 'map_embed' => '<iframe src="https://www.google.com/maps?q=Delhi&output=embed"></iframe>', 'form_show' => true, 'map_show' => true]),
            $b('map', ['map_embed' => 'https://www.google.com/maps?q=Delhi&output=embed']),
            $b('custom_box', ['heading' => 'Need something custom?', 'text' => 'Tell us', 'button_text' => 'Enquire', 'button_link' => 'popup', 'enquiry_item' => 'Custom']),
            $b('gallery', ['heading' => 'Gallery', 'images' => [['image' => 'images/pages/why.jpg', 'caption' => 'Nurse', 'alt' => 'nurse']]]),
            $b('video', ['heading' => 'Video', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']),
            $b('html', ['code' => '<div class="center">Custom HTML here</div>', 'wrap' => true]),
        ];
    }

    private function builderPage(array $attrs = []): Page
    {
        return Page::query()->create(array_merge([
            'title' => 'ICU at Home East Delhi',
            'slug' => 'icu-at-home-east-delhi',
            'template' => 'builder',
            'content' => ['hero_title' => 'ICU at Home in East Delhi', 'hero_text' => 'x', 'blocks' => static::allBlocks()],
            'is_published' => true,
            'focus_keyword' => 'ICU at home',
        ], $attrs));
    }

    public function test_list_shows_all_pages_and_template_filter_works(): void
    {
        Livewire::test(ListPages::class)->assertCanSeeTableRecords(Page::all());
        Livewire::test(ListPages::class)->filterTable('template', 'legal')
            ->assertCanSeeTableRecords(Page::where('template', 'legal')->get())
            ->assertCanNotSeeTableRecords(Page::where('template', '!=', 'legal')->get());
    }

    public function test_create_page_builder_page_from_admin(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Elder Care in Noida',
                'slug' => 'elder-care-in-noida',
                'template' => 'builder',
                'is_published' => true,
                'content' => [
                    'hero_title' => 'Elder Care in [Noida]',
                    'blocks' => [
                        ['type' => 'heading_text', 'data' => ['heading' => 'Trusted elder care', 'text' => 'Caring staff', 'show' => true]],
                        ['type' => 'faq', 'data' => ['heading' => 'FAQ', 'items' => [['question' => 'Do you work at night?', 'answer' => 'Yes.']], 'show' => true]],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::where('slug', 'elder-care-in-noida')->firstOrFail();
        $this->assertFalse($page->is_system);
        $this->assertCount(2, $page->content['blocks']);
        $this->assertNotNull($page->seo_score);

        $this->get('/elder-care-in-noida')->assertOk()->assertSee('Trusted elder care')->assertSee('Do you work at night?');
    }

    public function test_slug_rules(): void
    {
        foreach (['Bad Slug', 'UPPER', 'end-', 'about'] as $bad) { // 'about' already exists
            Livewire::test(CreatePage::class)
                ->fillForm(['title' => 'X', 'slug' => $bad, 'template' => 'builder'])
                ->call('create')
                ->assertHasFormErrors(['slug']);
        }
    }

    public function test_every_page_builder_block_renders_on_the_website(): void
    {
        $this->builderPage();

        $this->get('/icu-at-home-east-delhi')->assertOk()
            ->assertSee('ICU at Home in', false)
            ->assertSee('Why ICU at home')->assertSee('Is ICU at home safe?')->assertSee('Ravi Kumar')
            ->assertSee('Laxmi Nagar')->assertSee('Need something custom?')->assertSee('Custom HTML here', false)
            ->assertSee('youtube', false)->assertSee('FAQPage', false);
    }

    public function test_builder_page_with_every_block_opens_and_saves_unchanged(): void
    {
        $page = $this->builderPage();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $blocks = $page->fresh()->content['blocks'];
        $this->assertCount(20, $blocks);
        $this->assertSame(array_column(static::allBlocks(), 'type'), array_column($blocks, 'type'));
        $this->get('/icu-at-home-east-delhi')->assertOk()->assertSee('Is ICU at home safe?');
    }

    public function test_every_existing_page_saves_without_errors_and_keeps_its_content(): void
    {
        foreach (Page::all() as $page) {
            $before = $page->content;

            Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
                ->call('save')
                ->assertHasNoFormErrors();

            $after = $page->fresh()->content;
            foreach (['hero_title', 'section1_heading', 'section1_items', 'heading_name', 'section2_heading'] as $key) {
                if (array_key_exists($key, $before)) {
                    $this->assertEquals($before[$key], $after[$key] ?? null, "{$page->slug}: '{$key}' changed after saving");
                }
            }
            // The text editor may tidy the HTML (<b> → <strong>), but the words must stay the same
            if (array_key_exists('body', $before)) {
                $words = fn ($html) => trim(preg_replace('/\s+/u', ' ', html_entity_decode(preg_replace('/<[^>]+>/', ' ', (string) $html))));
                $this->assertSame($words($before['body']), $words($after['body'] ?? ''), "{$page->slug}: text of 'body' changed after saving");
            }
            $url = $page->slug === 'home' ? '/' : '/'.$page->slug;
            $this->get($url)->assertOk();
        }
    }

    public function test_editing_home_page_changes_the_website(): void
    {
        $home = Page::where('slug', 'home')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $home->getRouteKey()])
            ->fillForm(['t_home.hero_title' => 'Best Nurses in [Preet Vihar]'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Best Nurses in [Preet Vihar]', $home->fresh()->content['hero_title']);
        $this->get('/')->assertOk()->assertSee('Best Nurses in', false)->assertSee('Preet Vihar');
    }

    public function test_editing_about_page_changes_the_website(): void
    {
        $about = Page::where('slug', 'about')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $about->getRouteKey()])
            ->fillForm(['t_about.section1_heading' => 'Our story since 2015'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Our story since 2015', $about->fresh()->content['section1_heading']);
        $this->get('/about')->assertOk()->assertSee('Our story since 2015');
    }

    public function test_seo_fields_save_and_update_the_score(): void
    {
        $page = $this->builderPage(['seo_score' => null]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'focus_keyword' => 'ICU at home',
                'seo_title' => 'ICU at Home in East Delhi | 24x7 ICU Nurse & Setup',
                'seo_description' => 'ICU at home in East Delhi with ICU-trained nurses, oxygen, monitors and ventilator support. Same-day setup. Call now for a free consultation.',
                'canonical_url' => 'https://example.com/icu-at-home-east-delhi',
                'schema_type' => 'Service',
            ])
            ->assertSee('SEO score')
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();
        $this->assertSame('ICU at Home in East Delhi | 24x7 ICU Nurse & Setup', $page->seo_title);
        $this->assertNotNull($page->seo_score);
        $this->get('/icu-at-home-east-delhi')->assertOk()
            ->assertSee('<title>ICU at Home in East Delhi | 24x7 ICU Nurse &amp; Setup</title>', false)
            ->assertSee('rel="canonical" href="https://example.com/icu-at-home-east-delhi"', false);
    }

    public function test_noindex_page_is_hidden_from_google(): void
    {
        $page = $this->builderPage(['robots_index' => false]);

        $this->get('/icu-at-home-east-delhi')->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('icu-at-home-east-delhi');
    }

    public function test_unpublished_page_is_404_for_visitors_but_admin_can_preview(): void
    {
        $this->builderPage(['is_published' => false]);

        $this->get('/icu-at-home-east-delhi?preview=1')->assertOk();
        auth()->logout();
        $this->get('/icu-at-home-east-delhi')->assertNotFound();
        $this->get('/icu-at-home-east-delhi?preview=1')->assertNotFound();
    }

    public function test_copy_button_makes_an_unpublished_copy(): void
    {
        $page = $this->builderPage();

        Livewire::test(ListPages::class)->callAction(TestAction::make('replicate')->table($page));

        $copy = Page::where('title', 'ICU at Home East Delhi (copy)')->firstOrFail();
        $this->assertSame('icu-at-home-east-delhi-copy', $copy->slug);
        $this->assertFalse($copy->is_published);
        $this->assertFalse($copy->is_system);
        $this->assertEquals($page->content, $copy->content);
    }

    public function test_delete_page_but_never_home_or_thank_you(): void
    {
        $page = $this->builderPage();
        $home = Page::where('slug', 'home')->firstOrFail();
        $thanks = Page::where('slug', 'thank-you')->firstOrFail();

        Livewire::test(ListPages::class)
            ->assertActionHidden(TestAction::make('delete')->table($home))
            ->assertActionHidden(TestAction::make('delete')->table($thanks))
            ->callAction(TestAction::make('delete')->table($page));
        $this->assertModelMissing($page);

        Livewire::test(EditPage::class, ['record' => $home->getRouteKey()])->assertActionHidden('delete');

        // bulk delete skips system pages
        Livewire::test(ListPages::class)->callTableBulkAction('delete', [$home, $thanks, Page::where('slug', 'about')->first()]);
        $this->assertModelExists($home);
        $this->assertModelExists($thanks);
        $this->assertNull(Page::where('slug', 'about')->first());
    }

    public function test_home_slug_and_system_template_cannot_be_changed(): void
    {
        $home = Page::where('slug', 'home')->firstOrFail();

        Livewire::test(EditPage::class, ['record' => $home->getRouteKey()])
            ->assertFormFieldDisabled('slug')
            ->assertFormFieldDisabled('template')
            ->fillForm(['slug' => 'hacked', 'template' => 'builder'])
            ->call('save');

        $home->refresh();
        $this->assertSame('home', $home->slug);
        $this->assertSame('home', $home->template);
    }
}
