<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GenderType: string implements HasLabel
{
    case Male = 'male';
    case Female = 'female';
    case other = 'other';
    

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
