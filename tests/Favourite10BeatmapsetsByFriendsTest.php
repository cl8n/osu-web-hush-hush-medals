<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace Tests\MedalUnlocks\HushHush;

use App\Listeners\MedalUnlocks\HushHush\Favourite10BeatmapsetsByFriends;
use App\Models\Beatmapset;
use App\Models\User;
use App\Models\UserRelation;
use Tests\MedalUnlocks\MedalUnlockTestCase;

class Favourite10BeatmapsetsByFriendsTest extends MedalUnlockTestCase
{
    private User $friend;

    protected static function getMedalUnlockClass(): string
    {
        return Favourite10BeatmapsetsByFriends::class;
    }

    public function testBeatmapsetNotByFriend(): void
    {
        $this->createFavouritesOfBeatmapsetsByFriends(9);

        $beatmapset = Beatmapset::factory()->create();

        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->post(route('beatmapsets.favourites.store', $beatmapset), [
                'action' => 'favourite',
            ]);

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlocked(false);
    }

    public function testMoreFavourites(): void
    {
        $this->createFavouritesOfBeatmapsetsByFriends(11);

        $beatmapset = Beatmapset::factory()->owner($this->friend)->create();

        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->post(route('beatmapsets.favourites.store', $beatmapset), [
                'action' => 'favourite',
            ]);

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlocked();
    }

    public function testTooFewFavourites(): void
    {
        $this->createFavouritesOfBeatmapsetsByFriends(8);

        $beatmapset = Beatmapset::factory()->owner($this->friend)->create();

        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->post(route('beatmapsets.favourites.store', $beatmapset), [
                'action' => 'favourite',
            ]);

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlocked(false);
    }

    public function testUnlock(): void
    {
        $this->createFavouritesOfBeatmapsetsByFriends(9);

        $beatmapset = Beatmapset::factory()->owner($this->friend)->create();

        $this->resetMedalProgress();

        $this
            ->actingAsVerified($this->user)
            ->post(route('beatmapsets.favourites.store', $beatmapset), [
                'action' => 'favourite',
            ]);

        $this->assertMedalUnlockQueued();
        $this->assertMedalUnlocked();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->friend = User::factory()->create();

        UserRelation::create([
            'user_id' => $this->user->getKey(),
            'zebra_id' => $this->friend->getKey(),
            'friend' => true,
        ]);
    }

    private function createFavouritesOfBeatmapsetsByFriends(int $count): void
    {
        $this->user->favourites()->createMany(
            Beatmapset::factory()
                ->owner($this->friend)
                ->count($count)
                ->create()
                ->pluck('beatmapset_id')
                ->map(fn (int $id) => ['beatmapset_id' => $id]),
        );
    }
}
