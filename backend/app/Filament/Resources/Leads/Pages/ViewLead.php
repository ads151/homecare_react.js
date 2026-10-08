<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->color('info')
                ->url(fn () => 'tel:+91'.$this->record->mobile)->visible(fn () => filled($this->record->mobile)),
            Action::make('whatsapp')->label('WhatsApp')->icon(Heroicon::OutlinedChatBubbleOvalLeft)->color('success')
                ->url(fn () => 'https://wa.me/91'.$this->record->mobile, true)->visible(fn () => filled($this->record->mobile)),
            Action::make('notes')->label('Status & notes')->icon(Heroicon::OutlinedPencilSquare)
                ->fillForm(fn () => ['status' => $this->record->status, 'notes' => $this->record->notes])
                ->schema([
                    Select::make('status')->options(\App\Models\Lead::STATUSES)->required(),
                    Textarea::make('notes')->rows(4),
                ])
                ->action(fn (array $data) => $this->record->update($data)),
            DeleteAction::make(),
        ];
    }
}
