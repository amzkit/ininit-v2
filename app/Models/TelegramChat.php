<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class TelegramChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store_id',
        'user_id',
        'chat_id',
        'chat_type', // 'private' or 'group'
        'channel'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function routeNotificationForTelegram()
    {
        return $this->chat_id;
    }
}
