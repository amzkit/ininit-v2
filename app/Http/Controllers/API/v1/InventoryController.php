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
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    private $store;

    public function __construct(Request $request)
    {
        $this->store = Store::find($request->attributes->get('store_id'));
    }

    public function index(Request $request)
    {
        // Get selected date, default to today
        $selectedDate = $request->date ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();
        $yesterday = $selectedDate->copy()->subDay();
    
        // Fetch all products for the current store
        $products = Product::where('store_id', $this->store->id)
            ->where('stock_enabled', true)
            ->orderBy('sort_order', 'ASC')
            ->get();
    
        // Attach Yesterday and Selected Date Inventory to each product
        foreach ($products as $product) {
            // Selected Date Inventory
            $selected_inventory = Inventory::where('product_id', $product->id)
                                           ->where('store_id', $this->store->id)
                                           ->whereDate('created_at', $selectedDate)
                                           ->first();
            
            $product->selected_inventory = $selected_inventory->inventory ?? 0;
    
            // Yesterday's Inventory
            $yesterday_inventory = Inventory::where('product_id', $product->id)
                                            ->where('store_id', $this->store->id)
                                            ->whereDate('created_at', $yesterday)
                                            ->first();
    
            $product->yesterday_inventory = $yesterday_inventory->inventory ?? 0;
        }
    
        $inventory_ios = InventoryIO::where('store_id', $this->store->id)
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'asc')
            ->with('product', 'partner') // Load product and partner data
            //->with('product', 'partner') // โหลดข้อมูลสินค้าและพาร์ทเนอร์
            ->get();



        return response()->json([
            'success' => true,
            'date' => $selectedDate->format('Y-m-d'),
            'products' => $products,
            'inventory_ios' => $inventory_ios]);
    }

    public function update(Request $request)
    {
        $product_id = $request->product_id;
        $inventory = $request->inventory;
        $date = $request->date??\Carbon\Carbon::today();
    
        // Get or create today's inventory record using `created_at`
        $inv = Inventory::where('product_id', $product_id)
                        ->where('store_id', $this->store->id)
                        ->whereDate('created_at', $date)
                        ->first();

        // Create a new record if today's inventory does not exist
        if (!$inv) {
            $inv = new Inventory();
            $inv->product_id = $product_id;
            $inv->store_id = $this->store->id;
        }

        // Update the inventory value
        $inv->inventory = $inventory;
        $inv->created_at = $date;
        $inv->save();
        
    
        return response()->json(['success' => true, 'inventory' => $inv]);
    }

    public function history(Request $request)
    {
        $month = $request->month ?? \Carbon\Carbon::now()->format('Y-m');

        $io_invs = InventoryIO::where('store_id', $this->store->id)
                            ->where('created_at', 'like', $month . '%')
                            ->orderBy('created_at', 'ASC')
                            ->get();

        $history = [];
        foreach ($io_invs as $io) {
            $date = $io->created_at->format('Y-m-d');
            $sku = $io->product->sku;

            if (!isset($history[$date])) {
                $history[$date] = [
                    'created_at' => $date,
                    'details' => []
                ];
            }

            $history[$date]['details'][] = [
                'sku' => $sku,
                'product_name' => $io->product->name,
                'io_amount' => $io->io_amount,
                'note' => $io->note
            ];
        }

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function in_out(Request $request)
    {
        $product_id = $request->product_id;
        $partner_id = $request->partner_id;
        $io_amount = $request->io_amount;
        $note = $request->note;
        $date = $request->date??\Carbon\Carbon::today();
    
        // Create Inventory IO Entry
        $inv_io = new InventoryIO;
        $inv_io->store_id = $this->store->id;
        $inv_io->product_id = $product_id;
        $inv_io->io_amount = $io_amount;
        $inv_io->type = $io_amount > 0 ? 'in' : 'out';
        $inv_io->partner_id = $partner_id;
        $inv_io->note = $note;
        $inv_io->save();
    
        // Update today's Inventory
        $inventory = Inventory::firstOrNew([
            'product_id' => $product_id,
            'store_id' => $this->store->id,
            'created_at' => $date
        ]);
    
        $inventory->inventory += $io_amount;
        $inventory->save();
    
        return response()->json(['success' => true, 'io_inventory' => $inv_io]);
    }

    public function in_out_update(Request $request)
    {
        $product = $request->product;
        $io_amount = $request->io_amount;
        $note = $request->note;
        $id = $request->in_out_id;
        $date = $request->date;

        $inv_io = InventoryIO::find($id);
        if (!$inv_io) {
            return response()->json(['success' => false, 'message' => 'io_not_found']);
        }

        // Calculate the difference
        $difference = $io_amount - $inv_io->io_amount;

        // Update Inventory IO
        $inv_io->io_amount = $io_amount;
        $inv_io->note = $note;
        $inv_io->save();

        // Adjust Inventory
        $inventory = Inventory::firstOrNew([
            'product_id' => $product['id'],
            'store_id' => $this->store->id,
            'created_at' => \Carbon\Carbon::createFromFormat('Y-m-d', $date)
        ]);

        $inventory->inventory += $difference;
        $inventory->save();

        return response()->json(['success' => true, 'io_inventory' => $inv_io]);
    }

    public function saveOrder(Request $request)
    {
        $orderedIds = $request->ordered_ids;
        foreach ($orderedIds as $index => $id) {
            Product::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

}