<?php

namespace App\Policies;

use App\Models\Preset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PresetPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        // Allow all actions by admin users.
        if ($user->is_admin) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Preset  $preset
     * @return bool
     */
    public function view(User $user, Preset $preset): bool
    {
        return $user->is($preset->user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $owner
     *   The user this preset will belong to.
     * @return bool
     */
    public function create(User $user, User $owner): bool
    {
        return $user->is($owner);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Preset  $preset
     * @return bool
     */
    public function update(User $user, Preset $preset): bool
    {
        // User can only update the preset if they own it and it isn't
        // flagged as completed.
        return $user->is($preset->user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Preset  $preset
     * @return bool
     */
    public function delete(User $user, Preset $preset): bool
    {
        return $user->is($preset->user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Preset  $preset
     * @return bool
     */
    public function restore(User $user, Preset $preset): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Preset  $preset
     * @return bool
     */
    public function forceDelete(User $user, Preset $preset): bool
    {
        return false;
    }
}
