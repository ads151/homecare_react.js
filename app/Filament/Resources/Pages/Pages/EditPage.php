<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Services\SeoAnalyzer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View page')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->url(fn () => $this->record->url().($this->record->is_published ? '' : '?preview=1'), shouldOpenInNewTab: true),
            DeleteAction::make()->hidden(fn () => $this->record->is_system),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PageForm::splitContent($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageForm::mergeContent($data, $data['template'] ?? $this->record->template);
    }

    protected function afterSave(): void
    {
        $this->record->forceFill(['seo_score' => SeoAnalyzer::analyze($this->record->fresh())['score']])->saveQuietly();
    }
}
