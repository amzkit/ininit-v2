<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\FromArray;

use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryIO;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventoryDeltaPerMonthExport implements FromArray, WithTitle
{

    protected $store;
    protected $inventories;
    protected $inventory_io;
    protected $inventory_delta;

    protected $date_from;
    protected $date_to;

    private $year;
    private $month;

    public function __construct(int $year = null, int $month = null)
    {
        
        $this->store = Store::find(1);

        $this->year = $year;
        $this->month = $month;

        if($year == null){
            $this->year = date('Y');
        }

        if($month == null){
            // date('Y-m-01') -> first day of the month
            // date('Y-m-t') -> last day of the month
            $this->month = date('m');
            $this->date_from = date('Y-m-t', strtotime('-2 month'));
            $this->date_to = date('Y-m-t', strtotime('-1 month'));
        }else if ($year != null && $month != null){
            $this->date_from = date('Y-m-t', strtotime('-1 month', strtotime($year.'-'.sprintf('%02d', $month).'-01')));
            $this->date_to = date('Y-m-t', strtotime($year.'-'.sprintf('%02d', $month).'-01'));
        }
    }

    public function title(): string
    {
        return 'Delta Month ' . $this->month;
    }

    public function array(): array
    {
        
        $this->inventories = [];
        $this->inventory_io = [];

        // add empty cell (0,0) for 1st row and 1st column
        //  ['',            'bonnie',   'blooming', ...], // first line
        //  ['2024-09-01,   '10',       '100',      ...],
        //  ['2024-09-02,   '101',       '134',     ...],

        $this->inventory_delta = [['']];

        $products = Product::where('store_id', $this->store->id)
            ->orderBy('sku', 'ASC')
//            ->select('sku')
            ->pluck('id','sku')
            ->toArray();
            
        $sku_index = [];
        $current_index = 1;
        foreach($products as $sku=>$product_id){
            // Ignore dup
            if ( strlen($sku) > 0 && is_numeric($sku[0]) ) {
                unset($products[$sku]);
                continue;
            }
            if(!array_key_exists($sku, $this->inventories)){
                $this->inventories[$sku] = [];
                $this->inventory_io[$sku] = [];

                $sku_index[$sku] = $current_index;
                $current_index++;

                // first line
                $this->inventory_delta[0][] = $sku;
            }
        }
        $dates = [];
        $current_date = strtotime($this->date_from);
        
        // Add first previous day for yesterday-current_day data

        while($current_date <= strtotime($this->date_to)){
            $dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }
        //  dd($dates);

        // Add first line of the table with sku
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

                array_push($this->inventories[$sku], $inv_value);
                array_push($this->inventory_io[$sku], $io_value);
            }
        }



        // Delta
        for($i=1;$i<count($dates)-1;$i++){
            $this->inventory_delta[] = [$dates[$i]];

            foreach($products as $sku=>$product_id){
                // Yesterday inv [0] - Today inv = delta [1]
                $delta = $this->inventories[$sku][$i]-$this->inventories[$sku][$i+1];
                $io = $this->inventory_io[$sku][$i+1];
                if($io > 0){
                //    dd($delta, $io->io_amount);
                    $delta += $io;
                }
                $this->inventory_delta[$i][] = $delta;

            }

        }

        return $this->inventory_delta;
    }
}
