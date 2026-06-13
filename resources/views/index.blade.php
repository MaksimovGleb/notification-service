<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Service API</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 40px auto; padding: 0 20px; color: #333; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 15px; overflow-x: auto; border-radius: 5px; }
        .endpoint { border-left: 5px solid #3490dc; padding-left: 15px; margin-bottom: 30px; }
        .method { font-weight: bold; color: #3490dc; }
    </style>
</head>
<body>
    <h1>Микросервис уведомлений</h1>
    <p>Система для асинхронной обработки и отправки уведомлений (SMS, Email).</p>

    <h2>API Эндпоинты</h2>

    <div class="endpoint">
        <p><span class="method">POST</span> <code>/api/notifications/bulk</code></p>
        <p>Массовая рассылка уведомлений.</p>
        <pre>
{
    "external_id": "string",
    "channel": "email|sms",
    "priority": "low|normal|high",
    "content": "string",
    "recipients": ["string"]
}</pre>
    </div>

    <div class="endpoint">
        <p><span class="method">GET</span> <code>/api/notifications/history/{recipient}</code></p>
        <p>История уведомлений.</p>
    </div>
</body>
</html>
