<?php

use App\Models\TelegramChat;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\Controller;

class TelegramBotController extends Controller
{
    private $store;

    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);
        
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $chatType = $message->getChat()->getType();

        // Save chat ID if it doesn't exist
        TelegramChat::firstOrCreate(
            ['chat_id' => $chatId],
            ['chat_type' => $chatType]
        );

        return response()->json(['status' => 'Chat ID stored', 'chat_id' => $chatId]);
    }
}
