<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

use App\Models\TelegramChat;

class TelegramNotification extends Notification
{
    use Queueable;
    
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        // Get the latest chat_id from telegram_chats table

        
        $chat = TelegramChat::where('user_id', $notifiable->id)->latest()->first();
        /*
        if (!$chat) {
            return null; // No Telegram chat found for this user
        }
        */
 
        $chat_id = $chat->chat_id??env('TELEGRAM_CHAT_ID');

        return TelegramMessage::create()
            ->to($chat_id)
            ->content($this->message);
    }
}
