<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

/** Заполнение базы данных тестовыми уведомлениями */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        Notification::factory()->count(50)->create();
    }
}
