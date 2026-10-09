<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Enquiries';

    protected static ?string $navigationLabel = 'Enquiries (Leads)';

    protected static ?string $modelLabel = 'enquiry';

    protected static ?string $pluralModelLabel = 'enquiries';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $n = Lead::query()->where('status', 'new')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->options(Lead::STATUSES)->required(),
            Textarea::make('notes')->label('Notes (only for you)')->rows(4),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Section::make('Customer')->columnSpan(['lg' => 2])->columns(2)->schema([
                    TextEntry::make('name')->weight('bold')->placeholder('—'),
                    TextEntry::make('mobile')->placeholder('—')->copyable()
                        ->url(fn (Lead $r) => $r->mobile ? 'tel:+91'.$r->mobile : null),
                    TextEntry::make('email')->placeholder('—')->copyable(),
                    TextEntry::make('item')->label('Enquiry for')->placeholder('—'),
                    KeyValueEntry::make('form_values')->label('All form answers')->columnSpanFull()
                        ->state(fn (Lead $r) => collect((array) $r->data)->mapWithKeys(fn ($d, $k) => [($d['label'] ?? $k) => (string) ($d['value'] ?? '')])->all())
                        ->keyLabel('Question')->valueLabel('Answer'),
                ]),
                Section::make('Details')->schema([
                    TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => Lead::STATUSES[$state] ?? $state)->color(fn ($state) => static::statusColor($state)),
                    TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                    TextEntry::make('form_name')->label('Form')->placeholder('—'),
                    TextEntry::make('page_url')->label('Page')->placeholder('—')->url(fn ($state) => $state, true)->limit(50),
                    IconEntry::make('mail_sent')->label('Email sent')->boolean(),
                    TextEntry::make('mail_error')->label('Email error')->color('danger')->visible(fn (Lead $r) => ! $r->mail_sent && $r->mail_error),
                    TextEntry::make('ip')->label('IP address')->placeholder('—'),
                    TextEntry::make('notes')->placeholder('No notes yet'),
                ]),
            ]),
        ]);
    }

    public static function statusColor(?string $s): string
    {
        return match ($s) {
            'new' => 'danger',
            'called' => 'info',
            'follow_up' => 'warning',
            'converted' => 'success',
            default => 'gray',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d M, h:i A')->sortable()
                    ->description(fn (Lead $r) => $r->created_at?->diffForHumans()),
                TextColumn::make('name')->searchable()->weight('bold')->placeholder('—'),
                TextColumn::make('mobile')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('item')->label('Enquiry for')->searchable()->placeholder('—')->wrap(),
                TextColumn::make('area')->label('Area')->state(fn (Lead $r) => $r->data['area']['value'] ?? null)->placeholder('—')->toggleable(),
                TextColumn::make('form_name')->label('Form')->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('status')->options(Lead::STATUSES)->selectablePlaceholder(false),
                IconColumn::make('mail_sent')->label('Email')->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Lead::STATUSES)->multiple(),
                Filter::make('date')->schema([
                    DatePicker::make('from')->label('From date'),
                    DatePicker::make('until')->label('To date'),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                    ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->color('info')->iconButton()
                    ->url(fn (Lead $r) => 'tel:+91'.$r->mobile)->visible(fn (Lead $r) => filled($r->mobile)),
                Action::make('whatsapp')->label('WhatsApp')->icon(Heroicon::OutlinedChatBubbleOvalLeft)->color('success')->iconButton()
                    ->url(fn (Lead $r) => 'https://wa.me/91'.$r->mobile, true)->visible(fn (Lead $r) => filled($r->mobile)),
                ViewAction::make(),
                Action::make('notes')->label('Notes')->icon(Heroicon::OutlinedPencilSquare)
                    ->fillForm(fn (Lead $r) => ['status' => $r->status, 'notes' => $r->notes])
                    ->schema([
                        Select::make('status')->options(Lead::STATUSES)->required(),
                        Textarea::make('notes')->rows(4),
                    ])
                    ->action(fn (Lead $r, array $data) => $r->update($data)),
                DeleteAction::make()->iconButton(),
            ])
            ->headerActions([
                static::exportAction('export', 'Export to Excel (CSV)'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelected')->label('Export selected (CSV)')->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(fn (Collection $records) => static::csv($records)),
                    BulkAction::make('markCalled')->label('Mark as Called')->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'called'])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function exportAction(string $name, string $label): Action
    {
        return Action::make($name)->label($label)->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
            ->action(fn ($livewire) => static::csv(
                method_exists($livewire, 'getFilteredSortedTableQuery') ? $livewire->getFilteredSortedTableQuery()->get() : Lead::query()->latest()->get()
            ));
    }

    public static function csv($records)
    {
        $labels = [];
        foreach ($records as $r) {
            foreach ((array) $r->data as $k => $d) {
                $labels[$k] ??= $d['label'] ?? $k;
            }
        }
        $safe = fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'".$v : (string) $v;

        return response()->streamDownload(function () use ($records, $labels, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(['Date & Time'], array_values($labels), ['Enquiry For', 'Status', 'Notes', 'Form', 'Page', 'IP']));
            foreach ($records as $r) {
                $row = [$r->created_at?->format('d M Y, h:i A')];
                foreach (array_keys($labels) as $k) {
                    $row[] = $safe($r->data[$k]['value'] ?? '');
                }
                $row = array_merge($row, [$safe($r->item), Lead::STATUSES[$r->status] ?? $r->status, $safe($r->notes), $safe($r->form_name), $r->page_url, $r->ip]);
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'view' => ViewLead::route('/{record}'),
        ];
    }
}
