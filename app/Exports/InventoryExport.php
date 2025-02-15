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

class InventoryExport implements WithMultipleSheets
{
    use Exportable;


    protected $store;
    protected $inventories;
    protected $inventory_io;
    protected $inventory_delta;

    protected $year;

    public function __construct(int $year = null)
    {
        
        $this->store = Store::find(1);

        if($year == null){
            $this->year = date('Y');
        }
    }
    
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new InventoryPerMonthExport($this->year, 9);
        $sheets[] = new InventoryDeltaPerMonthExport($this->year, 9);


        return $sheets;
    }

}
