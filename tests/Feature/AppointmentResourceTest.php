<?php

use App\Filament\Parent\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Slot;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->parentUser = User::whereName('Parent')->first();
    $this->doctorUser = User::whereName('Doctor')->first();
    actingAs($this->parentUser);
});

it('renders the index page', function () {
    get(AppointmentResource::getUrl('index', panel: 'parent'))
        ->assertOk();
});

it('can show a list of appointments', function () {

    $appointments = Appointment::factory(3)
        ->for(Patient::factory())
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    Livewire::test(AppointmentResource\Pages\ListAppointments::class)
        ->assertCanSeeTableRecords($appointments)
        ->assertSeeText($appointments[0]->patient->name)
        ->assertSeeText($appointments[0]->description)
        ->assertSeeText($appointments[0]->doctor->name)
        ->assertSeeText($appointments[0]->clinic->name)
        ->assertSeeText($appointments[0]->date)
        ->assertSeeText($appointments[0]->slot->formatted_time)
        ->assertSeeText($appointments[0]->status->name);
});
