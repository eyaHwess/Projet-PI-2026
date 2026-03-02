<?php

namespace App\Enum;

enum ReclamationTypeEnum: string
{
    case BUG = 'Bug';
    case COACHING = 'Coaching';
    case PAYMENT = 'Payment';
    case OTHER = 'Other';
}
