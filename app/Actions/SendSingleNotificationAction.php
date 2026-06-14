<?php

namespace App\Actions;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\Providers\EmailProvider;
use App\Services\Providers\ProviderInterface;
use App\Services\Providers\SmsProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendSingleNotificationAction
{
    /** Получение экземпляра провайдера по каналу */
    protected function getProvider(string $channel): ProviderInterface
    {
        return match ($channel) {
            'email' => new EmailProvider(),
            'sms' => new SmsProvider(),
            default => throw new InvalidArgumentException("Unsupported channel: {$channel}"),
        };
    }

    /** Логика отправки уведомления и обновления его состояния */
    public function execute(Notification $notification): void
    {
        // Проверка статуса для обеспечения Exactly-once на уровне воркера
        if (in_array($notification->status, [NotificationStatus::SENT, NotificationStatus::DELIVERED])) {
            Log::info("Notification #{$notification->id} already processed. Skipping.");
            return;
        }

        try {
            $provider = $this->getProvider($notification->channel->value ?? $notification->channel);
            $response = $provider->send($notification->recipient, $notification->content);

            // Если провайдер вернул 'delivered', устанавливаем соответствующий статус
            $status = ($response['status'] ?? '') === 'delivered' 
                ? NotificationStatus::DELIVERED 
                : NotificationStatus::SENT;

            $notification->update([
                'status' => $status,
                'provider_response' => $response,
            ]);

            Log::info("Notification #{$notification->id} sent successfully with status: {$status->value}.");

        } catch (Exception $e) {
            Log::error("Failed to send notification #{$notification->id}. Error: " . $e->getMessage());

            $notification->update([
                'status' => NotificationStatus::FAILED,
                'provider_response' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }
}
