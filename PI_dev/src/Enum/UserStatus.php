<?php

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case BANNED = 'BANNED';
    case PENDING = 'PENDING';
}
