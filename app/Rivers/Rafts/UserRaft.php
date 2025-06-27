<?php

namespace App\Rivers\Rafts;

use App\Models\User;
use LsvEu\Rivers\Contracts\ModelRaft;

class UserRaft extends ModelRaft
{
    protected static string $modelClass = User::class;

    protected array $properties = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'email',
    ];
}
