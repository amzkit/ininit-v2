<?php

namespace App\Models;

use App\Models\Salepage;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\Packing;

class LineNotify
{
    public $message;
    public $token;

    public function __construct($token, $img_path = '')
    {
        //$this->message = $message;
        //$this->token = 'xNfpZV82up1GJM1bjR2vSNvhKmyKXH8pYY36oKRa1OH'; //$token;
        $this->token = $token;
    }

    public function salepage_notify($salepage, $order)
    {
        $msg = '';
        $msg .= 'คำสั่งซื้อใหม่ ' . $salepage->name . chr(0x0D) . chr(0x0A);
        $msg .= 'คำสั่งซื้อ : ' . $order->order_id . chr(0x0D) . chr(0x0A);
        $msg .= 'ที่อยู่จัดส่ง : ' . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A) . $order->customer->name . ' (' . $order->customer->mobile_number . ')' . chr(0x0D) . chr(0x0A);
        $msg .= $order->shipping_address . chr(0x0D) . chr(0x0A);
        $msg .= $order->item_label . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        $msg .= 'ยอดสั่งซื้อ : ' . $order->payment->total . 'บาท' . chr(0x0D) . chr(0x0A);
        if ($order->isCOD) {
            $msg .= 'ชำระเงินปลายทาง (COD) : ' . $order->payment->total . chr(0x0D) . chr(0x0A);
        } else {
            $msg .= 'ตรวจสอบสลิป : ' . chr(0x0D) . chr(0x0A) . 'https://ininit.com/order/' . $order->slug;
        }
        $this->message = $msg;

