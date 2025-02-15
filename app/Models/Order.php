<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Payment;
use App\Models\Product;

use App\Traits\Uuids;
use Vinkla\Hashids\Facades\Hashids;

class Order extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'order_id',
        'store_id',
        'created_by_user_id',
        'child_order',
        'enable',
        'pre_order',
        'status',
        'order_channel',
        'customer_id',
        'shipment_id',
        'payment_id',
        'items',
        'details',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'items'     =>  'array',
        'details'   =>  'array',
    ];

    protected $date = ['created_at'];

    protected $attributes = [
        //    'item_label',
        //    'shipping_address'
    ];

    protected $appends = [
        'initiated',   //a way round to load relationship, customer, payment, shipment
        'item_label',
        'shipping_address',
        'step',
        'slug',
        'payment_channel',
        'isSlipExisted',
        'isShipmentExisted',
    ];

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        // customer_id in orders table
        return $this->belongsTo(Customer::class);
    }

    public function shipment()
    {
        // order_id in shipments table
        return $this->hasOne(Shipment::class);
    }

    public function payment()
    {
        // order_id in payments table
        $payment = $this->hasOne(Payment::class);
        return $payment;
    }

    public function getInitiatedAttribute()
    {
        // For relationship
        $this->customer;
        $this->payment;
        $this->shipment;

        return true;
    }

    public function getSlugAttribute()
    {
        return Hashids::connection('order')->encode($this->store_id, $this->order_id);
    }

    public static function findBySlug($slug)
    {
        $ids = Hashids::connection('order')->decode($slug);
        if(!isset($ids) || count($ids) < 1){
            return null;
        }
        $store_id = $ids[0];
        $store_order_id = $ids[1];


        $order = Order::where([
            ['store_id', '=', $store_id],
            ['order_id', '=', $store_order_id]
        ])->first();

        return $order;
    }

    public function getItemLabelAttribute()
    {
        $item_label = '';
        try {
            foreach ($this->items as $i => $item) {
                //dd($item['product']);

                if ($i != array_key_first($this->items)) {
                    $item_label .= ', ';
                }
                $sku = $item['product']['sku'] ?? $item['sku']?? '';
                $amount = $item['amount'];
                $item_label .= $sku . '=' . $amount;
            }
        } catch (\Exception $e) {
            //dd($e);
            //return "generate_item_label_error, " . $e;
            return false;
        }
        return $item_label;
    }

    public function getShippingAddressAttribute()
    {
        $shipment = $this->shipment;
        if ($shipment) {
            $shipping_address = $shipment->shipping_address_string;
            /*
            $shipping_address = $shipment->shipping_address;

            unset($shipping_address['id']);
            unset($shipping_address['customer_id']);
            unset($shipping_address['created_at']);
            unset($shipping_address['updated_at']);

            $shipping_address = implode(' ', array_values($shipping_address));
            $shipping_address = preg_replace('!\s+!', ' ', $shipping_address);
            */
            return $shipping_address;
        }
        //return "shipment_not_found_error";
        return false;
    }

    public function getStepAttribute()
    {
        //$statuses = Util::getValidOrderStatuses();
        //$step = array_search($this->status, $statuses);
        $step = $this->status;
        //dd($statuses, $step, $this->status);
        return $step;
    }

    public function getIsSlipExistedAttribute()
    {
        $existed = false;
        if (!$this->payment) {
            return null;
        }

        if ($this->payment->slip) {
            $existed = true;
        }
        return $existed;
    }

    public function getIsShipmentExistedAttribute()
    {
        $existed = false;
        if ($this->shipment) {
            $existed = true;
        }
        return $existed;
    }

    public function getPaymentChannelAttribute()
    // Get Static data from order object, not via eloquent relationship
    {
        //return null;
        //dd($this, $this->payment->payment_channel);
        if (!$this->payment && isset($this->details['parent_id'])) {
            $payment_channel = Order::find($this->details['parent_id'])->payment_channel;
            return $payment_channel;
        } else if (!$this->payment) {
            return null;
        }

        //return $this->payment->payment_channel;
        //dd($this->payment);

       // if(!isset($this->payment->payment_channel['id'])){
            //dd($this->payment);

    //        $payment_channel = null;
    //    }else{

        $payment_channel = PaymentChannel::find($this->payment->payment_channel['id']??$this->payment->payment_channel[0]['id']);//dd($this->payment));
    //    }
        return $payment_channel;
    }

    public function getIsCODAttribute()
    {
        if(isset($this->payment_channel)){
            //if(!isset($this->payment_channel['type'])) dd($this->payment_channel);
            if(isset($this->payment_channel['type'])){
                if($this->payment_channel['type'] === 'cash_on_delivery'){
                    return true;
                }
            }elseif(isset($this->payment_channel[0]['type'])){
                if ($this->payment_channel[0]['type'] === 'cash_on_delivery') {
                    return true;
                }
            }
        }
        /*
        if (isset($this->details['payment_type']) && $this->details['payment_type'] === "cash_on_delivery") {
            return true;
        }*/
        return false;
    }

    public static function prepareFrontEndOrder(Order $order){
        $temp = array();
        $temp['id']         =   $order->id;
        $temp['enabled']    =   $order->enabled;
        $temp['step']       =   $order->step;
        $temp['status']     =   $order->status;
        $temp['preorder']   =   $order->preorder;
        $temp['isCOD']      =   $order->isCOD;
        $temp['slug']       =   $order->slug;
        $temp['child_order']    =   $order->child_order;
        $temp['parent_order_id']    =   $order->details['parent_order_id']??null;
        $temp['created_at'] =   date('d M H:i',strtotime($order->created_at));
        $temp['order_id']   =   $order->order_id;

        $temp['customer_name']  = $order->customer->name??'ไม่มีข้อมูล';
        $temp['mobile_number']  = $order->customer->mobile_number??'ไม่มีข้อมูล';
        $temp['shipping_address']   =   $order->shipping_address??'ไม่มีข้อมูล';

        $temp['status_color']   = Util::getStatusColor($order->status);
        $temp['status_text']    = Util::getStatusText($order->status);

        // Line My Shop
        //$temp['linemyshop_order_id'] = $order->details['linemyshop_order_id']??null;
        if(isset($order->details['linemyshop_order_id'])) $temp['linemyshop_order_id'] = $order->details['linemyshop_order_id']??null;

        // External Platform Referral
        if(isset($order->details['platform'])) $temp['platform'] = $order->details['platform']??null;
        if(isset($order->details['referer'])) $temp['referer'] = $order->details['referer']??null;
        if(isset($order->details['platform_order_id'])) $temp['platform_order_id'] = $order->details['platform_order_id']??null;



        // Payment
        $temp['payment']    =   array();//$order->payment;
        if(!$order->payment_channel)
            return null;
        $temp['payment']['payment_channel'] =  $order->payment_channel->id;//$order->payment_channel->id??$order->payment_channel['id']; //
        //$temp['payment']['shipping_cost']      =   $order->payment->shipping_cost?number_format($order->payment->shipping_cost).'.-':'';
        //$temp['payment']['transaction_fee']    =   $order->payment->transaction_fee?number_format($order->payment->transaction_fee).'.-':'';
        //$temp['payment']['subtotal']           =   number_format($order->payment->subtotal).'.-';
        //$temp['payment']['discount']           =   $order->payment->discount?number_format($order->payment->discount).'.-':'';
        //$temp['payment']['total']           =   number_format($order->payment->total).'.-';
        $temp['payment']['shipping_cost']      =   $order->payment->shipping_cost?number_format($order->payment->shipping_cost):'';
        $temp['payment']['transaction_fee']    =   $order->payment->transaction_fee?number_format($order->payment->transaction_fee):'';
        $temp['payment']['subtotal']           =   number_format($order->payment->subtotal);
        $temp['payment']['discount']           =   $order->payment->discount?number_format($order->payment->discount):'';
        $temp['payment']['total']           =   number_format($order->payment->total);
        $temp['payment']['slip']            =   $order->payment->slip;
        $temp['payment']['slip_date']       =   $order->payment->slip_datetime!=null?date('Y-m-d', strtotime($order->payment->slip_datetime)):null;
        $temp['payment']['slip_time']       =   $order->payment->slip_datetime!=null?date('H:i', strtotime($order->payment->slip_datetime)):null;


        $temp['payment_channel'] = $order->payment->payment_channel;
        // Option Payment Channel
        $option_payment_channels = array();

        $payment_channel_temp = array(); //$payment_channel;
        //dd($order->payment_channel);
        $payment_channel_temp['id'] = $order->payment_channel->id;
        $payment_channel_temp['account_name'] = $order->payment_channel->account_name;
        $payment_channel_temp['account_number'] = $order->payment_channel->account_number;
        $payment_channel_temp['code'] = $order->payment_channel->code;
        $payment_channel_temp['type'] = $order->payment_channel->type;
        $payment_channel_temp['text'] = $order->payment_channel->type=='cash_on_delivery'?'COD '.strtoupper($order->payment_channel->code).' ':'';
        //$payment_channel_temp['text'] .= $order->payment_channel->account_name . ' (' . $order->payment_channel->account_number . ')';
        $payment_channel_temp['value'] = $order->payment_channel->id;
        array_push($option_payment_channels, $payment_channel_temp);
        $payment_channels = PaymentChannel::where([
            ['store_id', '=', $order->store_id]
        ])->get();

        foreach($payment_channels as $payment_channel){
            $payment_channel_temp = array(); //$payment_channel;
            $payment_channel_temp['id'] = $payment_channel->id;
            $payment_channel_temp['account_name'] = $payment_channel->account_name;
            $payment_channel_temp['account_number'] = $payment_channel->account_number;
            $payment_channel_temp['code'] = $payment_channel->code;
            $payment_channel_temp['type'] = $payment_channel->type;
            $payment_channel_temp['text'] = $payment_channel->type=='cash_on_delivery'?'COD '.strtoupper($payment_channel->code).' ':'';
            //$payment_channel_temp['text'] .= $payment_channel->account_name . ' (' . $payment_channel->account_number . ')';
            $payment_channel_temp['value'] = $payment_channel->id;
            array_push($option_payment_channels, $payment_channel_temp);
        }

        $temp['option_payment_channels'] = $option_payment_channels;

        // Item
        $item_count = 0;
        $items = array();
        foreach($order->items as $item){
            $item_count += $item['amount'];
            $product_temp = array();
            $product_temp['name'] = $item['product']['name']??'';
            $product_temp['sku'] = $item['sku']??'';
            $product_temp['image'] = $item['product']['image']??null;
            //$product_temp['full_price'] = number_format($item['product']['full_price']).'.-';
            //$product_temp['price'] = number_format($item['product']['price']).'.-';
            $product_temp['full_price'] = number_format($item['product']['full_price']??0);
            $product_temp['price'] = number_format($item['product']['price']??0);
            $product_temp['amount'] = $item['amount'];
            $product_temp['product'] = $item['product'];
            array_push($items, $product_temp);
        }
        $temp['product_image']  = $order->items[0]['product']['image']??null;

        $temp['items']      =   $items;
        $temp['item_count'] =   $item_count;
        $temp['item_label'] =   $order->item_label;

        $temp['shipment']   =   $order->shipment;
        
        return $temp;
    }
}
