<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Customer;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'customer_id',
        'house_number',
        'street_address',
        'subdistrict',
        'district',
        'province',
        'postcode',
    ];

    protected $casts = [
//        'options' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
