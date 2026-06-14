<?php

namespace App\Services\Providers;

interface ProviderInterface
{
    /**
     * Отправить уведомление.
     * Реализовано для SMS и Email.
     * 
     * @param string $recipient Получатель
     * @param string $content Содержимое
     * @return array Ответ от провайдера
     * @throws \Exception
     */
    public function send(string $recipient, string $content): array;
}
