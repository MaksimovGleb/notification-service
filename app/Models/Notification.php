<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'external_id',
        'channel',
        'recipient',
        'content',
        'status',
        'priority',
        'provider_response',
    ];

    protected $casts = [
        'status' => NotificationStatus::class,
        'priority' => NotificationPriority::class,
        'provider_response' => 'array',
    ];
}
