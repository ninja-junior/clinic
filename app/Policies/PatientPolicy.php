<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Patient $patient): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'parent' => $user->id == $patient->parent_id
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'parent' => $user->id == $patient->parent_id
        };
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'parent' => $user->id == $patient->parent_id
        };
    }
}
