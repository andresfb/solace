<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

class RandomUserSelectorService
{
    private readonly int $limitDataset;

    private readonly int $maxRuns;

    private int $checks = 20;

    private int $runs = 1;

    public function __construct()
    {
        $this->limitDataset = config('settings.random_users_limit');
        $this->maxRuns = $this->checks * 5;
    }

    public function getUser(): User
    {
        $this->runs++;

        $user = $this->getPostingWeightedUsers()
            ->shuffle()
            ->first();

        if ($user !== null) {
            return $user;
        }

        if ($this->runs > $this->maxRuns) {
            throw new \RuntimeException('No user found.');
        }

        $this->checks--;

        if ($this->checks <= 0) {
            $this->checks = 20;
            Cache::forget("weighted:users:$this->checks");
        }

        return $this->getUser();
    }

    public function getPostingWeightedUsers(int $count = 20): Collection
    {
        return Cache::remember("weighted:users:posters:$count", now()->addMinutes(30), function () use ($count) {
            $lowPostUsers = User::withCount('posts')
                ->whereHas('profile', fn ($query) => $query->where('humanoid', false))
                ->orderBy('posts_count')
                ->limit($this->limitDataset)
                ->get();

            $highPostUsers = User::withCount('posts')
                ->whereHas('profile', fn ($query) => $query->where('humanoid', false))
                ->orderBy('posts_count', 'desc')
                ->limit($this->limitDataset)
                ->get();

            if ($lowPostUsers->isEmpty() && $highPostUsers->isEmpty()) {
                return $this->getRandomUsers($count);
            }

            if ($lowPostUsers->isEmpty()) {
                $lowPostUsers = $this->getRandomDbUsers();
            }

            if ($highPostUsers->isEmpty()) {
                $highPostUsers = $this->getRandomDbUsers();
            }

            // Define selection sizes
            $totalSelection = $count; // Total users to return
            $lowPostCount = round($totalSelection * 0.6); // 60% from low-post users
            $highPostCount = $totalSelection - $lowPostCount; // 40% from high-post users

            // Randomly sample from each group
            $selectedLowPostUsers = $lowPostUsers->shuffle()->take($lowPostCount);
            $selectedHighPostUsers = $highPostUsers->shuffle()->take($highPostCount);

            // Merge and Return or use the final collection
            return $selectedLowPostUsers->merge($selectedHighPostUsers)->unique();
        });
    }

    /**
     * @throws RandomException
     */
    public function getRandomUsers(int $count): Collection
    {
        return $this->getRandomDbUsers()
            ->shuffle()
            ->take($count);
    }

    /**
     * @throws RandomException
     */
    private function getRandomDbUsers(): Collection
    {
        $totalUsers = User::count();
        $randomOffset = random_int(0, max(0, $totalUsers - $this->limitDataset));
        return User::skip($randomOffset)->take($this->limitDataset)->get();
    }
}
