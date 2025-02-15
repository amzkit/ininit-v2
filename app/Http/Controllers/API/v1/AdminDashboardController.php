<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;

use App\Models\AdminStore;
use App\Models\User;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\InventoryIO;
use App\Models\Product;
use DateInterval;
use Illuminate\Support\Facades\DB;
use stdClass;

class AdminDashboardController extends Controller
{
    private $store;

    public function __construct()
    {
        $this->store = Store::find(1);
    }

    public static function middleware(): array
    {
        return [
            new Middleware('VerifyStore'),
        ];
    }


    public function inventory_delta(Request $request)
    {
        /*
        // Today Delta
        $today = $request->get('date')??now();//date('Y-m-d', strtotime($request->get('date')??now()));

        if($request->get('date_2'))
            $yesterday = $request->get('date_2');
        else if($request->get('date'))
            $yesterday = date('Y-m-d',strtotime("today"));
        else
            $yesterday = date('Y-m-d',strtotime("-1 days"));

        $today = "2024-09-22";
        $yesterday = "2024-09-21";

        $query = array();
        array_push($query, ['store_id', '=', $this->store->id]);
        //array_push($query, ['created_by_user_id', '=', auth()->user()->id]);
        array_push($query, ['created_at', ">=", date('Y-m-d 00:00:00', strtotime($today))]);
        array_push($query, ['created_at', "<=", date('Y-m-d 23:59:59', strtotime($today))]);
        //array_push($query, ['enabled', '=', 1]);
        $today_inventories = Inventory::where($query)->get();

        //$today_inventory_count = $today_inventories->count();
        //dd($today_inventories);

        $query = array();
        array_push($query, ['store_id', '=', $this->store->id]);
        //array_push($query, ['created_by_user_id', '=', auth()->user()->id]);
        array_push($query, ['created_at', ">=", date('Y-m-d 00:00:00', strtotime($yesterday))]);
        array_push($query, ['created_at', "<=", date('Y-m-d 23:59:59', strtotime($yesterday))]);

        $yesterday_inventories = Inventory::where($query)->get();
        
        // initial product sku list
        $inventory = [];
        $products = Product::where([['store_id', '=', $this->store->id]])->get();
        foreach($products as $product){
            $sku = $product->sku;
            if(!array_key_exists($sku, $inventory)){
                $inventory[$sku] = [];
            }
        }

        foreach($today_inventories as $inv){
            $sku = $inv->product->sku;
            array_push($inventory[$sku], $inv->inventory);
        }

        foreach($yesterday_inventories as $inv){
            $sku = $inv->product->sku;
            array_push($inventory[$sku], $inv->inventory);
        }

        $inventory_delta = [];
        foreach($inventory as $sku=>$inv){
            if(count($inv) > 1){
                $inventory_delta[$sku] = $inv[1]-$inv[0];
            }
        }
        */
        ////////////////////////////////

        $date_range = new \App\Models\Utility\DateRange();
        $date_from = $date_range->date_from;
        $date_to = $date_range->date_to;
        ////////////////////////////////
        $query = array();
        array_push($query, ['store_id', '=', $this->store->id]);
        //array_push($query, ['created_by_user_id', '=', auth()->user()->id]);
        array_push($query, ['created_at', ">=", date('Y-m-d 00:00:00', strtotime($date_from))]);
        array_push($query, ['created_at', "<=", date('Y-m-d 23:59:59', strtotime($date_to))]);
        
        $inventories = [];
        $inventory_io = [];
        $inventory_delta = [];
        $products = Product::where('store_id', $this->store->id)
            ->orderBy('sku', 'ASC')
//            ->select('sku')
            ->pluck('id','sku')
            ->toArray();

        foreach($products as $sku=>$product_id){
            if(!array_key_exists($sku, $inventories)){
                $inventories[$sku] = [];
                $inventory_io[$sku] = [];
                $inventory_delta[$sku] = [];
            }
        }

        //array_push($query, ['enabled', '=', 1]);
/*
        $dates = Inventory::where($query)
            ->orderBy('created_at', 'ASC')
            ->select(DB::raw('Date(created_at) as date'))
            ->groupBy('date')
            ->pluck('date')
            ->toArray();
*/
        $dates = [];
        $current_date = strtotime($date_from);
        
        // Add first previous day for yesterday-current_day data
        $dates[] = date('Y-m-d', strtotime('-1 day', $current_date));

        while($current_date <= strtotime($date_to)){
            $dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }

        $yesterday_value = 0;

        foreach($products as $sku=>$product_id){
            foreach($dates as $date){
                $inv_value = 0;
                $io_value = 0;
                $inventory = Inventory::where([
                    ['store_id', '=', $this->store->id],
                    ['product_id', '=', $product_id],
                ])->whereDate('created_at', '=', $date)->first();

                if(isset($inventory)){
                    $inv_value = $inventory->inventory;
                    $yesterday_value = $inv_value;
                }else{
                    // No inventory data recorded, use yesterday data (probaby a weekend or holiday) 
                    $inv_value = $yesterday_value;
                }

                $io = InventoryIO::where([
                    ['store_id', '=', $this->store->id],
                    ['product_id', '=', $product_id],
                ])->whereDate('created_at', '=', $date)->first();

                if(isset($io)){
                    $io_value = $io->io_amount;
                }

                array_push($inventories[$sku], [\Carbon\Carbon::parse($date)->timestamp * 1000, $inv_value]);
                array_push($inventory_io[$sku], [\Carbon\Carbon::parse($date)->timestamp * 1000, $io_value]);
            }
        }

        foreach($inventories as $sku=>$inventory){
            for($i=0; $i<count($dates)-1;$i++){

                // Yesterday inv [0] - Today inv = delta [1]
                $delta = $inventory[$i][1]-$inventory[$i+1][1];
                $io = $inventory_io[$sku][$i+1][1];
                if($io > 0){
                //    dd($delta, $io->io_amount);
                    $delta += $io;
                }
                
                //if($delta < 0) $delta = 0;
                array_push($inventory_delta[$sku], [$inventory[$i+1][0], $delta]);
            }
        }

        // Delta SUM/AVG
        // delta sum for each date
        $inventory_delta['sum'] = [];
        
        // For a glance
        // delta sum for all skus and dates
        $total_delta_sum = 0;
        // Max delta [ date, value ]
        $max_delta = [null, 0];

        for($i=0;$i<count($dates)-1;$i++){
            $delta_sum = 0;
            foreach($products as $sku=>$product_id){
                $delta_sum += $inventory_delta[$sku][$i][1];
            }
            $total_delta_sum += $delta_sum;

            // Max Delta
            if($max_delta[1] < $delta_sum) $max_delta = [$dates[$i+1], $delta_sum];

            array_push($inventory_delta['sum'], [$inventory_delta[$sku][$i][0], $delta_sum]);
        }

        $sku_list = array_keys($products);
        array_push($sku_list, 'sum');

        $glances = [
            'total_delta_sum'   =>  $total_delta_sum,   // Delta sum of all skus and dates
            'avg_delta'         =>  number_format((float)($total_delta_sum / count($dates)), 2, '.', ''),
            'max_delta'         =>  $max_delta,
        ];

        return response()->json([
            'success'           =>  true,
            'sku_list'          =>  $sku_list,
            'inventories'       =>  $inventories,
            'inventory_delta'   =>  $inventory_delta,
            'inventory_io'      =>  $inventory_io,
            'dates'             =>  $dates,
            'glances'           =>  $glances,
        ]);
    }
}
