<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Listeners\MedalUnlocks\HushHush;

use App\Events\ModelCreated;
use App\Events\ModelUpdating;
use App\Listeners\MedalUnlocks\MedalUnlock;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Support\Collection;

class MutualFriend extends MedalUnlock
{
    protected ModelCreated|ModelUpdating $event;

    public static function getMedalSlug(): string
    {
        return 'my-awesome-secret-medal';
    }

    protected function getApplicableUsers(): Collection|User|array
    {
        $relation = $this->event->model;

        return [$relation->user, $relation->target];
    }

    protected function shouldHandle(): bool
    {
        $model = $this->event->model;

        return $model instanceof UserRelation
            && $model->friend
            && ($this->event instanceof ModelCreated || $model->isDirty('friend'))
            && $this->isMutual($model);
    }

    protected function shouldUnlockForUser(User $user): bool
    {
        return true;
    }

    private function isMutual(UserRelation $friendRelation): bool
    {
        return UserRelation
            ::where('friend', true)
            ->where('user_id', $friendRelation->zebra_id)
            ->where('zebra_id', $friendRelation->user_id)
            ->exists();
    }
}
