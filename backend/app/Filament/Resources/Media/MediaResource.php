<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Filament\Support\SiteFields;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'file';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')->label('File')->required()
                ->disk('site')->directory('uploads/media')->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'])
                ->maxSize(10240)
                ->imageEditor()
                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => SiteFields::fileName($file))
                ->helperText('JPG, PNG, WEBP, GIF or PDF — up to 10 MB. After saving, copy the link and use it anywhere.')
                ->columnSpanFull(),
            TextInput::make('name')->label('Name (for you)')->maxLength(150),
            TextInput::make('alt')->label('ALT text (for Google)')->maxLength(150),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['md' => 3, 'xl' => 5])
            ->columns([
                Stack::make([
                    ImageColumn::make('path')->disk('site')->imageHeight(140)
                        ->extraImgAttributes(['style' => 'width:100%;object-fit:cover;border-radius:10px'])
                        ->defaultImageUrl(url('images/general/favicon.png')),
                    TextColumn::make('name')->weight('bold')->searchable()->placeholder('Untitled'),
                    TextColumn::make('path')->label('Link')->formatStateUsing(fn () => 'Copy link')
                        ->icon(Heroicon::OutlinedClipboard)->color('primary')
                        ->copyable()->copyableState(fn (Media $r) => $r->url())->copyMessage('Link copied'),
                    TextColumn::make('size')->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 0).' KB' : '')->color('gray')->size('xs'),
                ])->space(2),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()->after(fn (Media $r) => static::deleteFile($r))])
            ->toolbarActions([BulkActionGroup::make([
                DeleteBulkAction::make()->after(fn ($records) => $records->each(fn ($r) => static::deleteFile($r))),
            ])]);
    }

    public static function fillMeta(array $data): array
    {
        $full = public_path($data['path'] ?? '');
        if (is_file($full)) {
            $data['size'] = filesize($full);
            $data['mime'] = File::mimeType($full);
        }
        $data['name'] = ($data['name'] ?? '') ?: pathinfo($data['path'] ?? '', PATHINFO_FILENAME);

        return $data;
    }

    public static function deleteFile(Media $r): void
    {
        $full = public_path($r->path);
        if (str_starts_with(str_replace('\\', '/', $r->path), 'uploads/') && is_file($full)) {
            @unlink($full);
        }
    }

    public static function getPages(): array
    {
        return ['index' => ManageMedia::route('/')];
    }
}
