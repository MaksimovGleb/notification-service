<?php

namespace App\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Log;

class EmailProvider implements ProviderInterface
{
    public function send(string $recipient, string $content): array
    {
        // Симуляция сетевой задержки
        usleep(rand(100000, 500000));

        // Симуляция случайного сбоя (шанс 10%)
        if (rand(1, 10) === 1) {
            Log::error("EmailProvider: Failed to send to {$recipient}");
            throw new Exception("Temporary provider error");
        }

        Log::info("EmailProvider: Sent successfully to {$recipient}");

        return [
            'provider' => 'email_mock',
            'id' => uniqid('email_'),
            'status' => 'delivered',
            'timestamp' => now()->toIso8601String()
        ];
    }
}
