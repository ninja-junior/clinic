<?php

use App\Filament\Parent\Resources\PatientResource;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Factories\PetFactory;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->parentUser = User::whereName('Parent')->first();
    actingAs($this->parentUser);
});

it('renders the index page', function () {
    get(PatientResource::getUrl('index', panel: 'parent'))
        ->assertOk();
});

it('renders the create page', function () {
    get(PatientResource::getUrl('create', panel: 'parent'))
        ->assertOk();
});

it('renders the edit page', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    get(PatientResource::getUrl('edit', ['record' => $patient], panel: 'parent'))
        ->assertOk();
});

it('cannot edit patients that do not belong to the parent', function () {
    $patient = Patient::factory()->create();

    get(PatientResource::getUrl('edit', ['record' => $patient], panel: 'parent'))
        ->assertStatus(403);
});

it('can list patients', function () {
    $patients = Patient::factory(3)
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    Livewire::test(PatientResource\Pages\ListPatients::class)
        ->assertCanSeeTableRecords($patients)
        ->assertSeeText([
            $patients[0]->name,
            $patients[0]->date_of_birth->format(config('app.date_format')),
            $patients[0]->type->name,

            $patients[1]->name,
            $patients[1]->date_of_birth->format(config('app.date_format')),
            $patients[1]->type->name,

            $patients[2]->name,
            $patients[2]->date_of_birth->format(config('app.date_format')),
            $patients[2]->type->name,
        ]);
});

it('only shows patients for the current parent', function () {
    $myPet = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    $otherOwner = User::factory()->role('parent')->create();

    $otherPet = Patient::factory()
        ->for($otherOwner, relationship: 'parent')->create();

    Livewire::test(PatientResource\Pages\ListPatients::class)
        ->assertSeeText($myPet->name)
        ->assertDontSeeText($otherPet->name);
});

it('can create patients', function () {
    $newPet = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->make();

    Livewire::test(PatientResource\Pages\CreatePatient::class)
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type
        ])
        ->call('create')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Patient::class, [
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type
        ]);
});

it('validate form errors on create', function (Patient $newPet) {
    Livewire::test(PatientResource\Pages\CreatePatient::class)
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type
        ])
        ->call('create')
        ->assertHasFormErrors();
    
    $this->assertDatabaseMissing(Patient::class, [
        'name' => $newPet->name,
        'date_of_birth' => $newPet->date_of_birth,
        'type' => $newPet->type
    ]);
})->with([
    [fn() => Patient::factory()->state(['name' => null])->make(), 'Missing name'],
    [fn() => Patient::factory()->state(['date_of_birth' => null])->make(), 'Missing date of birth'],
    [fn() => Patient::factory()->state(['type' => null])->make(), 'Missing type'],
]);

it('can retrieve the patient data for edit', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    Livewire::test(PatientResource\Pages\EditPatient::class, [
        'record' => $patient->getRouteKey()
    ])
        ->assertFormSet([
            'name' => $patient->name,
            'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
            'type' => $patient->type->value
        ]);
});

it('can update the patient', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();
    $newPetData = Patient::factory()
        ->state([
            'name' => fake()->name,
            'date_of_birth' => fake()->date(),
            'type' => 'cat'
        ])
        ->for($this->parentUser, relationship: 'parent')
        ->make();

    Livewire::test(PatientResource\Pages\EditPatient::class, [
        'record' => $patient->getRouteKey()
    ])
        ->fillForm([
            'name' => $newPetData->name,
            'date_of_birth' => $newPetData->date_of_birth->format('Y-m-d'),
            'type' => $newPetData->type->value
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($patient->refresh())
        ->name->toBe($newPetData->name)
        ->date_of_birth->format('Y-m-d')->toBe($newPetData->date_of_birth->format('Y-m-d'))
        ->type->value->toBe($newPetData->type->value);
});

it('validate form errors on edit', function (Patient $updatedPet) {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();
    
    Livewire::test(PatientResource\Pages\EditPatient::class, [
        'record' => $patient->getRouteKey()
    ])
        ->fillForm([
            'name' => $updatedPet->name,
            'date_of_birth' => $updatedPet->date_of_birth,
            'type' => $updatedPet->type
        ])
        ->call('save')
        ->assertHasFormErrors();
})->with([
    [fn() => Patient::factory()->state(['name' => null])->make(), 'Missing name'],
    [fn() => Patient::factory()->state(['date_of_birth' => null])->make(), 'Missing date of birth'],
    [fn() => Patient::factory()->state(['type' => null])->make(), 'Missing type'],
]);

it('can delete a patient from the edit patient form', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    Livewire::test(PatientResource\Pages\EditPatient::class, [
        'record' => $patient->getRouteKey()
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($patient);
});

it('It can delete a patient from the list of patients', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    Livewire::test(PatientResource\Pages\ListPatients::class)
        ->assertTableActionVisible('delete', $patient)
        ->callTableAction(ActionsDeleteAction::class, $patient);
    
    $this->assertModelMissing($patient);
});

it('can upload patient image', function () {
    $newPet = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->make();
    
    Storage::fake('avatars');

    $file = UploadedFile::fake()->image('avatar.png');

    Livewire::test(PatientResource\Pages\CreatePatient::class)
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type,
            'avatar' => $file
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $patient = Patient::first();
    Storage::disk('avatars')->assertExists($patient->avatar);
});

it('can update the patient image', function () {
    $patient = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->create();

    Storage::fake('avatars');

    $file = UploadedFile::fake()->image('new_avatar.png');

    $newPet = Patient::factory()
        ->for($this->parentUser, relationship: 'parent')
        ->make();
    
    Livewire::test(PatientResource\Pages\EditPatient::class, [
        'record' => $patient->getRouteKey()
    ])
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type,
            'avatar' => $file
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $patient->refresh();

    Storage::disk('avatars')->assertExists($patient->avatar);
});
