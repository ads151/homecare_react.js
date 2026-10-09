<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/** Services (cards), Media Library and Admin users. */
class ServicesMediaUsersTest extends AdminTestCase
{
    /* ---------------- Services ---------------- */

    public function test_create_service_shows_on_website_and_in_form_dropdown(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'title' => 'Dialysis Nurse at Home',
                'badge' => '12 hr shift',
                'label' => 'New',
                'category' => 'Nursing',
                'features' => [['text' => 'Trained in dialysis care'], ['text' => 'Available 24x7']], // simple repeater: form keeps each point as ['text' => …]
                'price_prefix' => 'Starting',
                'price' => '1800',
                'price_note' => '/ day',
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $s = Service::where('title', 'Dialysis Nurse at Home')->firstOrFail();
        $this->assertSame(['Trained in dialysis care', 'Available 24x7'], array_values($s->features));

        $this->get('/home-care-services')->assertOk()
            ->assertSee('Dialysis Nurse at Home')->assertSee('Trained in dialysis care')->assertSee('1,800');
        $this->get('/')->assertOk()->assertSee('<option>Dialysis Nurse at Home</option>', false);
    }

    public function test_service_name_is_required(): void
    {
        Livewire::test(CreateService::class)->fillForm(['title' => ''])->call('create')->assertHasFormErrors(['title' => 'required']);
    }

    public function test_edit_every_service_and_save(): void
    {
        foreach (Service::all() as $s) {
            Livewire::test(EditService::class, ['record' => $s->getRouteKey()])->call('save')->assertHasNoFormErrors();
            $this->assertSame($s->title, $s->fresh()->title);
        }

        $s = Service::first();
        Livewire::test(EditService::class, ['record' => $s->getRouteKey()])
            ->fillForm(['title' => 'Renamed Service', 'price' => 'On Request'])
            ->call('save')->assertHasNoFormErrors();
        $this->get('/home-care-services')->assertSee('Renamed Service')->assertSee('On Request');
    }

    public function test_hide_service_with_toggle(): void
    {
        $s = Service::first();

        Livewire::test(ListServices::class)->call('updateTableColumnState', 'is_active', (string) $s->getKey(), false);

        $this->assertFalse($s->fresh()->is_active);
        $this->get('/home-care-services')->assertOk()->assertDontSee('>'.$s->title.'<', false);
    }

    public function test_reorder_copy_and_delete_service(): void
    {
        $ids = Service::orderBy('sort_order')->pluck('id')->all();
        $reversed = array_reverse($ids);

        Livewire::test(ListServices::class)->call('reorderTable', array_map('strval', $reversed));
        $this->assertSame($reversed, Service::orderBy('sort_order')->pluck('id')->all());

        $s = Service::first();
        Livewire::test(ListServices::class)->callAction(TestAction::make('replicate')->table($s));
        $copy = Service::where('title', $s->title.' (copy)')->firstOrFail();
        $this->assertFalse($copy->is_active);

        Livewire::test(ListServices::class)->callAction(TestAction::make('delete')->table($copy));
        $this->assertModelMissing($copy);

        Livewire::test(ListServices::class)->callTableBulkAction('delete', [$s]);
        $this->assertModelMissing($s);
    }

    /* ---------------- Media Library ---------------- */

    public function test_upload_edit_and_delete_file(): void
    {
        Storage::fake('site');

        Livewire::test(ManageMedia::class)
            ->callAction('create', data: [
                'path' => UploadedFile::fake()->image('Nurse Photo.jpg', 800, 600),
                'name' => 'Nurse photo',
                'alt' => 'Nurse with patient',
            ])
            ->assertHasNoFormErrors();

        $m = Media::firstOrFail();
        $this->assertStringStartsWith('uploads/media/', $m->path);
        $this->assertStringEndsWith('.jpg', $m->path);
        $this->assertSame('Nurse photo', $m->name);
        Storage::disk('site')->assertExists($m->path);

        Livewire::test(ManageMedia::class)
            ->callAction(TestAction::make('edit')->table($m), data: ['name' => 'Renamed', 'alt' => 'New alt'])
            ->assertHasNoFormErrors();
        $this->assertSame('Renamed', $m->fresh()->name);

        Livewire::test(ManageMedia::class)->callAction(TestAction::make('delete')->table($m));
        $this->assertModelMissing($m);
    }

    public function test_media_rejects_unsafe_file_types(): void
    {
        Storage::fake('site');

        Livewire::test(ManageMedia::class)
            ->callAction('create', data: ['path' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])
            ->assertHasFormErrors(['path']);

        $this->assertSame(0, Media::count());
    }

    /* ---------------- Admin users ---------------- */

    public function test_add_edit_and_delete_admin_user(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction('create', data: ['name' => 'Office Staff', 'email' => 'staff@example.com', 'password' => 'secret-pass-1'])
            ->assertHasNoFormErrors();

        $u = User::where('email', 'staff@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secret-pass-1', $u->password));

        // empty password on edit keeps the old one
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($u), data: ['name' => 'Office Staff 2', 'email' => 'staff@example.com', 'password' => ''])
            ->assertHasNoFormErrors();
        $this->assertSame('Office Staff 2', $u->fresh()->name);
        $this->assertTrue(Hash::check('secret-pass-1', $u->fresh()->password));

        // new password works (and is not hashed twice)
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($u), data: ['name' => 'Office Staff 2', 'email' => 'staff@example.com', 'password' => 'another-pass-2'])
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('another-pass-2', $u->fresh()->password));

        Livewire::test(ManageUsers::class)->callAction(TestAction::make('delete')->table($u));
        $this->assertModelMissing($u);
    }

    public function test_user_rules(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction('create', data: ['name' => 'X', 'email' => $this->admin->email, 'password' => 'secret-pass-1'])
            ->assertHasFormErrors(['email' => 'unique']);
        Livewire::test(ManageUsers::class)
            ->callAction('create', data: ['name' => 'X', 'email' => 'x@example.com', 'password' => 'short'])
            ->assertHasFormErrors(['password']);
        Livewire::test(ManageUsers::class)
            ->callAction('create', data: ['name' => 'X', 'email' => 'x@example.com', 'password' => ''])
            ->assertHasFormErrors(['password' => 'required']);
    }

    public function test_cannot_delete_yourself(): void
    {
        $other = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->assertActionHidden(TestAction::make('delete')->table($this->admin))
            ->assertActionVisible(TestAction::make('delete')->table($other));
    }

    public function test_new_admin_can_log_in(): void
    {
        $u = User::factory()->create(['email' => 'login@example.com', 'password' => 'secret-pass-1']);
        auth()->logout();

        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm(['email' => 'login@example.com', 'password' => 'secret-pass-1'])
            ->call('authenticate')
            ->assertHasNoFormErrors();
        $this->assertAuthenticatedAs($u);

        auth()->logout();
        Livewire::test(\Filament\Auth\Pages\Login::class)
            ->fillForm(['email' => 'login@example.com', 'password' => 'wrong-password'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
        $this->assertGuest();
    }
}
