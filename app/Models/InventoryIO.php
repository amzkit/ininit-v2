<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryIO extends Model
{
    use HasFactory;
    protected $table = 'inventory_io';
    protected $fillable = [
        'id',
        'store_id',
        'product_id',
        'io_amount',
        'note',
    ];

    protected $appends = [
    ];

    protected $attributes = [
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function getIOByDate($date=null)
    {

        if(!isset($date)){
            $date = \Carbon\Carbon::today();
            $date = $date->format('Y-m-d');
        }

        $result = InventoryIO::whereDate('created_at','=', $date)->get();

        return $result;
    }

    public static function getIONoteByDate($date=null)
    {
        $ios = self::getIOByDate($date);

        $result = [];

        foreach($ios as $io){
            $temp = '';
            $temp .= '[' . $io->product->sku . '] ';
            $temp .= $io->io_amount . ' ';
            $temp .= $io->note;
            array_push($result, $temp);
        }

        return $result;
    }

    public function fromCustomer()
    {
        return $this->belongsTo(Customer::class, 'from_customer_id');
    }

    public function fromAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'from_address_id');
    }

    public function toCustomer()
    {
        return $this->belongsTo(Customer::class, 'to_customer_id');
    }

    public function toAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'to_address_id');
    }
}
