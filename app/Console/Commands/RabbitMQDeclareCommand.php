<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

/** Команда для декларации очередей RabbitMQ */
class RabbitMQDeclareCommand extends Command
{
    protected $signature = 'rabbitmq:declare {--purge : Purge existing queues}';

    protected $description = 'Declare RabbitMQ queues from configuration';

    public function handle(): void
    {
        $this->info('Declaring RabbitMQ queues...');

        $queues = ['default', 'high'];
        $connection = 'rabbitmq';
        $purge = $this->option('purge');

        foreach ($queues as $queue) {
            try {
                // Get the RabbitMQ connection instance
                $conn = Queue::connection($connection);

                // внутренний метод драйвера для объявления очереди без добавления сообщения.
                if (method_exists($conn, 'declareQueue')) {
                    $conn->declareQueue($queue);
                    $this->info("Queue '{$queue}' declared.");
                } else {
                    // если метод не существует (хотя он должен существовать)
                    $this->warn("Method declareQueue not found, skipping explicit declaration.");
                }

                if ($purge && method_exists($conn, 'purgeQueue')) {
                    $conn->purgeQueue($queue);
                    $this->info("Queue '{$queue}' purged.");
                }
            } catch (\Exception $e) {
                $this->error("Failed to declare queue '{$queue}': " . $e->getMessage());
            }
        }

        $this->info('RabbitMQ queues declaration finished.');
    }
}
