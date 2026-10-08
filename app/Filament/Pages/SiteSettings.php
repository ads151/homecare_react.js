<?php

namespace App\Filament\Pages;

use App\Filament\Support\SiteFields as F;
use App\Models\Page as SitePage;
use App\Models\Setting;
use App\Services\SiteMailer;
use App\Support\Frontend;
use App\Support\Installer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $all = Setting::allSettings();
        $all['smtp_password'] = '';
        $this->form->fill($all);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make('settings')->persistTabInQueryString()->vertical()->tabs([
                $this->businessTab(),
                $this->contactTab(),
                $this->headerTab(),
                $this->footerTab(),
                $this->designTab(),
                $this->popupTab(),
                $this->floatingTab(),
                $this->formTab(),
                $this->emailTab(),
                $this->trackingTab(),
                $this->seoTab(),
                $this->frontendTab(),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::putMany($data);
        $this->data['smtp_password'] = '';

        Notification::make()->title('Settings saved')->body('The website is updated everywhere.')->success()->send();
    }

    /* ---------------------------------------------------------------- */
    protected function pageLinks(): array
    {
        try {
            return SitePage::query()->orderBy('title')->pluck('slug')->map(fn ($s) => $s === 'home' ? '' : $s)->push('popup')->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function links(string $name, string $label): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema([
                TextInput::make('label')->label('Text')->required()->maxLength(60),
                TextInput::make('link')->label('Link')->datalist($this->pageLinks())->maxLength(255)
                    ->helperText('Page slug (empty = Home), or full https:// link'),
            ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add link')
            ->itemLabel(fn (array $state) => $state['label'] ?? null)->collapsible();
    }

    protected function businessTab(): Tab
    {
        return Tab::make('Business')->icon(Heroicon::OutlinedBuildingStorefront)->schema([
            Section::make('Business details')->columns(2)->schema([
                TextInput::make('business_name')->label('Business name')->required()->maxLength(100),
                TextInput::make('tagline')->label('Tagline (under the name in header)')->maxLength(120),
                Toggle::make('show_name_with_logo')->label('Show business name next to the logo')->inline(false),
                TextInput::make('website_url')->label('Website address (optional)')->url()->placeholder('https://www.yourdomain.com')
                    ->helperText('Leave empty to detect automatically.'),
            ]),
            Section::make('Logo & icons')->columns(3)->schema([
                F::image('logo', 'general', 'Logo (square PNG, 240 × 240)'),
                F::image('favicon', 'general', 'Browser tab icon (favicon, 64 × 64 PNG)'),
                F::image('apple_touch_icon', 'general', 'Phone home-screen icon (180 × 180)'),
            ]),
        ]);
    }

    protected function contactTab(): Tab
    {
        return Tab::make('Phone, WhatsApp & Email')->icon(Heroicon::OutlinedPhone)->schema([
            Section::make('Contact details')->description('Change once — updates the header, footer, contact page and every Call / WhatsApp button.')->columns(2)->schema([
                TextInput::make('mobile')->label('Mobile number (as shown on site)')->required()->maxLength(30)->placeholder('98716 86357'),
                TextInput::make('whatsapp_number')->label('WhatsApp number (with country code)')->required()->maxLength(20)
                    ->placeholder('919871686357')->helperText('91 + 10-digit number. No + or spaces.')
                    ->regex('/^[0-9]{10,15}$/'),
                TextInput::make('email')->label('Email shown on site')->email()->required()->maxLength(150),
                TextInput::make('working_hours')->label('Working hours')->maxLength(100),
                Textarea::make('address')->label('Address')->rows(2)->columnSpanFull(),
            ]),
            Section::make('WhatsApp ready messages')->description('Text already typed when the customer opens WhatsApp.')->schema([
                Textarea::make('whatsapp_message')->label('General WhatsApp buttons')->rows(2),
                Textarea::make('whatsapp_card_message')->label('Service card buttons')->rows(2)->helperText('{item} = service name'),
            ]),
        ]);
    }

    protected function headerTab(): Tab
    {
        return Tab::make('Header & Menu')->icon(Heroicon::OutlinedBars3)->schema([
            Section::make('Top bar')->columns(2)->schema([
                Toggle::make('topbar_show')->label('Show thin top bar')->inline(false),
                TextInput::make('topbar_text')->label('Top bar text'),
            ]),
            Section::make('Header')->columns(2)->schema([
                Toggle::make('header_phone_show')->label('Show phone number')->inline(false),
                TextInput::make('header_phone_label')->label('Small text above phone'),
                Toggle::make('header_button_show')->label('Show header button')->inline(false),
                TextInput::make('header_button_text')->label('Header button text (opens popup)'),
            ]),
            Section::make('Main menu')->schema([$this->links('menu', 'Menu links')]),
        ]);
    }

    protected function footerTab(): Tab
    {
        return Tab::make('Footer')->icon(Heroicon::OutlinedQueueList)->schema([
            Section::make('About text')->schema([
                Textarea::make('footer_about')->label('Text under the logo')->rows(3),
                TextInput::make('copyright')->label('Copyright line')->helperText('{year} = current year'),
            ]),
            Section::make('Link columns')->columns(2)->schema([
                TextInput::make('footer_quick_title')->label('Column 1 title'),
                TextInput::make('footer_info_title')->label('Column 2 title'),
                $this->links('footer_quick_links', 'Column 1 links'),
                $this->links('footer_info_links', 'Column 2 links'),
                TextInput::make('footer_contact_title')->label('Contact column title'),
            ]),
            Section::make('Social media')->description('Paste the full link. Empty = icon hidden.')->columns(2)->schema([
                TextInput::make('social.facebook')->label('Facebook')->url(),
                TextInput::make('social.instagram')->label('Instagram')->url(),
                TextInput::make('social.youtube')->label('YouTube')->url(),
                TextInput::make('social.linkedin')->label('LinkedIn')->url(),
                TextInput::make('social.x')->label('X (Twitter)')->url(),
            ]),
        ]);
    }

    protected function designTab(): Tab
    {
        $btn = fn (string $key, string $label, bool $text = true) => Grid::make(3)->schema(array_values(array_filter([
            $text ? TextInput::make("buttons.{$key}_text")->label($label.' – text') : null,
            ColorPicker::make("buttons.{$key}_bg")->label('Background'),
            ColorPicker::make("buttons.{$key}_color")->label('Text colour'),
        ])));

        return Tab::make('Colours & Buttons')->icon(Heroicon::OutlinedSwatch)->schema([
            Section::make('Brand colours')->description('The whole design follows these colours.')->columns(5)->schema([
                ColorPicker::make('colors.primary')->label('Primary (blue)'),
                ColorPicker::make('colors.secondary')->label('Secondary (green)'),
                ColorPicker::make('colors.dark')->label('Dark (navy)'),
                ColorPicker::make('colors.accent')->label('Accent'),
                ColorPicker::make('colors.light')->label('Light background'),
            ]),
            Section::make('Buttons')->schema([
                $btn('main', 'Book Now button'),
                $btn('call', 'Call button'),
                $btn('whatsapp', 'WhatsApp button'),
                $btn('submit', 'Form submit button'),
                $btn('quote', 'Banner quote button'),
                Grid::make(3)->schema([
                    TextInput::make('buttons.sending_text')->label('Text while form is sending'),
                    Toggle::make('buttons.card_show_call')->label('Round Call button on cards')->inline(false),
                    Toggle::make('buttons.card_show_whatsapp')->label('Round WhatsApp button on cards')->inline(false),
                ]),
            ]),
        ]);
    }

    protected function popupTab(): Tab
    {
        return Tab::make('Popup, Banner & Thank You')->icon(Heroicon::OutlinedWindow)->schema([
            Section::make('Enquiry popup')->description('Opens from every Book Now / Get Free Quote button.')->columns(2)->schema([
                TextInput::make('popup_heading')->label('Heading (no service selected)'),
                TextInput::make('popup_item_prefix')->label('Word before service name')->helperText('"Book" → "Book Home Nursing"'),
                TextInput::make('popup_subtext')->label('Small text')->columnSpanFull(),
            ]),
            Section::make('Call-to-action banner (end of inner pages)')->columns(2)->schema([
                TextInput::make('cta_heading')->label('Heading'),
                TextInput::make('cta_text')->label('Text'),
                F::image('cta_image', 'banners', 'Background photo (empty = plain colour)'),
            ]),
            Section::make('Thank-you page buttons')->columns(4)->schema([
                TextInput::make('thankyou.call_text')->label('Call button text'),
                ColorPicker::make('thankyou.call_bg')->label('Call button colour'),
                ColorPicker::make('thankyou.call_color')->label('Call button text colour'),
                TextInput::make('thankyou.home_text')->label('Home button text'),
            ]),
        ]);
    }

    protected function floatingTab(): Tab
    {
        return Tab::make('Floating Buttons')->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)->schema([
            Section::make('Floating Call & WhatsApp buttons')->columns(3)->schema([
                Toggle::make('floating.floating_enabled')->label('Show floating buttons')->inline(false),
                Toggle::make('floating.call_enabled')->label('Call button')->inline(false),
                Toggle::make('floating.whatsapp_enabled')->label('WhatsApp button')->inline(false),
                Select::make('floating.position_side')->label('Side')->options(['right' => 'Right', 'left' => 'Left'])->selectablePlaceholder(false),
                Select::make('floating.layout')->label('Layout')->options(['vertical' => 'One above the other', 'horizontal' => 'Side by side'])->selectablePlaceholder(false),
                Select::make('floating.animation')->label('Animation')->options(['none' => 'None', 'pulse' => 'Pulse', 'blink' => 'Blink', 'shake' => 'Shake', 'bounce' => 'Bounce', 'ring' => 'Ring'])->selectablePlaceholder(false),
                Select::make('floating.animation_speed')->label('Animation speed')->options(['slow' => 'Slow', 'normal' => 'Normal', 'fast' => 'Fast'])->selectablePlaceholder(false),
                TextInput::make('floating.position_x')->label('Distance from side (px)')->numeric(),
                TextInput::make('floating.position_y')->label('Distance from bottom (px)')->numeric(),
                TextInput::make('floating.gap')->label('Gap between buttons (px)')->numeric(),
                TextInput::make('floating.size')->label('Size on computer (px)')->numeric(),
                TextInput::make('floating.mobile_size')->label('Size on mobile (px)')->numeric(),
                TextInput::make('floating.icon_size')->label('Icon size (px)')->numeric(),
                ColorPicker::make('floating.call_color')->label('Call colour'),
                ColorPicker::make('floating.whatsapp_color')->label('WhatsApp colour'),
                Toggle::make('floating.show_on_mobile')->label('Show on mobile')->inline(false),
                Toggle::make('floating.show_on_desktop')->label('Show on computer')->inline(false),
                Toggle::make('floating.tooltip_enabled')->label('Label on mouse-over')->inline(false),
                TextInput::make('floating.call_tooltip_text')->label('Call label'),
                TextInput::make('floating.whatsapp_tooltip_text')->label('WhatsApp label'),
            ]),
            Section::make('Mobile bottom bar (Call | WhatsApp | Book)')->description('Best for Google Ads. When ON, floating buttons hide on mobile.')->columns(4)->schema([
                Toggle::make('mobile_bar.enabled')->label('Show bar')->inline(false),
                TextInput::make('mobile_bar.call_text')->label('Call text'),
                TextInput::make('mobile_bar.whatsapp_text')->label('WhatsApp text'),
                TextInput::make('mobile_bar.main_text')->label('Book button text'),
            ]),
        ]);
    }

    protected function formTab(): Tab
    {
        return Tab::make('Enquiry Form')->icon(Heroicon::OutlinedClipboardDocumentList)->schema([
            Section::make('Form fields (used by ALL forms)')->schema([
                Repeater::make('form_fields')->label('Fields')
                    ->schema([
                        TextInput::make('label')->label('Label (shown above box)')->required(),
                        TextInput::make('name')->label('Code (no spaces)')->required()->regex('/^[a-z0-9_]+$/i')
                            ->helperText('Keep "name" and "mobile" as they are.'),
                        Select::make('type')->options([
                            'text' => 'Text', 'tel' => 'Mobile number', 'email' => 'Email', 'date' => 'Date',
                            'select' => 'Dropdown', 'textarea' => 'Big text box',
                        ])->default('text')->required()->live(),
                        TextInput::make('placeholder')->label('Hint inside box'),
                        Textarea::make('options')->label('Dropdown options')->rows(3)
                            ->helperText('Write "services" for all service cards, or one option per line.')
                            ->visible(fn ($get) => $get('type') === 'select'),
                        Toggle::make('required')->label('Must fill')->inline(false),
                        Toggle::make('short_form')->label('Show in short forms (banner & popup)')->default(true)->inline(false),
                    ])->columns(3)->reorderable()->addActionLabel('Add field')
                    ->itemLabel(fn (array $state) => ($state['label'] ?? '').(! empty($state['required']) ? ' *' : ''))->collapsible()->collapsed(),
                TextInput::make('privacy_note')->label('Small line under every form'),
            ]),
        ]);
    }

    protected function emailTab(): Tab
    {
        return Tab::make('Email / SMTP')->icon(Heroicon::OutlinedEnvelope)->schema([
            Section::make('Where enquiries are sent')->columns(2)->schema([
                TextInput::make('mail_to')->label('Send enquiries to')->required()->helperText('More than one? Separate with commas.'),
                TextInput::make('mail_cc')->label('Copy (CC) to (optional)'),
                TextInput::make('buttons.email_action_word')->label('Email subject word')->helperText('"New Booking Enquiry – Home Nursing – Rahul"'),
            ]),
            Section::make('SMTP (sending server)')
                ->description('Gmail: smtp.gmail.com · port 465 · SSL · password = 16-letter App Password (Google Account → Security → 2-Step Verification → App passwords). Hostinger email: smtp.hostinger.com · port 465 · SSL · normal email password.')
                ->columns(3)
                ->headerActions([
                    Action::make('testEmail')->label('Send test email')->icon(Heroicon::OutlinedPaperAirplane)
                        ->schema([TextInput::make('to')->label('Send test to')->email()->required()->default(fn () => $this->data['mail_to'] ?? auth()->user()?->email)])
                        ->action(fn (array $data) => $this->sendTest($data['to'])),
                ])
                ->schema([
                    Toggle::make('smtp_on')->label('Use SMTP (recommended)')->inline(false)->columnSpanFull(),
                    TextInput::make('smtp_host')->label('SMTP host')->placeholder('smtp.hostinger.com'),
                    TextInput::make('smtp_port')->label('Port')->numeric()->placeholder('465'),
                    Select::make('smtp_secure')->label('Security')->options(['ssl' => 'SSL (port 465)', 'tls' => 'TLS (port 587)', 'none' => 'None'])->selectablePlaceholder(false),
                    TextInput::make('smtp_username')->label('Username (full email)'),
                    TextInput::make('smtp_password')->label('Password')->password()->revealable()
                        ->placeholder(fn () => Setting::get('smtp_password') ? '•••••••• saved — leave empty to keep' : 'Not set yet')
                        ->helperText('Stored encrypted.'),
                    TextInput::make('from_email')->label('From email (optional)')->email()->helperText('Empty = SMTP username'),
                    TextInput::make('from_name')->label('From name'),
                ]),
            Section::make('Auto-reply to customer')->description('Sent only if the customer typed an email. {name} {item} {phone} work here.')->collapsible()->collapsed()->schema([
                Toggle::make('autoreply_on')->label('Send auto-reply'),
                TextInput::make('autoreply_subject')->label('Subject'),
                Textarea::make('autoreply_message')->label('Message')->rows(6),
            ]),
        ]);
    }

    protected function trackingTab(): Tab
    {
        $box = fn (string $key, string $label, string $help) => Section::make($label)->description($help)->collapsible()->schema([
            Toggle::make("tracking.{$key}_code_on")->label('ON'),
            Textarea::make("tracking.{$key}_code")->hiddenLabel()->rows(6)->extraInputAttributes(['style' => 'font-family:monospace;font-size:12px']),
        ]);

        return Tab::make('Tracking Codes')->icon(Heroicon::OutlinedChartBar)->schema([
            Text::make('Paste codes exactly as given by Google / Facebook. Every Call and WhatsApp click also sends "call_click" / "whatsapp_click" events to Google Tag Manager (dataLayer).'),
            $box('head', '1. Head code (every page, inside <head>)', 'Google Tag Manager <head> code, GA4 gtag.js, Meta Pixel.'),
            $box('bodystart', '2. Body start code (right after <body>)', 'Google Tag Manager <noscript> code.'),
            $box('bodyend', '3. Body end code (before </body>)', 'Chat widgets, other scripts.'),
            $box('thankyou', '4. Thank-you page only', 'Google Ads conversion event, Meta Pixel "Lead" event.'),
        ]);
    }

    protected function frontendTab(): Tab
    {
        return Tab::make('Website (Next.js)')->icon(Heroicon::OutlinedGlobeAlt)->schema([
            Section::make('Connection to the Next.js website')
                ->description('The visitors see the Next.js website. This admin sends it the content and tells it to refresh after every save.')
                ->headerActions([
                    Action::make('refreshSite')->label('Refresh website now')->icon(Heroicon::OutlinedArrowPath)
                        ->action(function () {
                            $r = Frontend::refresh();
                            Notification::make()->title($r['ok'] ? 'Website refreshed' : 'Could not reach the website')
                                ->body($r['message'])->{$r['ok'] ? 'success' : 'danger'}()->send();
                        }),
                ])
                ->schema([
                    TextInput::make('frontend_url')->label('Website (Next.js) address')->url()->placeholder('https://www.yourdomain.com')
                        ->helperText('Where the Next.js website runs. Empty = this Laravel app shows the website itself.'),
                    Text::make(fn () => 'Backend (API) address for the Next.js .env → BACKEND_URL='.rtrim(url('/'), '/')),
                    Text::make(fn () => Frontend::secret() !== ''
                        ? 'Secret key for the Next.js .env → API_SECRET='.Frontend::secret()
                        : 'No secret key yet — click "Create secret key" below.'),
                    Actions::make([
                        Action::make('newSecret')->label(fn () => Frontend::secret() !== '' ? 'Create NEW secret key' : 'Create secret key')
                            ->icon(Heroicon::OutlinedKey)->color('gray')
                            ->requiresConfirmation()
                            ->modalDescription('After this, put the new key in the Next.js website settings (API_SECRET) and restart it, otherwise the website cannot load content.')
                            ->action(function () {
                                $key = bin2hex(random_bytes(24));
                                Installer::writeEnv(['FRONTEND_SECRET' => $key]);
                                config(['frontend.secret' => $key]);
                                Notification::make()->title('New secret key created')->body('Copy it into the Next.js website: API_SECRET='.$key)->success()->persistent()->send();
                            }),
                    ]),
                ]),
        ]);
    }
    protected function seoTab(): Tab
    {
        return Tab::make('SEO & Google Business')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
            Section::make('Local business details (shown to Google)')->columns(3)->schema([
                Select::make('schema_type')->label('Business type')->options([
                    'MedicalBusiness' => 'Medical business',
                    'HomeHealthCareService' => 'Home health care service',
                    'LocalBusiness' => 'Local business',
                    'Organization' => 'Organization',
                ])->selectablePlaceholder(false),
                TextInput::make('city')->label('City / Area'),
                TextInput::make('state')->label('State'),
                TextInput::make('pincode')->label('PIN code'),
                TextInput::make('schema_opening_hours')->label('Opening hours (Google format)')->helperText('e.g. Mo-Su 00:00-23:59 or Mo-Sa 09:00-19:00'),
                TextInput::make('price_range')->label('Price range')->placeholder('₹₹'),
            ]),
            Section::make('Search console & sharing')->columns(2)->schema([
                TextInput::make('google_site_verification')->label('Google Search Console verification code')->helperText('Only the content="…" value of the meta tag.'),
                TextInput::make('bing_site_verification')->label('Bing verification code'),
                F::image('default_og_image', 'social', 'Default share image (1200 × 630)'),
                Textarea::make('robots_extra')->label('Extra robots.txt lines (advanced)')->rows(4),
            ]),
            Text::make(fn () => 'Sitemap: '.url('sitemap.xml').'  ·  Robots: '.url('robots.txt').'  — submit the sitemap in Google Search Console.'),
        ]);
    }

    protected function sendTest(string $to): void
    {
        $state = array_replace(Setting::allSettings(), $this->form->getRawState());
        if (blank($state['smtp_password'] ?? null)) {
            $state['smtp_password'] = Setting::get('smtp_password');
        }
        try {
            $m = SiteMailer::make($state);
            $m->addAddress($to);
            $m->Subject = 'Test email from '.($state['business_name'] ?? 'your website');
            $m->Body = 'If you can read this, your email (SMTP) settings are working. Enquiries from the website will arrive here.';
            $m->send();
            Notification::make()->title('Test email sent to '.$to)->body('Check the inbox (and spam folder). Remember to SAVE the settings.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Email could not be sent')->body($e->getMessage())->danger()->persistent()->send();
        }
    }
}
