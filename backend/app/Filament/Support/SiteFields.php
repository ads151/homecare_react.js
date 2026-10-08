<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/** Re-usable admin form fields for the website. */
class SiteFields
{
    public const HEADING_HELP = 'Words inside [square brackets] show in green colour.';

    public const LINK_HELP = 'popup = opens enquiry form · page-slug (e.g. contact) = opens that page · #section = scroll · full https:// link · empty = no button';

    public const TEXT_HELP = 'Empty line = new paragraph. You can use {phone}, {email}, {address}, {business}.';

    /** Image upload saved into public/uploads/<dir> with an SEO-friendly file name. */
    public static function image(string $name, string $dir = 'general', ?string $label = null): FileUpload
    {
        return FileUpload::make($name)
            ->label($label ?? Str::headline(Str::afterLast($name, '.')))
            ->disk('site')
            ->directory('uploads/'.$dir)
            ->visibility('public')
            ->image()
            ->imageEditor()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon'])
            ->maxSize(5120)
            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => static::fileName($file))
            ->downloadable()
            ->openable();
    }

    public static function fileName(TemporaryUploadedFile $file): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';

        return Str::limit($base, 60, '').'-'.Str::lower(Str::random(5)).'.'.strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
    }

    public static function alt(string $name = 'image_alt'): TextInput
    {
        return TextInput::make($name)->label('Image ALT text (for Google)')->maxLength(150)
            ->helperText('Describe the photo in a few words, ideally with your keyword.');
    }

    public static function heading(string $name = 'heading', string $label = 'Heading'): TextInput
    {
        return TextInput::make($name)->label($label)->helperText(self::HEADING_HELP)->maxLength(200);
    }

    public static function link(string $name, string $label = 'Button link', string $default = 'popup'): TextInput
    {
        return TextInput::make($name)->label($label)->default($default)->helperText(self::LINK_HELP)->maxLength(300);
    }

    /** small / heading / text group for a section. */
    public static function head(string $prefix = ''): array
    {
        return [
            TextInput::make($prefix.'small')->label('Small text above heading')->maxLength(120),
            static::heading($prefix.'heading'),
            Textarea::make($prefix.'text')->label('Paragraph')->rows(3)->helperText(self::TEXT_HELP)->columnSpanFull(),
        ];
    }

    public static function show(string $name = 'show', string $label = 'Show this section'): Toggle
    {
        return Toggle::make($name)->label($label)->default(true)->inline(false);
    }

    public static function points(string $name, string $label = 'Tick points'): Repeater
    {
        return Repeater::make($name)->label($label)
            ->simple(TextInput::make('text')->required()->maxLength(200))
            ->reorderable()->defaultItems(0)->addActionLabel('Add point');
    }

    public static function stats(string $name = 'items'): Repeater
    {
        return Repeater::make($name)->label('Numbers')
            ->schema([
                TextInput::make('value')->label('Big number')->required()->maxLength(20),
                TextInput::make('label')->label('Small text')->maxLength(80),
            ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add number')
            ->itemLabel(fn (array $state) => $state['value'] ?? null)->collapsible();
    }

    public static function iconItems(string $name = 'items', string $label = 'Items'): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema([
                TextInput::make('icon')->label('Icon (emoji)')->maxLength(10)->helperText('e.g. 🩺 ❤️ 🛡️ — press Win + . to pick an emoji'),
                TextInput::make('title')->label('Heading')->required()->maxLength(120),
                Textarea::make('text')->label('Text')->rows(2)->columnSpanFull(),
            ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add item')
            ->itemLabel(fn (array $state) => trim(($state['icon'] ?? '').' '.($state['title'] ?? '')) ?: null)->collapsible();
    }

    public static function faq(string $name = 'items'): Repeater
    {
        return Repeater::make($name)->label('Questions')
            ->schema([
                TextInput::make('question')->required()->maxLength(250),
                Textarea::make('answer')->required()->rows(3),
            ])->reorderable()->defaultItems(0)->addActionLabel('Add question')
            ->itemLabel(fn (array $state) => $state['question'] ?? null)->collapsible()->collapsed();
    }

    public static function testimonials(string $name = 'items'): Repeater
    {
        return Repeater::make($name)->label('Reviews')
            ->schema([
                TextInput::make('name')->label('Customer name')->required()->maxLength(80),
                TextInput::make('area')->label('Area / City')->maxLength(80),
                Textarea::make('text')->label('Review')->required()->rows(3)->columnSpanFull(),
            ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add review')
            ->itemLabel(fn (array $state) => $state['name'] ?? null)->collapsible()->collapsed();
    }

    public static function photoCards(string $name = 'items'): Repeater
    {
        return Repeater::make($name)->label('Photo cards')
            ->schema([
                static::image('image', 'cards', 'Photo'),
                Grid::make(1)->schema([
                    TextInput::make('title')->required()->maxLength(120),
                    TextInput::make('text')->label('Small text')->maxLength(150),
                    static::link('link', 'Click opens'),
                    static::alt(),
                ])->columnSpan(1),
            ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add card')
            ->itemLabel(fn (array $state) => $state['title'] ?? null)->collapsible();
    }

    public static function background(): Select
    {
        return Select::make('background')->label('Background')->options(['white' => 'White', 'light' => 'Light colour'])->default('white')->selectablePlaceholder(false);
    }

    public static function anchor(): TextInput
    {
        return TextInput::make('anchor')->label('Section ID (optional)')->helperText('For links like #faq. Letters, numbers and - only.')->maxLength(40);
    }

    /** Page Builder sections. */
    public static function builder(string $name = 'blocks', string $label = 'Page sections'): Builder
    {
        return Builder::make($name)
            ->label($label)
            ->blocks(static::blocks())
            ->collapsible()
            ->cloneable()
            ->reorderableWithButtons()
            ->blockNumbers(false)
            ->addActionLabel('Add a section')
            ->blockPickerColumns(3)
            ->blockPickerWidth('3xl')
            ->columnSpanFull();
    }

    public static function blocks(): array
    {
        $common = fn (array $fields, bool $bg = true) => array_merge(
            [static::show()],
            $fields,
            $bg ? [Grid::make(2)->schema([static::background(), static::anchor()])] : [static::anchor()],
        );

        return [
            Block::make('heading_text')->label('Heading + Text')->icon(Heroicon::OutlinedBars3BottomLeft)
                ->schema($common([
                    ...static::head(),
                    Select::make('align')->options(['center' => 'Center', 'left' => 'Left'])->default('center')->selectablePlaceholder(false),
                    Grid::make(2)->schema([TextInput::make('button_text')->label('Button text (optional)'), static::link('button_link')]),
                ])),

            Block::make('rich_text')->label('Rich Text / Article')->icon(Heroicon::OutlinedDocumentText)
                ->schema($common([
                    TextInput::make('small')->label('Small text above heading'),
                    static::heading(),
                    RichEditor::make('body')->label('Content')
                        ->toolbarButtons([['bold', 'italic', 'underline', 'strike', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['table', 'attachFiles'], ['undo', 'redo']])
                        ->fileAttachmentsDisk('site')->fileAttachmentsDirectory('uploads/content')->fileAttachmentsVisibility('public')
                        ->columnSpanFull(),
                ])),

            Block::make('image_text')->label('Photo + Text')->icon(Heroicon::OutlinedPhoto)
                ->schema($common([
                    Grid::make(2)->schema([
                        static::image('image', 'sections', 'Photo (900 × 700)'),
                        Grid::make(1)->schema([
                            Select::make('image_position')->label('Photo side')->options(['left' => 'Left', 'right' => 'Right'])->default('left')->selectablePlaceholder(false),
                            static::alt(),
                        ]),
                    ]),
                    ...static::head(),
                    static::points('points'),
                    Grid::make(2)->schema([TextInput::make('button_text')->label('Button text (optional)'), static::link('button_link')]),
                ])),

            Block::make('services')->label('Service Cards')->icon(Heroicon::OutlinedSquares2x2)
                ->schema($common([
                    ...static::head(),
                    Grid::make(3)->schema([
                        Toggle::make('filter')->label('Show filter buttons')->inline(false),
                        TextInput::make('limit')->label('How many cards (0 = all)')->numeric()->default(0),
                        TextInput::make('category')->label('Only this category (optional)'),
                    ]),
                    Grid::make(2)->schema([TextInput::make('button_text')->label('Button under cards (optional)'), static::link('button_link', 'Button link', 'home-care-services')]),
                ])),

            Block::make('stats')->label('Numbers Strip')->icon(Heroicon::OutlinedChartBar)
                ->schema([static::show(), static::stats()]),

            Block::make('icon_boxes')->label('Icon Boxes (3 in a row)')->icon(Heroicon::OutlinedViewColumns)
                ->schema($common([...static::head(), static::iconItems()])),

            Block::make('steps')->label('How It Works (steps)')->icon(Heroicon::OutlinedListBullet)
                ->schema($common([...static::head(), static::iconItems('items', 'Steps')])),

            Block::make('why')->label('Why Choose Us (photo + points)')->icon(Heroicon::OutlinedShieldCheck)
                ->schema($common([
                    Grid::make(2)->schema([
                        static::image('image', 'sections', 'Photo (800 × 860)'),
                        Grid::make(1)->schema([
                            TextInput::make('badge_big')->label('Big text on photo box'),
                            TextInput::make('badge_text')->label('Small text on photo box'),
                            static::alt(),
                        ]),
                    ]),
                    ...static::head(),
                    static::iconItems('items', 'Points'),
                    Grid::make(2)->schema([TextInput::make('button_text')->label('Button text (optional)'), static::link('button_link')]),
                ])),

            Block::make('photo_cards')->label('Photo Cards')->icon(Heroicon::OutlinedRectangleGroup)
                ->schema($common([...static::head(), static::photoCards()])),

            Block::make('faq')->label('FAQ')->icon(Heroicon::OutlinedQuestionMarkCircle)
                ->schema($common([...static::head(), static::faq()])),

            Block::make('testimonials')->label('Testimonials')->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->schema($common([...static::head(), static::testimonials()])),

            Block::make('areas')->label('Areas We Serve')->icon(Heroicon::OutlinedMapPin)
                ->schema($common([...static::head(), static::points('items', 'Areas')->addActionLabel('Add area')])),

            Block::make('cta')->label('Call-to-Action Banner')->icon(Heroicon::OutlinedMegaphone)
                ->schema([
                    static::show(),
                    TextInput::make('heading')->helperText('Empty = text from Site Settings'),
                    TextInput::make('text')->helperText('Empty = text from Site Settings'),
                ]),

            Block::make('form')->label('Enquiry Form (dark section)')->icon(Heroicon::OutlinedEnvelope)
                ->schema([
                    static::show(),
                    ...static::head(),
                    Grid::make(2)->schema([
                        TextInput::make('form_heading')->default('Request a Call Back'),
                        TextInput::make('form_text')->default('Free consultation · No obligation'),
                        TextInput::make('enquiry_item')->label('Enquiry for (optional)')->helperText('Shown in the email, e.g. "ICU at Home"'),
                        Toggle::make('short_form')->label('Short form (hide message box)')->inline(false),
                        Toggle::make('show_contact')->label('Show phone / email / address')->default(true)->inline(false),
                    ]),
                    static::anchor(),
                ]),

            Block::make('contact')->label('Contact Cards + Form + Map')->icon(Heroicon::OutlinedPhone)
                ->schema($common([
                    ...static::head(),
                    Grid::make(2)->schema([
                        Toggle::make('form_show')->label('Show form')->default(true)->inline(false),
                        Toggle::make('map_show')->label('Show map')->default(true)->inline(false),
                        TextInput::make('form_heading')->default('Send Us a Message'),
                        TextInput::make('form_text'),
                    ]),
                    Textarea::make('map_embed')->label('Google Map embed link')->rows(2)->helperText('Google Maps → Share → Embed a map → paste the code or the link.'),
                ])),

            Block::make('map')->label('Google Map')->icon(Heroicon::OutlinedMap)
                ->schema($common([
                    ...static::head(),
                    Textarea::make('map_embed')->label('Google Map embed link')->rows(2)->required()->helperText('Google Maps → Share → Embed a map → paste the code or the link.'),
                ])),

            Block::make('custom_box')->label('Highlight Box with Buttons')->icon(Heroicon::OutlinedSparkles)
                ->schema([
                    static::show(),
                    TextInput::make('heading')->required(),
                    Textarea::make('text')->rows(2),
                    Grid::make(3)->schema([
                        TextInput::make('button_text')->default('Enquire Now'),
                        static::link('button_link'),
                        TextInput::make('enquiry_item')->label('Enquiry for'),
                    ]),
                ]),

            Block::make('gallery')->label('Photo Gallery')->icon(Heroicon::OutlinedPhoto)
                ->schema($common([
                    ...static::head(),
                    Repeater::make('images')->label('Photos')->schema([
                        static::image('image', 'gallery', 'Photo'),
                        Grid::make(1)->schema([TextInput::make('caption'), static::alt('alt')]),
                    ])->columns(2)->reorderable()->defaultItems(0)->addActionLabel('Add photo'),
                ])),

            Block::make('video')->label('YouTube Video')->icon(Heroicon::OutlinedPlayCircle)
                ->schema($common([
                    ...static::head(),
                    TextInput::make('url')->label('YouTube link')->required()->url()->placeholder('https://www.youtube.com/watch?v=…'),
                ])),

            Block::make('html')->label('Custom HTML / Code')->icon(Heroicon::OutlinedCodeBracket)
                ->schema([
                    static::show(),
                    Textarea::make('code')->label('HTML code')->rows(8)->required()->helperText('Advanced: any HTML, iframe or widget code.'),
                    Toggle::make('wrap')->label('Put inside a normal section (with page width)')->default(true),
                    Grid::make(2)->schema([static::background(), static::anchor()]),
                ]),
        ];
    }
}
