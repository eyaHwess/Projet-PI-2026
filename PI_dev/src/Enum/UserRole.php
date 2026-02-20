<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'ROLE_USER';
    case COACH = 'ROLE_COACH';
    case ADMIN = 'ROLE_ADMIN';
}
