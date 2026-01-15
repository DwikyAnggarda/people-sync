<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkSchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_work_schedule');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkSchedule $workSchedule): bool
    {
        return $user->can('view_work_schedule');
    }

    /**
     * Determine whether the user can create models.
     * Work schedules are pre-seeded, no creation needed.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkSchedule $workSchedule): bool
    {
        return $user->can('update_work_schedule');
    }

    /**
     * Determine whether the user can delete the model.
     * Work schedules should not be deleted.
     */
    public function delete(User $user, WorkSchedule $workSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, WorkSchedule $workSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, WorkSchedule $workSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, WorkSchedule $workSchedule): bool
    {
        return false;
    }
}
