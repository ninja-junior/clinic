<?php

namespace App\Filament\Parent\Resources\AppointmentResource\Pages;

use App\Filament\Parent\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;
}
