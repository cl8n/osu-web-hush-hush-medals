<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace Tests\MedalUnlocks\HushHush;

use App\Listeners\MedalUnlocks\HushHush\ViewFirstUser;
use Tests\MedalUnlocks\MedalUnlockTestCase;

class ViewFirstUserTest extends MedalUnlockTestCase
{
    protected static function getMedalUnlockClass(): string
    {
        return ViewFirstUser::class;
    }

    public function testUnlock(): void
    {
        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->get(route('users.show', 1));

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlocked();
    }

    public function testWrongUser(): void
    {
        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->get(route('users.show', 2));

        $this->assertMedalUnlockQueued(false);
        $this->assertMedalUnlocked(false);
    }
}
