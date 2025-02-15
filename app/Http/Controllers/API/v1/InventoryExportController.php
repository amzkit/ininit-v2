<?php

namespace App\Http\Controllers\API\v1;

use App\Exports\InventoryExport;
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
use Maatwebsite\Excel\Facades\Excel;

class InventoryExportController extends Controller
{
    public function export() 
    {
        return Excel::download(new InventoryExport, 'inventory.xlsx');
    }
}