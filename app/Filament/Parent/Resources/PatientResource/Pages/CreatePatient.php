<?php

namespace App\Filament\Parent\Resources\PatientResource\Pages;

use App\Filament\Parent\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;
}
