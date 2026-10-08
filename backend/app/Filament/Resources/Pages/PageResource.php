<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Models\Page;
use App\Services\SeoAnalyzer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('bold')
                    ->description(fn (Page $r) => $r->slug === 'home' ? '/' : '/'.$r->slug),
                TextColumn::make('template')->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => Str::before(Page::TEMPLATES[$state] ?? $state, ' (')),
                TextColumn::make('seo_score')->label('SEO Score')->sortable()->badge()
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : $state.' / 100')
                    ->color(fn ($state) => $state === null ? 'gray' : SeoAnalyzer::color((int) $state)),
                TextColumn::make('focus_keyword')->label('Focus keyword')->placeholder('Not set')->toggleable(),
                IconColumn::make('is_published')->label('Live')->boolean(),
                IconColumn::make('robots_index')->label('Google')->boolean()->toggleable(),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable()->toggleable(),
            ])
            ->defaultSort('id')
            ->filters([
                SelectFilter::make('template')->options(Page::TEMPLATES),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                    ->url(fn (Page $r) => $r->url().($r->is_published ? '' : '?preview=1'), shouldOpenInNewTab: true),
                EditAction::make(),
                ReplicateAction::make()->label('Copy')
                    ->excludeAttributes(['seo_score'])
                    ->mutateRecordDataUsing(function (array $data) {
                        $data['title'] .= ' (copy)';
                        $data['slug'] = static::uniqueSlug(Str::slug($data['title']));
                        $data['is_system'] = false;
                        $data['is_published'] = false;

                        return $data;
                    })
                    ->successRedirectUrl(fn (Page $replica) => static::getUrl('edit', ['record' => $replica])),
                DeleteAction::make()->hidden(fn (Page $r) => $r->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(fn ($records) => $records->reject->is_system->each->delete()),
                ]),
            ]);
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = $slug ?: 'page';
        $base = $slug;
        $i = 2;
        while (Page::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
