<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Uuids;
use Vinkla\Hashids\Facades\Hashids;

class AdminStore extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'default',
        'user_id',
        'store_id',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
