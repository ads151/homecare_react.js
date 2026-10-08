<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Lead::query()->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->label('Date')->since(),
                TextColumn::make('name')->weight('bold')->placeholder('—'),
                TextColumn::make('mobile')->copyable()->placeholder('—'),
                TextColumn::make('item')->label('Enquiry for')->placeholder('—'),
                SelectColumn::make('status')->options(Lead::STATUSES)->selectablePlaceholder(false),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->url(fn (Lead $r) => LeadResource::getUrl('view', ['record' => $r])),
            ])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Enquiries from the website forms will appear here.');
    }
}