        $this->notify();
    }

    public function facebook_notify($order)
    {
        $msg = '';
        $msg .= $order->order_id . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        $msg .= $order->customer->name . chr(0x0D) . chr(0x0A);
        $msg .= $order->customer->mobile_number . chr(0x0D) . chr(0x0A);
        $msg .= $order->shipping_address . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        $msg .= $order->item_label . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        if ($order->isCOD) {
            $msg .= 'COD : ' . $order->payment->total . '.-' . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        }else{
            $msg .= 'โอนแล้ว ✅ ' . $order->payment->total . '.-' . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        }

        $msg .= 'รายละเอียด : ' . chr(0x0D) . chr(0x0A) . 'https://ininit.com/order/' . $order->slug . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        if (isset($order->payment->slip)) {
            $msg .= 'หลักฐาน : ' . chr(0x0D) . chr(0x0A) . 'https://ininit.com' . $order->payment->slip;
        }

        $this->message = $msg;

        $this->notify();
    }

    public function inventory_notify($store_id)
    {

        $sum_array = [];

        $girl_sku = ['bonnie', 'sexy', 'picnic', 'wood', 'blooming'];
        $girls = [];
        $io_girls = [];
        for($i=0;$i<count($girl_sku);$i++){
            $sku = $girl_sku[$i];
            $inv = Inventory::getInventoryFromSKU($sku, $store_id);
            array_push($girls, $inv);
            $io = $inv - (Inventory::getLastInventoryFromSKU($sku, $store_id) + InventoryIO::getIOBySKUDate($store_id, $sku));
            array_push($io_girls,$io);
        }
        array_push($sum_array, array_sum($girls));
        $msg = date("d/m/Y") . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        $msg .= 'คอลผู้หญิง' . chr(0x0D) . chr(0x0A);
        $msg .= '• Bonnie Bo   ' . $girls[0] .' ('.$io_girls[0].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Sexy           ' . $girls[1].' ('.$io_girls[1].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Sweetie       ' . $girls[2].' ('.$io_girls[2].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Wood sand  ' . $girls[3].' ('.$io_girls[3].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Blooming     ' . $girls[4].' ('.$io_girls[4].')'. chr(0x0D) . chr(0x0A);
        $msg .= 'รวม       ' . $sum_array[0] . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        /*
        $winter_sku = ['cuddle', 'memory'];
        $winters = [];
        for($i=0;$i<count($winter_sku);$i++)
            array_push($winters,Inventory::getInventoryFromSKU($winter_sku[$i], $store_id)); 
        array_push($sum_array, array_sum($winters));
        $msg .= 'คอลหน้าหนาว' . chr(0x0D) . chr(0x0A);
        $msg .= '• Cuddle    ' . $winters[0] . chr(0x0D) . chr(0x0A);
        $msg .= '• Memory    ' . $winters[1] . chr(0x0D) . chr(0x0A);
        $msg .= 'รวม       ' . $sum_array[1] . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);
        */

        //////// BOOK
        $book_sku = ['charming', 'martini'];
        $books = [];
        $io_books = [];
        for($i=0;$i<count($book_sku);$i++){
            $sku = $book_sku[$i];
            $inv = Inventory::getInventoryFromSKU($sku, $store_id);
            array_push($books, $inv);
            $io = $inv - (Inventory::getLastInventoryFromSKU($sku, $store_id) + InventoryIO::getIOBySKUDate($store_id, $sku));
            array_push($io_books,$io);
        }
        array_push($sum_array, array_sum($books));
        $msg .= 'คอลหนังสือ' . chr(0x0D) . chr(0x0A);
        $msg .= '• Charming  ' . $books[0] .' ('.$io_books[0].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Martini       ' . $books[1] .' ('.$io_books[1].')'. chr(0x0D) . chr(0x0A);
        $msg .= 'รวม       ' . $sum_array[1] . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        /////// MEN
        $men_sku = ['tender', 'onyx', 'exceed'];
        $men = [];
        $io_men = [];
        for($i=0;$i<count($men_sku);$i++){
            $sku = $men_sku[$i];
            $inv = Inventory::getInventoryFromSKU($sku, $store_id);
            array_push($men, $inv);
            $io = $inv - (Inventory::getLastInventoryFromSKU($sku, $store_id) + InventoryIO::getIOBySKUDate($store_id, $sku));
            array_push($io_men,$io);
        }
        array_push($sum_array, array_sum($men));
        $msg .= 'คอลผู้ชาย' . chr(0x0D) . chr(0x0A);
        $msg .= '• Tender     '. $men[0] .' ('.$io_men[0].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Onyx        '. $men[1] .' ('.$io_men[1].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Exceed    '. $men[2] .' ('.$io_men[2].')'. chr(0x0D) . chr(0x0A);
        $msg .= 'รวม       ' . $sum_array[2] . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        ///////// GIRLS MATERIALS
        $material_sku = ['dreamy', 'passion', 'kiss', 'mine'];
        $materials = [];
        $io_materials = [];
        for($i=0;$i<count($material_sku);$i++){
            $sku = $material_sku[$i];
            $inv = Inventory::getInventoryFromSKU($sku, $store_id);
            array_push($materials, $inv);
            $io = $inv - (Inventory::getLastInventoryFromSKU($sku, $store_id) + InventoryIO::getIOBySKUDate($store_id, $sku));
            array_push($io_materials,$io);
        }
        array_push($sum_array, array_sum($materials));        
        $msg .= 'คอลเกิร์ลแมททีเรียล' . chr(0x0D) . chr(0x0A);
        $msg .= '• Dreamy      '. $materials[0] .' ('.$io_materials[0].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Passion       '. $materials[1] .' ('.$io_materials[1].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Kiss me       '. $materials[2] .' ('.$io_materials[2].')'. chr(0x0D) . chr(0x0A);
        $msg .= '• Mine Wish  '. $materials[3] .' ('.$io_materials[3].')'. chr(0x0D) . chr(0x0A);
        $msg .= 'รวม       ' . $sum_array[3] . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        $msg .= 'รวมทั้งหมด ' . array_sum($sum_array) . chr(0x0D) . chr(0x0A) . chr(0x0D) . chr(0x0A);

        $prw_sku = ['prw_cof_300g', 'prw_cof_100g', 'prw_tam_100g'];
        $prw = [];
        for($i=0;$i<count($prw_sku);$i++)
            array_push($prw,Inventory::getInventoryFromSKU($prw_sku[$i], $store_id)); 
        array_push($sum_array, array_sum($prw));  
        $msg .= 'สครับ'. chr(0x0D) . chr(0x0A);
        $msg .= '• กาแฟใหญ่  '. $prw[0] . chr(0x0D) . chr(0x0A);
        $msg .= '• การแฟเล็ก  '. $prw[1] . chr(0x0D) . chr(0x0A);        
        $msg .= '• มะขามเล็ก  '. $prw[2] . chr(0x0D) . chr(0x0A);
        $msg .= chr(0x0D) . chr(0x0A);
        $balance_sku = ['sunscreen_balance', 'booster_balance'];
        $balance = [];
        for($i=0;$i<count($balance_sku);$i++)
            array_push($balance,Inventory::getInventoryFromSKU($balance_sku[$i], $store_id)); 
        array_push($sum_array, array_sum($balance));  
        $msg .= 'Balance'. chr(0x0D) . chr(0x0A);
        $msg .= '• กันแดด    '. $balance[0] . chr(0x0D) . chr(0x0A);
        $msg .= '• บูสเตอร์    '. $balance[1] . chr(0x0D) . chr(0x0A);
        $msg .= chr(0x0D) . chr(0x0A);


        $ios = \App\Models\InventoryIO::getIONoteByDate($store_id);

        if(count($ios) > 0){
            $msg .= date("d/m/Y") . ' สินค้าเข้าออก' . chr(0x0D) . chr(0x0A);
            $msg .= chr(0x0D) . chr(0x0A);
            foreach($ios as $io){
                $msg .= $io . chr(0x0D) . chr(0x0A);
            }
            $msg .= chr(0x0D) . chr(0x0A);
        }

        $msg .= 'รายละเอียด : ' . chr(0x0D) . chr(0x0A) . 'https://ininit.com/inventory';

        $this->message = $msg;
        $this->notify();
    }

    public function packing_notify($store_id, $type, $date=null)
    {
        if(!isset($date)) $date = date('Y-m-d');//\Carbon\Carbon::today(); //
        $msg = '';
        if($type==0) $msg .='[พิมพ์]'; else $msg.='[ส่ง]';
        $msg = ' วันที่ '. $date . chr(0x0D) . chr(0x0A). chr(0x0D) . chr(0x0A);

        $packings = [];
        $original_shop_packings = [];

        $custom_order_original_shop_id_list = [
            '2110174d-1ce2-4f40-a411-386470ebe14d', // Tiktok Itsskin
            '5d63482a-de3a-4270-8e30-b89ec1e00175', // Tiktok Byprw Store
            'd3903183-9d47-4096-9c87-72f4a71af341', // Tiktok BYPRW
            '082abfe2-2218-4959-88c0-a5b9a7944d67', // Shopee Itsskin
            'dc9c3ea3-60f4-4c91-b7d2-d6e2ac24e18b', // Shopee Byprw Store
            '40c36c0e-5ab5-465b-9bcb-1fcb74f47d8f', // LAZ Itsskin
            '0799020f-2b7a-4bad-9bcd-7453e0cf604b', // LAZ Byprw Store
            '19ef926a-ecad-4977-a69b-f6467876d1dc', // LAZ BYPRW
        ];
        $shipping_company_collection = Packing::where([
            ['store_id','=', $store_id],
            ['type','=', $type],
            ])->whereDate('created_at', $date)
            ->select('shipping_company_id')
            ->distinct()
            ->orderBy('shipping_company_id', 'ASC')
            ->get();

        if(count($shipping_company_collection) == 0) return null;

        $shipping_companies = [];
        foreach($shipping_company_collection as $shipping_company){
            if(!in_array($shipping_company->shipping_company_id, $shipping_companies)){
                array_push($shipping_companies, $shipping_company->shipping_company_id);
            }
        }
        
        foreach($shipping_companies as $shipping_company_id){
            $platform_shop_collection = Packing::where([
                ['store_id','=', $store_id],
                ['shipping_company_id','=', $shipping_company_id],
                ['type','=', $type],
                ])->whereDate('created_at', $date)
                ->select('platform_shop_id')
                ->distinct()
                ->orderBy('platform_shop_id', 'ASC')
                ->get();
            $platform_shops = [];
            foreach($platform_shop_collection as $platform_shop){
                if(!in_array($platform_shop->platform_shop_id, $platform_shops)){
                    array_push($platform_shops, $platform_shop->platform_shop_id);
                }
            }
            
            usort($platform_shops, function($a, $b) use ($custom_order_original_shop_id_list){

                $aIndex = array_search($a, $custom_order_original_shop_id_list);
                $bIndex = array_search($b, $custom_order_original_shop_id_list);
                
                // ถ้าไม่ได้พบใน $custom_order_shop_id_list จะให้เป็นลำดับที่หลังสุด
                if ($aIndex === false) $aIndex = PHP_INT_MAX;
                if ($bIndex === false) $bIndex = PHP_INT_MAX;
            
                return $aIndex - $bIndex;

            });

            foreach($platform_shops as $platform_shop_id){
                $quantity = Packing::getQuantity($store_id, $shipping_company_id, $platform_shop_id, $type, $date);
                $shop = PlatformShop::find($platform_shop_id);
                $shop_name = config('settings.platforms')[$shop->platform_id]['en'].' | '.$shop->shop_name;

                if(in_array($platform_shop_id, $custom_order_original_shop_id_list)){
                    $original_shop_packings[$shipping_company_id][$shop_name] = $quantity;
                }else{
                    $packings[$shipping_company_id][$shop_name] = $quantity;
                }
            }

        }
        
        // Initial Shipping company sum with 0 for each company
        $shipping_company_sum = [];
        foreach($shipping_companies as $shipping_company){
            $shipping_company_sum[$shipping_company] = 0;
        }

        $original_group_sum = 0;
        foreach ($original_shop_packings as $shipping_company => $each_company) {
            $msg .= strtoupper($shipping_company) . "\n";
            $sum_each = 0;
            foreach ($each_company as $shop_name => $quantity) {
                $sum_each += $quantity;
                $msg .= "• $shop_name = $quantity\n";
            }
            $original_group_sum += $sum_each;
            $shipping_company_sum[$shipping_company] += $sum_each;
            $msg .= "\n";
        }
        $msg .= ">>> รวม $original_group_sum\n__________________________\n";

        $msg .= "กลุ่ม 2\n\n";
        $group_sum = 0;
        foreach ($packings as $shipping_company => $each_company) {
            $msg .= strtoupper($shipping_company) . "\n";
            $sum_each = 0;
            foreach ($each_company as $shop_name => $quantity) {
                $sum_each += $quantity;
                $msg .= "• $shop_name = $quantity\n";
            }
            $group_sum += $sum_each;
            $shipping_company_sum[$shipping_company] += $sum_each;
            $msg .= "\n";
        }
        $msg .= ">>> รวม $group_sum\n__________________________\n\n";

        foreach($shipping_company_sum as $shipping_company => $sum){
            $msg .= "รวม ".strtoupper($shipping_company)." = {$sum}\n";
        }
        
        if($type==0) $msg .="\nพิมพ์"; else $msg.="\nส่ง";
        $msg .= 'รวมทั้งหมด ' . $original_group_sum+$group_sum . chr(0x0D) . chr(0x0A). chr(0x0D) . chr(0x0A);
        
        //dd($packings, $original_shop_packings);
        $this->message = $msg;
        $this->notify();
    }

    public function notify()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        error_reporting(E_ALL);

        date_default_timezone_set("Asia/Bangkok");

        $line_api = 'https://notify-api.line.me/api/notify';

        $headers = array('Method: POST', 'Content-type: multipart/form-data', 'Authorization: Bearer ' . $this->token);

        //$headers = array('Method: POST', 'Content-Type: text/html', 'Authorization: Bearer ' . $this->token);
        $message_data = array(
            'message' => $this->message,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $line_api);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        // Check Error
        if (curl_error($ch)) {
            $result_ = array('status' => '000: send fail', 'message' => curl_error($ch));
        } else {
            $result_ = json_decode($result, true);
        }

        curl_close($ch);
    }
}
