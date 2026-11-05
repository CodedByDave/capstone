<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** A user's own profile picture. The old file is removed when it is replaced. */
class ProfilePhotoService
{
    private const DISK = 'public';

    public function __construct(private readonly UserRepository $users) {}

    public function update(User $user, UploadedFile $photo): User
    {
        $previous = $user->profile_photo_path;
        $path = $photo->store('profile-photos', self::DISK);

        $this->users->setProfilePhoto($user, $path);

        if ($previous !== null) {
            Storage::disk(self::DISK)->delete($previous);
        }

        return $user->refresh();
    }
}
