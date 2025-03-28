<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\TelegramNotification;

class Notification extends Model
{
    //
    public $channel;
    public $message;
    public $store_id;

    public function __construct()
    {        
        $this->store_id = auth()->user()->store->id;
    }

    public function inventory($date = null)
    {
        $this->channel = 'inventory';
        $store_id = $this->store_id;
        $sum_array = [];
    
        // Convert the date to Y-m-d format if provided
        $formattedDate = $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : null;
    
        // Function to fetch inventory and calculate IO for different SKUs
        function getInventoryData($sku_list, $store_id, $date) {
            $inventoryData = [];
            $ioData = [];
    
            $last = [];
            foreach ($sku_list as $sku) {
                $inv = Inventory::getInventoryFromSKU($sku, $store_id, $date);
                $inventoryData[] = $inv;
                $lastInv = Inventory::getLastInventoryFromSKU($sku, $store_id, $date);
                $io = $inv - ($lastInv + InventoryIO::getIOBySKUDate($store_id, $sku, $date));
                $ioData[] = $io;

                $last[] = $lastInv;
            }
            dd($inventoryData, $ioData, $last);
            return [$inventoryData, $ioData];
        }
    
        // 📌 Women's Collection
        $girl_sku = ['bonnie', 'sexy', 'picnic', 'wood', 'blooming'];
        [$girls, $io_girls] = getInventoryData($girl_sku, $store_id, $formattedDate);
        array_push($sum_array, array_sum($girls));
    
        $msg = date("d/m/Y", strtotime($date ?: 'today')) . "\n\n";
        $msg .= 'คอลผู้หญิง' . "\n";
        $msg .= '• Bonnie Bo   ' . $girls[0] .' ('.$io_girls[0].')'. "\n";
        $msg .= '• Sexy           ' . $girls[1].' ('.$io_girls[1].')'. "\n";
        $msg .= '• Sweetie       ' . $girls[2].' ('.$io_girls[2].')'. "\n";
        $msg .= '• Wood sand  ' . $girls[3].' ('.$io_girls[3].')'. "\n";
        $msg .= '• Blooming     ' . $girls[4].' ('.$io_girls[4].')'. "\n";
        $msg .= 'รวม       ' . $sum_array[0] . "\n\n";
    
        // 📌 Books Collection
        $book_sku = ['charming', 'martini'];
        [$books, $io_books] = getInventoryData($book_sku, $store_id, $formattedDate);
        array_push($sum_array, array_sum($books));
    
        $msg .= 'คอลหนังสือ' . "\n";
        $msg .= '• Charming  ' . $books[0] .' ('.$io_books[0].')'. "\n";
        $msg .= '• Martini       ' . $books[1] .' ('.$io_books[1].')'. "\n";
        $msg .= 'รวม       ' . $sum_array[1] . "\n\n";
    
        // 📌 Men's Collection
        $men_sku = ['tender', 'onyx', 'exceed'];
        [$men, $io_men] = getInventoryData($men_sku, $store_id, $formattedDate);
        array_push($sum_array, array_sum($men));
    
        $msg .= 'คอลผู้ชาย' . "\n";
        $msg .= '• Tender     '. $men[0] .' ('.$io_men[0].')'. "\n";
        $msg .= '• Onyx        '. $men[1] .' ('.$io_men[1].')'. "\n";
        $msg .= '• Exceed    '. $men[2] .' ('.$io_men[2].')'. "\n";
        $msg .= 'รวม       ' . $sum_array[2] . "\n\n";
    
        // 📌 Girls Material Collection
        $material_sku = ['dreamy', 'passion', 'kiss', 'mine'];
        [$materials, $io_materials] = getInventoryData($material_sku, $store_id, $formattedDate);
        array_push($sum_array, array_sum($materials));
    
        $msg .= 'คอลเกิร์ลแมททีเรียล' . "\n";
        $msg .= '• Dreamy      '. $materials[0] .' ('.$io_materials[0].')'. "\n";
        $msg .= '• Passion       '. $materials[1] .' ('.$io_materials[1].')'. "\n";
        $msg .= '• Kiss me       '. $materials[2] .' ('.$io_materials[2].')'. "\n";
        $msg .= '• Mine Wish  '. $materials[3] .' ('.$io_materials[3].')'. "\n";
        $msg .= 'รวม       ' . $sum_array[3] . "\n\n";
    
        $msg .= 'รวมทั้งหมด ' . array_sum($sum_array) . "\n\n";
    
        // 📌 PRW Collection
        $prw_sku = ['prw_cof_300g', 'prw_cof_100g', 'prw_tam_100g'];
        $prw = array_map(fn($sku) => Inventory::getInventoryFromSKU($sku, $store_id, $formattedDate), $prw_sku);
        array_push($sum_array, array_sum($prw));
    
        $msg .= 'สครับ' . "\n";
        $msg .= '• กาแฟใหญ่  '. $prw[0] . "\n";
        $msg .= '• กาแฟเล็ก  '. $prw[1] . "\n";        
        $msg .= '• มะขามเล็ก  '. $prw[2] . "\n\n";
    
        // 📌 Balance Collection
        $balance_sku = ['sunscreen_balance', 'booster_balance'];
        $balance = array_map(fn($sku) => Inventory::getInventoryFromSKU($sku, $store_id, $formattedDate), $balance_sku);
        array_push($sum_array, array_sum($balance));
    
        $msg .= 'Balance' . "\n";
        $msg .= '• กันแดด    '. $balance[0] . "\n";
        $msg .= '• บูสเตอร์    '. $balance[1] . "\n\n";
    
        // 📌 Fetch IO Notes for the Specified Date
        $ios = \App\Models\InventoryIO::getIONoteByDate($formattedDate);
        if (count($ios) > 0) {
            $msg .= date("d/m/Y", strtotime($date ?: 'today')) . ' สินค้าเข้าออก' . "\n\n";
            foreach ($ios as $io) {
                $msg .= $io . "\n";
            }
            $msg .= "\n";
        }
    
        // 📌 Add Inventory Details Link
        $msg .= 'รายละเอียด : https://ininit.com/inventory';
    
        // Assign and Send Notification
        $this->message = $msg;
        //dd($this->message);
        $this->notify();
    }
    

    public function notify()
    {

        $chatId = env('TELEGRAM_CHAT_ID', null);

        $groupChats = TelegramChat::where([
            ['store_id', $this->store_id],
            ['chat_type', 'group'],
            ['channel', $this->channel]
        ])->pluck('chat_id');
        
        if(isset($groupChats)){
            //$chatId = $groupChats->chat_id;
        }

        \Illuminate\Support\Facades\Notification::send(
            new self(['chat_id' => $chatId, 'message' => $this->message]),
            new TelegramNotification($this->message)
        );
    }
}
