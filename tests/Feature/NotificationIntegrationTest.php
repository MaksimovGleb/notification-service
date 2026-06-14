<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class NotificationIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
    }

    public function test_bulk_notification_sending_flow(): void
    {
        Queue::fake();

        $payload = [
            'external_id' => 'order_123',
            'channel' => 'email',
            'content' => 'Your order has been shipped!',
            'recipients' => ['user1@example.com', 'user2@example.com'],
            'priority' => 'high'
        ];

        $response = $this->postJson('/api/notifications/bulk', $payload);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Notifications queued successfully.',
                'count' => 2,
                'external_id' => 'order_123'
            ]);

        $this->assertDatabaseHas('notifications', [
            'external_id' => 'order_123_user1@example.com',
            'recipient' => 'user1@example.com',
            'status' => NotificationStatus::QUEUED,
            'priority' => 'high'
        ]);

        $this->assertDatabaseHas('notifications', [
            'external_id' => 'order_123_user2@example.com',
            'recipient' => 'user2@example.com',
            'status' => NotificationStatus::QUEUED,
            'priority' => 'high'
        ]);

        Queue::assertPushed(SendNotificationJob::class, 2);
        Queue::assertPushedOn('high', SendNotificationJob::class);
    }

    public function test_idempotency_prevents_duplicate_requests(): void
    {
        $payload = [
            'external_id' => 'unique_req_id',
            'channel' => 'sms',
            'content' => 'Test message',
            'recipients' => ['+1234567890'],
        ];

        // Первый запрос
        $this->postJson('/api/notifications/bulk', $payload)->assertStatus(202);

        // Второй запрос с тем же external_id
        $this->postJson('/api/notifications/bulk', $payload)
            ->assertStatus(409)
            ->assertJson(['message' => 'Duplicate request detected.']);
    }

    public function test_notification_status_history(): void
    {
        Notification::create([
            'recipient' => 'test@example.com',
            'channel' => 'email',
            'content' => 'Old message',
            'status' => NotificationStatus::SENT,
        ]);

        $response = $this->getJson('/api/notifications/history/test@example.com');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'history')
            ->assertJsonPath('history.0.content', 'Old message');
    }

    public function test_full_chain_execution(): void
    {
        // Не имитируем очередь (Queue::fake), чтобы протестировать выполнение задачи
        $notification = Notification::create([
            'channel' => 'email',
            'recipient' => 'jobtest@example.com',
            'content' => 'Hello!',
            'status' => NotificationStatus::QUEUED,
        ]);

        $service = $this->createMock(NotificationService::class);
        $service->expects($this->once())
            ->method('send')
            ->with($this->callback(fn($n) => $n->id === $notification->id));

        $job = new SendNotificationJob($notification->id);
        $job->handle($service);
    }
}
