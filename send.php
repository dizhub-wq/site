<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
        exit;
    }

    // Отправка в Telegram
    $telegramToken = '8316273787:AAH17QhiEjKW4PafTRAo-b3JGl1lSjxQryw';
    $telegramChatId = '380097556';
    
    $telegramMessage = "🔔 *Новая заявка с сайта*\n\n";
    $telegramMessage .= "👤 *Имя:* $name\n";
    $telegramMessage .= "📧 *Email/Telegram:* $email\n";
    $telegramMessage .= "📱 *Телефон:* $phone\n\n";
    $telegramMessage .= "💬 *Сообщение:*\n$message";
    
    $telegramUrl = "https://api.telegram.org/bot$telegramToken/sendMessage";
    $telegramData = [
        'chat_id' => $telegramChatId,
        'text' => $telegramMessage,
        'parse_mode' => 'Markdown'
    ];
    
    $telegramContext = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($telegramData)
        ]
    ]);
    
    $telegramResult = @file_get_contents($telegramUrl, false, $telegramContext);
    
    if ($telegramResult === false) {
        error_log("Telegram API Error: " . print_r(error_get_last(), true));
        echo json_encode(['success' => false, 'message' => 'Ошибка отправки в Telegram']);
    } else {
        $response = json_decode($telegramResult, true);
        if (isset($response['ok']) && $response['ok']) {
            echo json_encode(['success' => true, 'message' => 'Заявка отправлена']);
        } else {
            $errorMsg = isset($response['description']) ? $response['description'] : 'Неизвестная ошибка';
            error_log("Telegram Response Error: " . $errorMsg);
            echo json_encode(['success' => false, 'message' => 'Ошибка Telegram: ' . $errorMsg]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>