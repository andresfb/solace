<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProfileGender;
use App\Models\Profiles\Profile;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Common\Dtos\RandomUserItem;
use Modules\Common\Traits\Screenable;

class RegisterUserService
{
    use Screenable;

    public function __construct(protected readonly ProfileImageGenService $imageGenService) {}

    /**
     * @throws Exception
     */
    public function execute(RandomUserItem $userItem): void
    {
        try {
            if (User::where('email', $userItem->email)->exists()) {
                $message = "User with email '$userItem->email' already exists.";

                $this->line($message);
                Log::notice($message);

                return;
            }

            $this->line('Creating User '.$userItem->email);

            $user = User::create([
                'name' => $userItem->name,
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
        } finally {
            $this->line('');
        }
    }
}
