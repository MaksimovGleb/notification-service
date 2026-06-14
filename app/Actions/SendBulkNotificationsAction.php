<?php

namespace App\Actions;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/** Выполнение массовой рассылки уведомлений */
class SendBulkNotificationsAction
{
    public function execute(array $data): Collection
    {

        $externalId = $data['external_id'] ?? null;
        $priority = $data['priority'] ?? 'normal';

        // Проверка идемпотентности через Redis
        if ($externalId) {
            $lockKey = "notification_lock:{$externalId}";
            if (!Redis::set($lockKey, 'processing', 'EX', 60, 'NX')) {
                throw new \RuntimeException("Duplicate request detected.", 409);
            }
        }

        return DB::transaction(function () use ($data, $externalId, $priority) {
            $notifications = collect();

            foreach ($data['recipients'] as $recipient) {
                $uniqueId = $externalId ? "{$externalId}_{$recipient}" : null;

                $notification = Notification::firstOrCreate(
                    ['external_id' => $uniqueId],
                    [
                        'channel' => $data['channel'],
                        'recipient' => $recipient,
                        'content' => $data['content'],
                        'priority' => $priority,
                        'status' => NotificationStatus::QUEUED,
                    ]
                );

                if ($notification->wasRecentlyCreated || !$uniqueId) {
                    $notifications->push($notification);

                    // Отправка в RabbitMQ с учетом приоритета
                    $queue = $notification->priority === NotificationPriority::HIGH ? 'high' : 'default';
                    SendNotificationJob::dispatch($notification->id)->onQueue($queue);
                }
            }

            return $notifications;
        });
    }
}
