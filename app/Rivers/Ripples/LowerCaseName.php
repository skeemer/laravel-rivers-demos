<?php

namespace App\Rivers\Ripples;

use App\Models\User;
use App\Rivers\Rafts\UserRaft;
use LsvEu\Rivers\Cartography\Ripple;

class LowerCaseName extends Ripple
{
    public function process(?UserRaft $user = null): void
    {
        User::whereId($user->id)->update(['name' => str($user->name)->lower()]);
    }
}
