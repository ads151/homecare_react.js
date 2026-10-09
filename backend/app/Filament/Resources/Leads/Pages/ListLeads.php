<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('All')];
        foreach (Lead::STATUSES as $key => $label) {
            $tabs[$key] = Tab::make($label)
                ->badge(fn () => Lead::query()->where('status', $key)->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $key));
        }

        return $tabs;
    }
}
