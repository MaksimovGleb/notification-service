<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OutboxRelayTest extends TestCase
{
    use DatabaseTransactions;

    public function test_outbox_relay_resends_stuck_notifications(): void
    {
        Queue::fake();

        // Создаем "застрявшее" уведомление (создано 10 минут назад в статусе QUEUED)
        $stuckNotification = Notification::create([
            'channel' => 'email',
            'recipient' => 'stuck@example.com',
            'content' => 'I am stuck',
            'status' => NotificationStatus::QUEUED,
        ]);
        
        // Вручную устанавливаем старую дату, так как она не в fillable
        $stuckNotification->created_at = Carbon::now()->subMinutes(10);
        $stuckNotification->save();

        // Создаем "новое" уведомление
        $newNotification = Notification::create([
            'channel' => 'email',
            'recipient' => 'new@example.com',
            'content' => 'I am new',
            'status' => NotificationStatus::QUEUED,
        ]);

        $this->artisan('outbox:relay')
            ->expectsOutput('Запуск Outbox Relay...')
            ->expectsOutput("Уведомление #{$stuckNotification->id} повторно отправлено в очередь 'default'.")
            ->assertExitCode(0);

        // Проверяем, что в очередь попало только застрявшее уведомление
        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($stuckNotification) {
            return $job->notificationId === $stuckNotification->id;
        });

        Queue::assertNotPushed(SendNotificationJob::class, function ($job) use ($newNotification) {
            return $job->notificationId === $newNotification->id;
        });
    }
}
