<?php

namespace App\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Log;

class SmsProvider implements ProviderInterface
{
    public function send(string $recipient, string $content): array
    {
        // Симуляция сетевой задержки
        usleep(rand(50000, 200000));

        // Симуляция случайного сбоя (шанс 10%)
        if (rand(1, 10) === 1) {
            Log::error("SmsProvider: Failed to send to {$recipient}");
            throw new Exception("Sms gateway timeout");
        }

        Log::info("SmsProvider: Sent successfully to {$recipient}");

        return [
            'provider' => 'sms_mock',
            'id' => uniqid('sms_'),
            'status' => 'delivered',
            'timestamp' => now()->toIso8601String()
        ];
    }
}
