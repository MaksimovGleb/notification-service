<?php

namespace Database\Factories;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'external_id' => $this->faker->unique()->uuid(),
            'channel' => $this->faker->randomElement(['sms', 'email']),
            'recipient' => $this->faker->randomElement([$this->faker->email(), $this->faker->phoneNumber()]),
            'content' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(NotificationStatus::cases()),
            'priority' => $this->faker->randomElement(NotificationPriority::cases()),
            'provider_response' => null,
        ];
    }
}
