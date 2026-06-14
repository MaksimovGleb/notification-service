<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения задачи */
    public $tries = 3;

    /** Задержка между попытками в секундах */
    public $backoff = [10, 30, 60];

    public function __construct(
        public string $notificationId
    ) {}

    public function handle(NotificationService $service): void
    {
        $notification = Notification::findOrFail($this->notificationId);
        $service->send($notification);
    }
}
