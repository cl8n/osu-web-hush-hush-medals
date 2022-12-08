<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks\HushHush;

use App\Events\ModelCreated;
use App\Listeners\MedalUnlocks\MedalUnlock;
use App\Models\FavouriteBeatmapset;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Favourite10BeatmapsetsByFriends extends MedalUnlock
{
    protected ModelCreated $event;

    public static function getMedalSlug(): string
    {
        return 'my-awesome-secret-medal-2';
    }

    protected function getApplicableUsers(): Collection|User|array
    {
        return $this->event->model->user;
    }

    protected function shouldHandle(): bool
    {
        return $this->event->model instanceof FavouriteBeatmapset;
    }

    protected function shouldUnlockForUser(User $user): bool
    {
        return $user
            ->favourites()
            ->whereHas('beatmapset', function (Builder $query) use ($user) {
                $query->whereIn('user_id', $user->friends->pluck('user_id'));
            })
            ->count() >= 10;
    }
}
