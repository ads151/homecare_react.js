<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $trend = collect(range(6, 0))->map(fn ($d) => Lead::query()->whereDate('created_at', now()->subDays($d))->count())->all();
        $avgSeo = (int) round((float) Page::query()->whereNotNull('seo_score')->where('robots_index', true)->avg('seo_score'));

        return [
            Stat::make('New enquiries', Lead::query()->where('status', 'new')->count())
                ->description('Not called yet')->color('danger')->icon('heroicon-o-bell-alert')
                ->url(LeadResource::getUrl('index', ['tab' => 'new'])),
            Stat::make('Today', Lead::query()->whereDate('created_at', today())->count())
                ->description('Enquiries received today')->chart($trend)->color('success')->icon('heroicon-o-inbox-arrow-down'),
            Stat::make('This month', Lead::query()->where('created_at', '>=', now()->startOfMonth())->count())
                ->description(Lead::query()->where('status', 'converted')->where('created_at', '>=', now()->startOfMonth())->count().' converted')
                ->icon('heroicon-o-calendar'),
            Stat::make('Average SEO score', $avgSeo.' / 100')
                ->description('Of pages shown in Google')->color($avgSeo >= 80 ? 'success' : ($avgSeo >= 50 ? 'warning' : 'danger'))
                ->icon('heroicon-o-magnifying-glass'),
        ];
    }
}
