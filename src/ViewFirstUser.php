<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks\HushHush;

use App\Events\UserViewAttempted;
use App\Listeners\MedalUnlocks\MedalUnlock;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ViewFirstUser extends MedalUnlock
{
    protected UserViewAttempted $event;

    public static function getMedalSlug(): string
    {
        return 'anonymous';
    }

    public static function getQueueableState(): mixed
    {
        return auth()->id();
    }

    protected function getApplicableUsers(): Collection|User|array
    {
        return User::find($this->state) ?? [];
    }

    protected function shouldHandle(): bool
    {
        $route = Route::current();

        return auth()->check()
            && $route->getName() === 'users.show'
            && $route->parameter('user') === '1';
    }

    protected function shouldUnlockForUser(User $user): bool
    {
        return true;
    }
}
