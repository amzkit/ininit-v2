<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'alias',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
