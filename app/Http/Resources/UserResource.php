<?php

namespace App\Http\Resources;

use App\Models\User;

class UserResource
{

    public static function for( ?User $user ): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

}
