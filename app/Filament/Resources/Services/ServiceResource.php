<?php

namespace App\Filament\Resources\Services;

use App\Filament\Support\SiteFields as F;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Services (cards)';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Card')->description('Cards show on the Home page, the Services page and in the "Service Needed" list of every form.')->columns(2)->schema([
                        TextInput::make('title')->label('Service name')->required()->maxLength(120)->columnSpanFull(),
                        TextInput::make('badge')->label('Dark label on photo')->placeholder('12 / 24 hr shift')->maxLength(60),
                        TextInput::make('label')->label('Green label on photo')->placeholder('Most Booked')->maxLength(40),
                        TextInput::make('category')->label('Category (for filter buttons)')->maxLength(60)
                            ->datalist(fn () => Service::query()->whereNotNull('category')->distinct()->pluck('category')->all()),
                        TextInput::make('button_text')->label('Button text')->placeholder('Empty = Book Now')->maxLength(40),
                        Textarea::make('text')->label('Short line under name (optional)')->rows(2)->columnSpanFull(),
                        Repeater::make('features')->label('Tick points')->simple(TextInput::make('text')->required()->maxLength(150))
                            ->reorderable()->addActionLabel('Add point')->defaultItems(0)->columnSpanFull(),
                    ]),
                    Section::make('Price (optional)')->description('Leave price empty to hide it. A number like 1200 shows as ₹1,200; text like "On Request" shows as it is.')
                        ->columns(4)->collapsible()->schema([
                            TextInput::make('price_prefix')->label('Before price')->placeholder('Starting'),
                            TextInput::make('price')->label('Price'),
                            TextInput::make('old_price')->label('Old price (crossed)'),
                            TextInput::make('price_note')->label('After price')->placeholder('/ day'),
                        ]),
                ]),
                Group::make()->schema([
                    Section::make('Photo')->schema([
                        F::image('image', 'services', 'Card photo (700 × 440)'),
                        F::alt('image_alt'),
                    ]),
                    Section::make('Visibility')->schema([
                        Toggle::make('is_active')->label('Show on website')->default(true),
                        TextInput::make('sort_order')->label('Position')->numeric()->default(fn () => (int) Service::max('sort_order') + 1)
                            ->helperText('1 = first. You can also drag rows in the list.'),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->disk('site')->label('')->imageHeight(48)->extraImgAttributes(['style' => 'border-radius:8px;object-fit:cover;width:76px']),
                TextColumn::make('title')->searchable()->weight('bold')->description(fn (Service $r) => $r->badge),
                TextColumn::make('category')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('label')->badge()->color('success')->placeholder('—')->toggleable(),
                TextColumn::make('price')->formatStateUsing(fn ($state) => is_numeric(str_replace([',', ' '], '', (string) $state)) ? '₹'.$state : $state)->placeholder('—')->toggleable(),
                ToggleColumn::make('is_active')->label('Show'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()->label('Copy')->mutateRecordDataUsing(function (array $data) {
                    $data['title'] .= ' (copy)';
                    $data['is_active'] = false;
                    $data['sort_order'] = (int) Service::max('sort_order') + 1;

                    return $data;
                }),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}

