<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Uuids;

class Packing extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'store_id',
        'platform_shop_id',
        'shipping_company_id',
        'type', // type=0 => printing, type=1 => packing
        'round',
        'quantity',
        'details',
    ];

    protected $appends = [
    //    'initiated',
    ];

    public function getInitiatedAttribute()
    {
        // For relationship
        $this->platform_shop;

        return true;
    }

    public function platform_shop()
    {
        return $this->belongsTo(PlatformShop::class, 'platform_shop_id');
    }

    public static function getHistory_v1($store_id, $date=null)
    {
        if(!isset($date)) $date = date('Y-m-d');//\Carbon\Carbon::today(); //
        $packing = [];
        // Type = 0 -> printing, Type = 1 -> packing
        for($type=0; $type<=1;$type++){
            // Get existing shipping company
            $existing_shipping_companies = Packing::where([
                ['store_id','=', $store_id],
                ['type','=', $type],
                ])->whereDate('created_at', $date)
                ->select('shipping_company_id')
                ->groupBy('shipping_company_id')
                ->orderBy('shipping_company_id', 'ASC')
                ->get();
            $shipping_companies = [];
            foreach($existing_shipping_companies as $company){
                array_push($shipping_companies, $company->shipping_company_id);
            }
            //dd($shipping_companies);

            $max_round = Packing::where([
                ['store_id','=', $store_id],
                ['type','=', $type],
                ])->whereDate('created_at', $date)
                ->max('round')??0;

            $packing_temp = [];

            foreach($shipping_companies as $company){
                $existing_platform_shops = Packing::where([
                    ['store_id','=', $store_id],
                    ['type','=', $type],
                    ])->whereDate('created_at', $date)
                    ->select('platform_shop_id')
                    ->groupBy('platform_shop_id')
                    ->get();
                    
                //dd($existing_platform_shops);
                $temp = [];

                foreach($existing_platform_shops as $platform_shop){
                    $shop_name = $platform_shop->platform_shop->shop_name;
                    $packing_temp[$company][$shop_name] = [];
                    for($i=1;$i<=$max_round;$i++){
                        $existing_packings = Packing::where([
                            ['store_id','=', $store_id],
                            ['type','=', $type],
                            ['platform_shop_id', '=', $platform_shop->platform_shop->id],
                            ['shipping_company_id', '=', $company],
                            ['round', '=', $i],
                            ])->whereDate('created_at', $date)
                            ->first();
                            //->get();
                        
                        //if($company == 'jandt' && $shop_name == 'itsskin_byprw'){
                        //    dd($i, $shop_name, $platform_shop->platform_shop->id, $existing_packings);
                        //}

                        //dd($shop_name, $existing_packings);
                        // Not found skip
                        if(!isset($existing_packings)) continue;
                        $quantity = $existing_packings->quantity;
                        //array_push($temp, [$shop_name=>$quantity]);
                        array_push($packing_temp[$company][$shop_name], $quantity);
                        //if($company == 'jandt' && $shop_name == 'itsskin_byprw' && $quantity==0){
                            //dd($i, $shop_name, $platform_shop->platform_shop->id, $quantity);
                            //dd($packing_temp);
                        //}
                        //if($company == 'jandt' && $shop_name == 'itsskin_byprw'){
                            //dd($i, $shop_name, $platform_shop->platform_shop->id, $existing_packings->quantity);
                        //    dd($packing_temp);
                        //}

                        //foreach($existing_packings as $packing){
                        //    $quantity = $packing->quantity??0;
                        //    array_push($packing_temp[$company][$shop_name], $quantity);
                        //}
                    }

                }
                //if($company == 'jandt'){
                    //dd($temp);
                //}
            }

            if($type == 0){
                $packings['prints'] = $packing_temp;
                $packings['max_round'][0] = $max_round;
            }else{
                $packings['packs'] = $packing_temp;
                $packings['max_round'][1] = $max_round;
            }
        }
        return $packings;
    }

    public static function getMaxRound($store_id, $date=null)
    {
        if(!isset($date)) $date = date('Y-m-d');//\Carbon\Carbon::today(); //

        $print_max_round = Packing::where([
            ['store_id','=', $store_id],
            ['type','=', 0],
            ])->whereDate('created_at', $date)
            ->max('round')??0;
        $pack_max_round = Packing::where([
            ['store_id','=', $store_id],
            ['type','=', 1],
            ])->whereDate('created_at', $date)
            ->max('round')??0;
        return [$print_max_round, $pack_max_round];
    }

    public static function getHistory($store_id, $date=null)
    {
        if(!isset($date)) $date = date('Y-m-d');//\Carbon\Carbon::today(); //
        $packings = [0=>[], 1=>[]];
        $packing_ids = [0=>[], 1=>[]]; // Packing IDs => for update packing
        // Type = 0 -> printing, Type = 1 -> packing
        $existing_packings = Packing::where([
            ['store_id','=', $store_id],
            ])->whereDate('created_at', $date)
            ->orderBy('shipping_company_id', 'ASC')
            ->orderBy('round', 'ASC')
            ->get();

        foreach($existing_packings as $packing){
            //$type = $packing->type==0?'prints':'packs';
            $type = $packing->type;

            // Shipping Company
            // $packings['prints']['ems']
            $shipping_company = $packing->shipping_company_id;
            if(!array_key_exists($shipping_company, $packings[$type])){
                $packings[$type][$shipping_company] = [];
                $packing_ids[$type][$shipping_company] = [];
            }

            // Shop Name
            // $packings['prints']['ems']['itsskin_byprw']
            $shop_name = $packing->platform_shop->shop_name;
            $shop_name = '['.config('settings.platforms')[$packing->platform_shop->platform_id]['en'].'] '.$shop_name;
            if(!array_key_exists($shop_name, $packings[$type][$shipping_company])){
                $packings[$type][$shipping_company][$shop_name] = [];
                $packing_ids[$type][$shipping_company][$shop_name] = [];

            }

            // Quantity
            //  Value is [PACKING_ID, QUANTITY] ==> PACKING_ID is used for edit and updating
            // $packings['prints']['ems']['itsskin_byprw'] = ['PACKING_ID', 10], '2'=>['PACKING_ID', 20]]
            // = [$round => $quantity]
            $round = $packing->round;
            $quantity = $packing->quantity;
            if(!array_key_exists($round, $packings[$type][$shipping_company][$shop_name])){
                // $packings['prints']['ems']['itsskin_byprw'] = ['1'=> ['PACKING_ID', 10], '2'=>['PACKING_ID', 20]]
                $packings[$type][$shipping_company][$shop_name][$round] = $quantity;
                $packing_ids[$type][$shipping_company][$shop_name][$round] = $packing->id;

            }
        }
        
        return [$packings, $packing_ids];
    }

    public static function getTemplate($store_id){
        $template = [];

        //platform_shop_id: null,
        //quantity: 0,
        
        //$last_date = date('Y-m-d',strtotime("-1 days"));
        $last_date = Packing::latest()->first();
        if(isset($last_date))
            $last_date = $last_date->created_at;
        else
            return $template;

        $existing_packings = Packing::where([
            ['store_id','=', $store_id],
            ])->whereDate('created_at', $last_date)
            ->orderBy('shipping_company_id', 'ASC')
            ->orderBy('round', 'ASC')
            ->get();

        foreach($existing_packings as $packing){

            // Create shipping company keys
            // $template['ems'], $template['flash']
            $shipping_company = $packing->shipping_company_id;
            if(!array_key_exists($shipping_company, $template)){
                $template[$shipping_company] = [];
            }
            
            // Create platform_shop entry
            $platform_shop_id = $packing->platform_shop_id;
            if(!str_contains(json_encode($template[$shipping_company]), $platform_shop_id)){
                array_push($template[$shipping_company], ['platform_shop_id'=>$platform_shop_id, 'quantity'=>0]);
            }
            //if(!array_key_exists($platform_shop_id, $template[$shipping_company])){
            //    array_push($template[$shipping_company], ['platform_shop_id'=>$platform_shop_id, 'quantity'=>0]);
            //}
        }
        
        return $template;
    }


    public static function getQuantity($store_id, $shipping_company_id, $platform_shop_id, $type, $date){
        if(!isset($date)) $date = date('Y-m-d');//\Carbon\Carbon::today(); //

        $quantity = Packing::where([
            ['store_id','=', $store_id],
            ['shipping_company_id','=', $shipping_company_id],
            ['platform_shop_id','=', $platform_shop_id],
            ['type','=', $type],
            ])->whereDate('created_at', $date)
            ->select('quantity')
            ->sum('quantity');

        return $quantity;

    }

}
