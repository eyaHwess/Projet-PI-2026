<?php

namespace App\Enum;

enum ReclamationStatusEnum: string
{
    case PENDING = 'Pending';
    case IN_PROGRESS = 'In progress';
    case ANSWERED = 'Answered';
    case CLOSED = 'Closed';
}
