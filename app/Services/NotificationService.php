<?php

namespace App\Services;

use App\Actions\SendBulkNotificationsAction;
use App\Actions\SendSingleNotificationAction;
use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        protected SendBulkNotificationsAction $sendBulkAction,
        protected SendSingleNotificationAction $sendSingleAction
    ) {}

    /** Запуск массовой рассылки уведомлений через Action */
    public function sendBulk(array $data): Collection
    {
        return $this->sendBulkAction->execute($data);
    }

    /** Отправка одиночного уведомления через Action */
    public function send(Notification $notification): void
    {
        $this->sendSingleAction->execute($notification);
    }
}
