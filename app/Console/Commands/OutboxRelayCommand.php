<?php

namespace App\Console\Commands;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class OutboxRelayCommand extends Command
{
    protected $signature = 'outbox:relay';

    protected $description = 'Переотправка уведомлений, застрявших в статусе queued (Transactional Outbox Relay)';

    public function handle(): void
    {
        $this->info('Запуск Outbox Relay...');

        // Находим уведомления, которые "застряли" в статусе QUEUED (например, созданы более 5 минут назад)
        // Это может произойти, если транзакция в БД прошла успешно, но RabbitMQ был недоступен
        $stuckNotifications = Notification::where('status', NotificationStatus::QUEUED)
            ->where('created_at', '<', Carbon::now()->subMinutes(5))
            ->limit(100)
            ->get();

        if ($stuckNotifications->isEmpty()) {
            $this->info('Застрявших уведомлений не найдено.');
            return;
        }

        foreach ($stuckNotifications as $notification) {
            // Определяем очередь на основе приоритета
            $queue = $notification->priority === NotificationPriority::HIGH ? 'high' : 'default';

            // Повторно отправляем задачу в очередь RabbitMQ
            SendNotificationJob::dispatch($notification->id)->onQueue($queue);

            $this->line("Уведомление #{$notification->id} повторно отправлено в очередь '{$queue}'.");
        }

        $this->info("Успешно переотправлено уведомлений: {$stuckNotifications->count()}.");
    }
}
