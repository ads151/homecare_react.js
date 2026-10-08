<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\SiteFields as F;
use App\Models\Page;
use App\Services\SeoAnalyzer;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('page')->persistTabInQueryString()->columnSpanFull()->tabs([
                Tab::make('Content')->icon(Heroicon::OutlinedPencilSquare)->schema(static::contentTab()),
                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema(static::seoTab())
                    ->badge(fn (?Page $record) => $record?->seo_score !== null ? $record->seo_score.'/100' : null)
                    ->badgeColor(fn (?Page $record) => SeoAnalyzer::color((int) $record?->seo_score)),
                Tab::make('Advanced')->icon(Heroicon::OutlinedCog6Tooth)->schema([
                    Textarea::make('content.head_code')->label('Extra code in <head> for this page only')->rows(6)
                        ->helperText('Example: a page-specific Google Ads tag or schema JSON-LD. Leave empty if not needed.'),
                ]),
            ]),
        ]);
    }

    /* ------------------------------------------------------------------ */
    protected static function contentTab(): array
    {
        $is = fn (string ...$templates) => fn (Get $get) => in_array($get('template'), $templates, true);

        return [
            Section::make('Page')->columns(2)->schema([
                TextInput::make('title')->label('Page name')->required()->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set, string $operation) {
                        if ($operation === 'create' && blank($get('slug_touched'))) {
                            $set('slug', PageResource::uniqueSlug(Str::slug((string) $state)));
                        }
                    }),
                TextInput::make('slug')->label('Page URL')->required()->maxLength(120)
                    ->prefix(fn () => rtrim(url('/'), '/').'/')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->validationMessages(['regex' => 'Use small letters, numbers and dashes only, e.g. home-nursing-in-delhi'])
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Page $record) => $record?->slug === 'home' || $record?->slug === 'thank-you')
                    ->dehydrated(fn (?Page $record) => ! in_array($record?->slug, ['home', 'thank-you'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set) => $set('slug_touched', '1'))
                    ->helperText('Short, with your keyword. Example: icu-at-home-east-delhi'),
                Select::make('template')->label('Design')->options(Page::TEMPLATES)->default('builder')->required()
                    ->live()->selectablePlaceholder(false)
                    ->disabled(fn (?Page $record) => (bool) $record?->is_system)
                    ->dehydrated(fn (?Page $record) => ! $record?->is_system)
                    ->helperText('"Page Builder" lets you add any sections. The other designs are the original page layouts.'),
                Toggle::make('is_published')->label('Published (visible on website)')->default(true)->inline(false)
                    ->disabled(fn (?Page $record) => $record?->slug === 'home'),
            ]),

            // Inner-page banner (all designs except Home)
            Section::make('Top banner')->description('Big photo with the page heading (H1).')->collapsible()
                ->visible(fn (Get $get) => $get('template') !== 'home')
                ->statePath('content')->columns(2)->schema([
                    Toggle::make('hero_show')->label('Show top banner')->default(true)->inline(false)
                        ->visible(fn (Get $get) => $get('../template') === 'builder'),
                    TextInput::make('page_name')->label('Name in breadcrumb (Home › …)')->maxLength(100),
                    F::heading('hero_title', 'Main heading (H1)')->live(onBlur: true),
                    Textarea::make('hero_text')->label('Line under heading')->rows(2),
                    F::image('hero_image', 'banners', 'Banner photo – computer (1600 × 600)'),
                    F::image('hero_image_mobile', 'banners', 'Banner photo – mobile (optional, 800 × 1000)'),
                    F::alt('hero_image_alt'),
                ]),

            // ---------------- HOME ----------------
            Group::make()->statePath(static::templateKey('home'))->visible($is('home'))->schema(static::homeSections()),

            // ---------------- ABOUT ----------------
            Group::make()->statePath(static::templateKey('about'))->visible($is('about'))->schema([
                Section::make('Section 1 – Photo + text')->collapsible()->columns(2)->schema([
                    F::show('section1_show'),
                    F::image('section1_image', 'pages', 'Photo (900 × 700)'),
                    F::alt('section1_image_alt'),
                    ...F::head('section1_'),
                    F::points('section1_items')->columnSpanFull(),
                    TextInput::make('section1_button_text')->label('Button text'),
                    F::link('section1_button_link'),
                ]),
                Section::make('Section 2 – Numbers strip')->collapsible()->collapsed()->schema([F::show('section2_show'), F::stats('section2_items')]),
                Section::make('Section 3 – Three boxes')->collapsible()->collapsed()->columns(2)->schema([
                    F::show('section3_show'), ...F::head('section3_'), F::iconItems('section3_items', 'Boxes')->columnSpanFull(),
                ]),
                Section::make('Section 4 – Photo cards')->collapsible()->collapsed()->columns(2)->schema([
                    F::show('section4_show'), ...F::head('section4_'), F::photoCards('section4_items')->columnSpanFull(),
                ]),
            ]),

            // ---------------- CONTACT ----------------
            Group::make()->statePath(static::templateKey('contact'))->visible($is('contact'))->schema([
                Section::make('Contact section')->description('Phone, WhatsApp, email and address come from Site Settings.')->columns(2)->schema([
                    ...F::head('section1_'),
                    TextInput::make('form_heading')->default('Send Us a Message'),
                    TextInput::make('form_text'),
                    Toggle::make('map_show')->label('Show Google map')->default(true)->inline(false),
                    Textarea::make('map_embed')->label('Google Map embed link')->rows(2)->columnSpanFull()
                        ->helperText('Google Maps → Share → Embed a map → paste the code or the link.'),
                ]),
            ]),

            // ---------------- ALL SERVICES ----------------
            Group::make()->statePath(static::templateKey('listing'))->visible($is('listing'))->schema([
                Section::make('Heading above cards')->description('The cards come from the Services menu.')->columns(2)->schema([
                    ...F::head('section1_'),
                    Toggle::make('filter_show')->label('Show filter buttons (All / categories)')->default(true)->inline(false),
                    TextInput::make('category_filter')->label('Show only this category (optional)'),
                ]),
                Section::make('Box under the cards')->collapsible()->columns(2)->schema([
                    F::show('section2_show'),
                    TextInput::make('section2_heading')->label('Heading'),
                    Textarea::make('section2_text')->label('Text')->rows(2)->columnSpanFull(),
                    TextInput::make('section2_button_text')->label('Button text'),
                    F::link('section2_button_link'),
                    TextInput::make('section2_item')->label('Enquiry for (shown in popup & email)'),
                ]),
            ]),

            // ---------------- LONG TEXT ----------------
            Group::make()->statePath(static::templateKey('legal'))->visible($is('legal'))->schema([
                Section::make('Page text')->schema([
                    TextInput::make('last_updated')->label('"Last updated" date text')->placeholder('6 October 2026'),
                    RichEditor::make('body')->label('Text')
                        ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['table', 'attachFiles'], ['undo', 'redo']])
                        ->fileAttachmentsDisk('site')->fileAttachmentsDirectory('uploads/content')->fileAttachmentsVisibility('public')
                        ->helperText('You can use {phone}, {email}, {address}, {business}.'),
                ]),
            ]),

            // ---------------- THANK YOU ----------------
            Group::make()->statePath(static::templateKey('thank-you'))->visible($is('thank-you'))->schema([
                Section::make('Thank-you message')->description('{name} = customer first name · {item} = service enquired. Buttons are in Site Settings → Thank You.')->columns(2)->schema([
                    TextInput::make('heading_name')->label('Heading when name is known')->default('Thank You, {name}!'),
                    TextInput::make('heading_general')->label('Heading when name is not known')->default('Thank You!'),
                    Textarea::make('message_item')->label('Message when service is known')->rows(2),
                    Textarea::make('message_general')->label('Message when service is not known')->rows(2),
                    TextInput::make('small_line')->label('Small line under message')->columnSpanFull(),
                ]),
            ]),

            // ---------------- PAGE BUILDER ----------------
            Section::make('Page sections')->description('Add, move, copy or remove sections. Every section uses the website design.')
                ->visible($is('builder'))->schema([F::builder('content.blocks')]),

            Section::make('Extra sections (optional)')->description('Add more sections at the end of this page (before the footer).')
                ->visible(fn (Get $get) => $get('template') !== 'builder')->collapsible()->collapsed()
                ->schema([F::builder('content.extra_blocks', 'Extra sections')]),

            Section::make('Bottom call-to-action banner')->collapsible()->collapsed()
                ->visible($is('builder', 'about', 'contact', 'listing', 'legal'))
                ->statePath('content')->columns(3)->schema([
                    Toggle::make('cta_show')->label('Show banner')->default(true)->inline(false),
                    TextInput::make('cta_heading')->label('Heading')->placeholder('Empty = from Site Settings'),
                    TextInput::make('cta_text')->label('Text')->placeholder('Empty = from Site Settings'),
                ]),
        ];
    }

    protected static function homeSections(): array
    {
        return [
            Section::make('Banner (top of home page)')->collapsible()->columns(2)->schema([
                F::image('hero_image', 'banners', 'Banner photo – computer (1600 × 900)'),
                F::image('hero_image_mobile', 'banners', 'Banner photo – mobile (800 × 1000)'),
                F::alt('hero_image_alt'),
                TextInput::make('hero_badge')->label('Small label above heading'),
                F::heading('hero_title', 'Main heading (H1)')->live(onBlur: true)->columnSpanFull(),
                Textarea::make('hero_text')->label('Paragraph')->rows(3)->columnSpanFull()->live(onBlur: true),
                F::points('hero_points')->columnSpanFull(),
                TextInput::make('hero_call_text')->label('Call button text')->helperText('{phone} = your number'),
                TextInput::make('hero_trust_line')->label('Line with stars (empty = hide)'),
                Toggle::make('hero_form_show')->label('Show form on banner')->default(true)->inline(false),
                TextInput::make('hero_form_heading')->label('Form heading'),
                F::heading('hero_form_text', 'Text under form heading'),
            ]),
            Section::make('Numbers strip')->collapsible()->collapsed()->schema([F::show('section1_show'), F::stats('section1_items')]),
            Section::make('Service cards')->description('Cards come from the Services menu.')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section2_show'), ...F::head('section2_'),
                Toggle::make('section2_filter')->label('Filter buttons')->inline(false),
                TextInput::make('section2_limit')->label('How many cards (0 = all)')->numeric()->default(0),
                TextInput::make('section2_button_text')->label('Button under cards (optional)'),
                F::link('section2_button_link', 'Button link', 'home-care-services'),
            ]),
            Section::make('How it works')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section3_show'), ...F::head('section3_'), F::iconItems('section3_items', 'Steps')->columnSpanFull(),
            ]),
            Section::make('Why choose us')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section4_show'),
                F::image('section4_image', 'pages', 'Photo (800 × 860)'),
                F::alt('section4_image_alt'),
                TextInput::make('section4_badge_big')->label('Big text on photo box'),
                TextInput::make('section4_badge_text')->label('Small text on photo box'),
                ...F::head('section4_'),
                F::iconItems('section4_items', 'Points')->columnSpanFull(),
                TextInput::make('section4_button_text')->label('Button text'),
                F::link('section4_button_link'),
            ]),
            Section::make('Middle banner')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section5_show'),
                TextInput::make('section5_heading')->label('Heading'),
                TextInput::make('section5_text')->label('Text')->columnSpanFull(),
            ]),
            Section::make('Areas we serve')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section6_show'), ...F::head('section6_'), F::points('section6_areas', 'Areas')->addActionLabel('Add area')->columnSpanFull(),
            ]),
            Section::make('Testimonials')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section7_show'), ...F::head('section7_'), F::testimonials('section7_items')->columnSpanFull(),
            ]),
            Section::make('FAQ')->description('Also shown in Google as FAQ rich results.')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section8_show'), ...F::head('section8_'), F::faq('section8_items')->columnSpanFull(),
            ]),
            Section::make('Bottom form (dark section)')->collapsible()->collapsed()->columns(2)->schema([
                F::show('section9_show'), ...F::head('section9_'),
                TextInput::make('section9_form_heading')->label('Form heading'),
                TextInput::make('section9_form_text')->label('Text under form heading'),
            ]),
        ];
    }

    /* ------------------------------------------------------------------ */
    protected static function seoTab(): array
    {
        return [
            Grid::make(['default' => 1, 'lg' => 5])->schema([
                Group::make()->columnSpan(['lg' => 3])->schema([
                    Section::make('Search engine (Google)')->schema([
                        TextInput::make('focus_keyword')->label('Focus keyword')->maxLength(120)->live(onBlur: true)
                            ->helperText('The main phrase people search on Google, e.g. "home nursing in East Delhi". Add more with commas — the first one is checked.'),
                        TextInput::make('seo_title')->label('SEO title')->maxLength(120)->live(onBlur: true)
                            ->hint(fn (?string $state) => mb_strlen((string) $state).' / 60')
                            ->helperText('Shown as the blue link in Google. 30–60 characters. Empty = page heading + business name.'),
                        Textarea::make('seo_description')->label('Meta description')->rows(3)->maxLength(320)->live(onBlur: true)
                            ->hint(fn (?string $state) => mb_strlen((string) $state).' / 160')
                            ->helperText('The 2 lines under the title in Google. 120–160 characters. Use the keyword and a call to action.'),
                    ]),
                    Section::make('Indexing')->columns(3)->schema([
                        Toggle::make('robots_index')->label('Show in Google (index)')->default(true)->inline(false)->live(),
                        Toggle::make('robots_follow')->label('Follow links (follow)')->default(true)->inline(false),
                        Toggle::make('in_sitemap')->label('Include in sitemap.xml')->default(true)->inline(false),
                        TextInput::make('canonical_url')->label('Canonical URL (optional)')->url()->maxLength(255)->columnSpanFull()
                            ->helperText('Only if this page is a copy of another page. Empty = this page itself.'),
                        Select::make('schema_type')->label('Schema type (structured data)')->columnSpanFull()
                            ->options([
                                'WebPage' => 'Web page (normal)',
                                'AboutPage' => 'About page',
                                'ContactPage' => 'Contact page',
                                'Service' => 'Service page',
                                'Article' => 'Article / Blog',
                                'MedicalBusiness' => 'Medical business (local business)',
                                'HomeHealthCareService' => 'Home health care service (local business)',
                                'LocalBusiness' => 'Local business',
                                'none' => 'None',
                            ])->placeholder('Automatic'),
                    ]),
                    Section::make('Social sharing (WhatsApp / Facebook)')->collapsible()->collapsed()->columns(2)->schema([
                        TextInput::make('og_title')->label('Share title')->maxLength(150)->placeholder('Empty = SEO title'),
                        Textarea::make('og_description')->label('Share description')->rows(2)->maxLength(300)->placeholder('Empty = meta description'),
                        F::image('og_image', 'social', 'Share image (1200 × 630)')->columnSpanFull(),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('SEO score')
                        ->headerActions([
                            Action::make('recheck')->label('Re-check')->icon(Heroicon::OutlinedArrowPath)->link()->action(fn () => null),
                        ])
                        ->schema([
                            View::make('filament.seo-panel')->viewData(fn ($livewire, ?Page $record) => [
                                'result' => static::liveAnalysis($livewire, $record),
                            ]),
                        ]),
                ]),
            ]),
        ];
    }

    /**
     * Each page design keeps its fields in its own form key (t_home, t_about…),
     * because different designs reuse names like "section1_items" for
     * different kinds of lists. They are merged back into "content" on save.
     */
    public const TEMPLATE_KEYS = ['home', 'about', 'contact', 'listing', 'legal', 'thank-you'];

    public static function templateKey(string $template): string
    {
        return 't_'.str_replace('-', '', $template);
    }

    /** Record data → form data. */
    public static function splitContent(array $data): array
    {
        $content = static::withDefaults(is_array($data['content'] ?? null) ? $data['content'] : []);
        $data['content'] = $content;
        foreach (static::TEMPLATE_KEYS as $t) {
            $data[static::templateKey($t)] = $content;
        }

        return $data;
    }

    /** Form data → record data. */
    public static function mergeContent(array $data, ?string $template): array
    {
        $content = is_array($data['content'] ?? null) ? $data['content'] : [];
        $key = static::templateKey((string) $template);
        if (in_array($template, static::TEMPLATE_KEYS, true) && is_array($data[$key] ?? null)) {
            $content = array_replace($content, $data[$key]);
        }
        foreach (static::TEMPLATE_KEYS as $t) {
            unset($data[static::templateKey($t)]);
        }
        $data['content'] = $content;

        return $data;
    }

    /**
     * Toggles that are ON when the value is missing in older content
     * (a Toggle would otherwise show OFF and hide the section on save).
     */
    public static function withDefaults(array $content): array
    {
        foreach (['hero_show', 'cta_show', 'map_show', 'filter_show', 'hero_form_show',
            'section1_show', 'section2_show', 'section3_show', 'section4_show', 'section5_show',
            'section6_show', 'section7_show', 'section8_show', 'section9_show'] as $key) {
            $content[$key] ??= true;
        }
        foreach (['blocks', 'extra_blocks'] as $list) {
            if (! isset($content[$list]) || ! is_array($content[$list])) {
                continue;
            }
            foreach ($content[$list] as $i => $block) {
                if (! is_array($block)) {
                    continue;
                }
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];
                foreach (['show', 'show_contact', 'form_show', 'map_show', 'wrap'] as $key) {
                    $data[$key] ??= true;
                }
                $content[$list][$i]['data'] = $data;
            }
        }

        return $content;
    }

    /** SEO analysis of the CURRENT (unsaved) form values. */
    public static function liveAnalysis($livewire, ?Page $record): array
    {
        try {
            $state = $livewire->getSchema('form')->getStateSnapshot();
        } catch (\Throwable) {
            $state = [];
        }
        $state = static::normalize($state);
        $state = static::mergeContent($state, $state['template'] ?? $record?->template);

        $page = new Page;
        $page->forceFill([
            'title' => $state['title'] ?? $record?->title ?? 'Page',
            'slug' => $state['slug'] ?? $record?->slug ?? 'page',
            'template' => $state['template'] ?? $record?->template ?? 'builder',
            'content' => $state['content'] ?? $record?->content ?? [],
        ]);
        foreach (\App\Services\SiteRenderer::SEO_KEYS as $k) {
            $page->{$k} = array_key_exists($k, $state) ? $state[$k] : $record?->{$k};
        }

        return SeoAnalyzer::analyze($page);
    }

    /** Turns upload-field states ([uuid => 'path']) into plain values. */
    protected static function normalize(mixed $v): mixed
    {
        if (is_object($v)) {
            return '';
        }
        if (! is_array($v)) {
            return $v;
        }
        if (count($v) === 1) {
            $key = array_key_first($v);
            $val = $v[$key];
            if (is_string($key) && Str::isUuid($key) && (is_string($val) || is_object($val))) {
                return is_object($val) ? '' : $val;
            }
        }
        if ($v === []) {
            return $v;
        }

        return array_map(fn ($x) => static::normalize($x), $v);
    }
}
