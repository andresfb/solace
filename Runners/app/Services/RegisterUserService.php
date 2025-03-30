<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProfileGender;
use App\Models\Profiles\Profile;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Common\Dtos\RandomUserItem;
use Modules\Common\Traits\Screenable;
use Throwable;

class RegisterUserService
{
    use Screenable;

    public function __construct(protected readonly ProfileImageGenService $imageGenService) {}

    /**
     * @throws Throwable
     */
    public function execute(RandomUserItem $userItem): void
    {
        $commit = false;

        try {
            if (User::where('username', $userItem->username)->exists()) {
                $message = "User with username '$userItem->username' already exists.";

                $this->line($message);
                Log::notice($message);

                return;
            }

            $this->line('Creating User '.$userItem->username);

            DB::beginTransaction();
            $commit = true;

            $user = User::create([
                'name' => $userItem->name,
                'username' => $userItem->username,
                'email' => $userItem->email,
                'password' => bcrypt($userItem->password),
                'email_verified_at' => $userItem->registered->addMinutes(random_int(1, 30)),
                'created_at' => $userItem->registered,
            ]);

            $this->line('Creating Profile...');

            $profile = Profile::create([
                'user_id' => $user->id,
                'gender' => ProfileGender::fromString($userItem->gender),
                'city' => $userItem->city,
                'country' => $userItem->country,
                'phone' => $userItem->phone,
                'birthday' => $userItem->dob,
            ]);

            if (! blank($userItem->picture)) {
                $this->line('Saving Picture...');

                $profile->addMediaFromUrl($userItem->picture)
                    ->toMediaCollection('avatar');

                return;
            }

            $this->line('Generating Avatar...');

            $image = $this->imageGenService->generateImage($userItem->email);
            if ($image === '' || $image === '0') {
                return;
            }

            $this->line('Saving avatar to profile...');

            $profile->addMedia($image)
                ->toMediaCollection('avatar');
        } catch (Exception|Throwable $e) {
            $commit = false;

            $this->error($e->getMessage());

            Log::error($e->getMessage());

            DB::rollBack();
        } finally {
            $this->line('');

            if ($commit) {
                DB::commit();
            }
        }
    }
}
