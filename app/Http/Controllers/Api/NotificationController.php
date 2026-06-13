<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationStoreRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /** Запуск массовой рассылки уведомлений */
    public function sendBulk(NotificationStoreRequest $request, NotificationService $service): JsonResponse
    {
        try {
            $notifications = $service->sendBulk($request->validated());

            return response()->json([
                'message' => 'Notifications queued successfully.',
                'count' => $notifications->count(),
                'external_id' => $request->external_id
            ], 202);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'external_id' => $request->external_id
            ], $e->getCode() ?: 400);
        }
    }

    /** Получение истории уведомлений для конкретного получателя */
    public function getHistory(string $recipient): JsonResponse
    {
        $history = Notification::where('recipient', $recipient)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'recipient' => $recipient,
            'history' => $history
        ]);
    }
}
