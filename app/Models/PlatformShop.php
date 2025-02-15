<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Uuids;

class PlatformShop extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'store_id',
        'platform_id',
        'shop_name',
    ];
}
