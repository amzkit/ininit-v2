<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\CustomerAddress;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'mobile_number',
    ];

    protected $casts = [];

    public function customer_addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function storeCustomer($customer)
    {
        return 1;
    }
}
