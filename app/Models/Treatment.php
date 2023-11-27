<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Treatment extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => MoneyCast::class,
    ];
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
