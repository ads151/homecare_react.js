<?php

namespace Tests\Feature;

use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\Lead;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

/** Enquiries (Leads): every tab, filter, button and export. */
class LeadsTest extends AdminTestCase
{
    /** @return array<string, Lead> one lead per status */
    private function oneOfEach(): array
    {
        $out = [];
        foreach (array_keys(Lead::STATUSES) as $i => $status) {
            $out[$status] = $this->makeLead(['status' => $status, 'created_at' => now()->subDays($i * 10)]);
        }

        return $out;
    }

    public function test_each_status_tab_shows_only_its_leads(): void
    {
        $leads = $this->oneOfEach();
        $this->makeLead(['status' => 'new']); // 2 new

        Livewire::test(ListLeads::class)->assertCountTableRecords(count($leads) + 1);

        foreach (array_keys(Lead::STATUSES) as $status) {
            $expected = Lead::query()->where('status', $status)->get();
            Livewire::test(ListLeads::class)
                ->set('activeTab', $status)
                ->assertCountTableRecords($expected->count())
                ->assertCanSeeTableRecords($expected)
                ->assertCanNotSeeTableRecords(Lead::query()->where('status', '!=', $status)->get());
        }
    }

    public function test_status_filter(): void
    {
        $leads = $this->oneOfEach();

        Livewire::test(ListLeads::class)
            ->filterTable('status', ['called', 'converted'])
            ->assertCanSeeTableRecords([$leads['called'], $leads['converted']])
            ->assertCanNotSeeTableRecords([$leads['new'], $leads['follow_up'], $leads['closed']]);
    }

    public function test_date_filter(): void
    {
        $leads = $this->oneOfEach(); // 0, 10, 20, 30, 40 days old

        Livewire::test(ListLeads::class)
            ->filterTable('date', ['from' => now()->subDays(15)->toDateString(), 'until' => now()->toDateString()])
            ->assertCanSeeTableRecords([$leads['new'], $leads['called']])
            ->assertCanNotSeeTableRecords([$leads['follow_up'], $leads['converted'], $leads['closed']]);

        Livewire::test(ListLeads::class)
            ->filterTable('date', ['until' => now()->subDays(25)->toDateString()])
            ->assertCanSeeTableRecords([$leads['converted'], $leads['closed']])
            ->assertCanNotSeeTableRecords([$leads['new'], $leads['called']]);
    }

    public function test_search_by_name_mobile_and_item(): void
    {
        $a = $this->makeLead(['name' => 'Rahul Sharma', 'mobile' => '9811111111', 'item' => 'ICU at Home']);
        $b = $this->makeLead(['name' => 'Priya Gupta', 'mobile' => '9822222222', 'item' => 'Elder Care']);

        Livewire::test(ListLeads::class)->searchTable('Rahul')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
        Livewire::test(ListLeads::class)->searchTable('9822222222')->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);
        Livewire::test(ListLeads::class)->searchTable('ICU')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
    }

    public function test_status_dropdown_in_table_saves(): void
    {
        $lead = $this->makeLead();

        foreach (array_keys(Lead::STATUSES) as $status) {
            Livewire::test(ListLeads::class)->call('updateTableColumnState', 'status', (string) $lead->getKey(), $status);
            $this->assertSame($status, $lead->fresh()->status);
        }
    }

    public function test_notes_button_in_table(): void
    {
        $lead = $this->makeLead();

        Livewire::test(ListLeads::class)
            ->callAction(TestAction::make('notes')->table($lead), data: ['status' => 'follow_up', 'notes' => 'Call on Monday'])
            ->assertHasNoFormErrors();

        $this->assertSame('follow_up', $lead->fresh()->status);
        $this->assertSame('Call on Monday', $lead->fresh()->notes);
    }

    public function test_call_and_whatsapp_buttons_link_to_the_number(): void
    {
        $lead = $this->makeLead(['mobile' => '9876543210']);
        $noMobile = $this->makeLead(['mobile' => null]);

        Livewire::test(ListLeads::class)
            ->assertActionVisible(TestAction::make('call')->table($lead))
            ->assertActionHasUrl(TestAction::make('call')->table($lead), 'tel:+919876543210')
            ->assertActionHasUrl(TestAction::make('whatsapp')->table($lead), 'https://wa.me/919876543210')
            ->assertActionHidden(TestAction::make('call')->table($noMobile))
            ->assertActionHidden(TestAction::make('whatsapp')->table($noMobile));
    }

    public function test_view_page_status_and_notes_button(): void
    {
        $lead = $this->makeLead();

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->assertOk()
            ->callAction('notes', data: ['status' => 'converted', 'notes' => 'Booked 12 hr nurse'])
            ->assertHasNoFormErrors();

        $this->assertSame('converted', $lead->fresh()->status);
        $this->assertSame('Booked 12 hr nurse', $lead->fresh()->notes);
    }

    public function test_view_page_delete_button(): void
    {
        $lead = $this->makeLead();

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])->callAction('delete');

        $this->assertModelMissing($lead);
    }

    public function test_bulk_mark_as_called(): void
    {
        $a = $this->makeLead();
        $b = $this->makeLead();
        $c = $this->makeLead();

        Livewire::test(ListLeads::class)->callTableBulkAction('markCalled', [$a, $b]);

        $this->assertSame('called', $a->fresh()->status);
        $this->assertSame('called', $b->fresh()->status);
        $this->assertSame('new', $c->fresh()->status);
    }

    public function test_delete_and_bulk_delete(): void
    {
        $a = $this->makeLead();
        $b = $this->makeLead();
        $c = $this->makeLead();

        Livewire::test(ListLeads::class)->callAction(TestAction::make('delete')->table($a));
        $this->assertModelMissing($a);

        Livewire::test(ListLeads::class)->callTableBulkAction('delete', [$b]);
        $this->assertModelMissing($b);
        $this->assertModelExists($c);
    }

    public function test_csv_export_all_and_selected(): void
    {
        $a = $this->makeLead(['name' => 'Rahul Sharma', 'notes' => '=HYPERLINK("x")']);
        $b = $this->makeLead(['name' => 'Priya Gupta']);

        Livewire::test(ListLeads::class)->callAction(TestAction::make('export')->table())->assertFileDownloaded();
        Livewire::test(ListLeads::class)->callTableBulkAction('exportSelected', [$a])->assertFileDownloaded();

        // CSV content: header, both rows, and spreadsheet formulas made safe
        ob_start();
        LeadResource::csv(Lead::query()->orderBy('id')->get())->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString('Date & Time', $csv);
        $this->assertStringContainsString('Rahul Sharma', $csv);
        $this->assertStringContainsString('Priya Gupta', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_menu_badge_counts_new_leads(): void
    {
        $this->assertNull(LeadResource::getNavigationBadge());
        $this->makeLead();
        $this->makeLead();
        $this->makeLead(['status' => 'called']);
        $this->assertSame('2', LeadResource::getNavigationBadge());
    }

    public function test_leads_cannot_be_created_from_admin(): void
    {
        $this->assertFalse(LeadResource::canCreate());
    }
}
