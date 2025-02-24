<?php

/**
 * Telegram Helper Functions
 * @author A. Kerem Gök
 */

// Telegram Bot Ayarları
define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN'));
define('TELEGRAM_CHAT_ID', env('TELEGRAM_CHAT_ID'));


// Telegram'a mesaj gönderme fonksiyonu
function sendTelegramMessage($message)
{
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        error_log("Telegram bildirimi gönderilemedi");
    }
}
