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

class InventoryPerMonthExport implements FromArray, WithTitle
{

    protected $store;
    protected $inventory;
    protected $inventory_raw;
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
        return 'Inventory Month ' . $this->month;
    }

    public function array(): array
    {

        $this->inventory_raw = [];
        $this->inventory_io = [];

        // add empty cell (0,0) for 1st row and 1st column
        //  ['',            'bonnie',   'blooming', ...], // first line
        //  ['2024-09-01,   '10',       '100',      ...],
        //  ['2024-09-02,   '101',       '134',     ...],

        $this->inventory = [['']];

        $products_tmp = Product::where('store_id', $this->store->id)
            ->orderBy('sku', 'ASC')
//            ->select('sku')
            ->pluck('id','sku')
            ->toArray();

        $sku_index = [];
        $current_index = 1;
        foreach($products_tmp as $sku=>$product_id){
            // Ignore dup
            if ( strlen($sku) > 0 && is_numeric($sku[0]) ) {
                unset($products_tmp[$sku]);
                continue;
            }
        }
        
        // Rearrange Product Keys
        $sku_keys = [
            'bonnie', 'sexy', 'blooming', 'picnic', 'wood',
            'dreamy', 'passion', 'mine', 'kiss',
            'charming', 'martini',
            'onyx', 'tender', 'exceed',
            'memory', 'cuddle',
        ];
        $products = [];

        foreach($sku_keys as $sku){
            $products[$sku] = $products_tmp[$sku];
        }

        foreach($products as $sku=>$product_id){
            if(!array_key_exists($sku, $this->inventory_raw)){
                $this->inventory_raw[$sku] = [];
                $this->inventory_io[$sku] = [];

                $sku_index[$sku] = $current_index;
                $current_index++;

                // first line
                $this->inventory[0][] = $sku;
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
        $yesterday_value = [];
        $row_index = 0;
        for($i = 0; $i< count($dates); $i++){
            $this->inventory[] = [$dates[$i]];
            $row_index++;

            // Check if there is io on this date
            $io = InventoryIO::where([
                ['store_id', '=', $this->store->id],
            ])->whereDate('created_at', '=', $dates[$i])->first();

            if(isset($io)){
                // There is io
                

                // Start plotting io
                foreach($products as $sku=>$product_id){
                    $io_value = 0;
                    $io = InventoryIO::where([
                        ['store_id', '=', $this->store->id],
                        ['product_id', '=', $product_id],
                    ])->whereDate('created_at', '=', $dates[$i])->first();

                    if(isset($io)){
                        $note = '';
                        $io_value = $io->io_amount;
                        if($io_value > 0){
                            $io_value = '[+'.$io_value.']';
                            $note = $io->note;
                        }else if($io_value == 0){
                            $io_value = null;
                        }else{
                            $io_value = '['.$io_value.']';
                            $note = $io->note;
                        }

                    }

                    $this->inventory[$row_index][] = $io_value;

                }

                if(isset($note)){
                    $this->inventory[$row_index][] = $note;
                }
                // Add new date line for inventory
                $this->inventory[] = [$dates[$i]];
                $row_index++;

            }


            // Start to plot inv on the date
            foreach($products as $sku=>$product_id){
                $inv_value = 0;
                $inventory = Inventory::where([
                    ['store_id', '=', $this->store->id],
                    ['product_id', '=', $product_id],
                ])->whereDate('created_at', '=', $dates[$i])->first();

                if(isset($inventory)){
                    $inv_value = $inventory->inventory;
                    $yesterday_value[$sku] = $inv_value;
                }else{
                    // No inventory data recorded, use yesterday data (probaby a weekend or holiday) 
                    $inv_value = $yesterday_value[$sku]??0;
                }


                $this->inventory[$row_index][] = $inv_value;
            }
        }
        return $this->inventory;
    }
}
