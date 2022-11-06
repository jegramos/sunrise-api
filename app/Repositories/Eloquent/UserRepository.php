<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /** @inheritDoc */
    public function all(array $filters = null, bool $paginated = false): array
    {
        return User::with('userProfile')->get()->toArray();
    }

    /** @inheritDoc */
    public function create(array $userInfo): array
    {
        return [];
    }

    /** @inheritDoc */
    public function read($id): array
    {
        return [];
    }

    /** @inheritDoc */
    public function update($id, array $newUserInfo): array
    {
        return [];
    }

    /** @inheritDoc */
    public function destroy($id): array
    {
        return [];
    }
}