<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'dashboard_preferences'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'dashboard_preferences' => 'array',
            'role' => UserRole::class,
        ];
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->using(EventUser::class)
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function permissionForEvent(Event $event): ?Permission
    {
        $permission = $this->events
            ->find($event->id)
            ?->pivot
            ->permission;

        return $permission ? Permission::from($permission) : null;
    }

    public function canView(Event $event): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($this->permissionForEvent($event), [Permission::Viewer, Permission::Tracker]);
    }

    public function canTrack(Event $event): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissionForEvent($event) === Permission::Tracker;
    }
}
