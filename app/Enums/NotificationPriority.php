<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
}
