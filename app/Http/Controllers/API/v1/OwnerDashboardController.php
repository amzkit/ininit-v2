<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Routing\Controllers\Middleware;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Store;
use App\Models\AdminStore;
use App\Models\User;
use App\Models\Order;
use App\Models\Packing;

use Carbon\Carbon;
use DateTime;
use stdClass;

class OwnerDashboardController extends Controller
{
    private $store;

    public function __construct(Request $request)
    {
        $this->store = Store::find($request->attributes->get('store_id'));
    }

    public function order_month(Request $request){
        $select_admin_id = $request->select_admin_id??null;

        $current_month = date('m', strtotime(now()));
        $current_year = date('Y', strtotime(now()));
        $previous_month = date('m',strtotime("-1 month"));
        $previous_year_month = date('Y',strtotime("-1 month"));

        if($request->month){
            //$selected_month = substr($request->month, 5, 2);
            //$selected_year = substr($request->month, 0, 4);
            $selected_month = $request->month;
            $selected_year = $request->year;
        }else{
            $selected_month = date('m', strtotime(now()));
            $selected_year = date('Y', strtotime(now()));
            //$selected_month = date('m',strtotime("-1 month"));
            //$selected_year = date('Y',strtotime("-1 month"));
        }

        $date_range = new \App\Models\Utility\DateRange($selected_year, $selected_month);

        $packing_data = [];
        $packing_data = $this->get_data_between($date_range->date_from, $date_range->date_to);
    
        return response()->json([
            'success'=>true,
            'packing_data'      =>  $packing_data,
        ]);
    }
    
    public function get_data_between($date_from, $date_to) {
        $dates = [];
        $current_date = strtotime($date_from);
        $today = Carbon::today();
    
        while ($current_date <= strtotime($date_to)) {
            $dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }
    
        ////////////////////////////////
        // Build query
        $query = [
            ['store_id', '=', $this->store->id],
            ['type', '=', 0],
        ];
    
        // 📌 Get the first day of the selected month and previous month
        $selectedMonthKey = Carbon::parse($date_from)->format('Y-m-01');
        $comparisonMonthKey = Carbon::parse($date_from)->subMonth()->format('Y-m-01');
    
        // 📌 Get Data for the Selected Month
        $packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 Get Data for the Previous Month
        $previous_month_from = Carbon::parse($date_from)->subMonth()->toDateString();
        $previous_month_to = Carbon::parse($date_to)->subMonth()->toDateString();
    
        $previous_packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$previous_month_from, $previous_month_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 Get Data for the Last 7 Days of the Month Before the Previous One (Fallback Data)
        $last_week_of_two_months_ago_from = Carbon::parse($previous_month_from)->subWeek()->toDateString();
        $last_week_of_two_months_ago_to = Carbon::parse($previous_month_from)->subDay()->toDateString();
    
        $fallback_packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$last_week_of_two_months_ago_from, $last_week_of_two_months_ago_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 **Map data by actual date for fast lookup**
        $previousPackingMap = $previous_packings->keyBy('date');
        $fallbackPackingMap = $fallback_packings->keyBy('date');
    
        // 📌 **Build Data Arrays for ApexCharts**
        $selected_data = [];
        $comparison_data = [];
    
        foreach ($dates as $date) {
            // Get this month's value
            $existingData = $packings->firstWhere('date', $date);
            $quantity = $existingData ? (int)$existingData->quantity : 0;
            $selected_data[] = [$date, $quantity];
    
            // Get the **same weekday** in the previous month
            $same_weekday_last_month = Carbon::parse($date)->subMonth();
            while ($same_weekday_last_month->format('l') !== Carbon::parse($date)->format('l')) {
                $same_weekday_last_month->subDay();
            }
            $actualComparisonDate = $same_weekday_last_month->toDateString();
    
            // Get the actual previous month data
            $previousData = $previousPackingMap->get($actualComparisonDate);
    
            if ($previousData) {
                $previousQuantity = (int)$previousData->quantity;
            } else {
                // If the previous month is missing this date, take from the last week of two months ago
                $fallbackData = $fallbackPackingMap->first();
                $previousQuantity = $fallbackData ? (int)$fallbackData->quantity : 0;
    
                // Remove the used fallback data so the next missing date gets the next available fallback
                $fallbackPackingMap->shift();
            }
    
            $comparison_data[] = [$date, $previousQuantity, $actualComparisonDate];
        }
    
        // 📌 **Handle Future Dates (If Current Month)**
        if (Carbon::parse($date_from)->isCurrentMonth()) {
            $last_available_date = collect($selected_data)->last()[0] ?? $date_from;
    
            while (Carbon::parse($last_available_date)->lt(Carbon::parse($date_to))) {
                $last_available_date = Carbon::parse($last_available_date)->addDay()->toDateString();
    
                // Get the same weekday from the previous month
                $same_weekday_last_month = Carbon::parse($last_available_date)->subMonth();
                while ($same_weekday_last_month->format('l') !== Carbon::parse($last_available_date)->format('l')) {
                    $same_weekday_last_month->subDay();
                }
    
                // Get data from previous month for the same weekday
                $previousData = $previousPackingMap->get($same_weekday_last_month->toDateString());
    
                if ($previousData) {
                    $previousQuantity = (int)$previousData->quantity;
                } else {
                    // Fallback: take from the last week of two months ago
                    $fallbackData = $fallbackPackingMap->first();
                    $previousQuantity = $fallbackData ? (int)$fallbackData->quantity : 0;
                }
    
                $comparison_data[] = [$last_available_date, $previousQuantity];
            }
        }
    
        // 📌 **Calculate Analytics (Using Actual Dates)**
        function calculateAnalytics($data, $useActualDates = false) {
            $sum = 0;
            $max_value = PHP_INT_MIN;
            $min_value = PHP_INT_MAX;
            $max_date = "N/A";
            $min_date = "N/A";
            $valid_entries = 0;
    
            foreach ($data as $item) {
                $value = (int)$item[1];
                $actualDate = $useActualDates && isset($item[2]) ? $item[2] : $item[0];
    
                if ($value > 0) { // Ignore zero values
                    $sum += $value;
                    $valid_entries++;
    
                    if ($value > $max_value) {
                        $max_value = $value;
                        $max_date = Carbon::parse($actualDate)->format('D d, M'); // "Thu 10, May"
                    }
                    if ($value < $min_value) {
                        $min_value = $value;
                        $min_date = Carbon::parse($actualDate)->format('D d, M'); // "Thu 10, May"
                    }
                }
            }
    
            // If no valid entries, reset min/max values
            if ($valid_entries === 0) {
                $max_value = 0;
                $min_value = 0;
                $max_date = "N/A";
                $min_date = "N/A";
            }
    
            $average = $valid_entries > 0 ? round($sum / $valid_entries, 2) : 0;
            return compact("sum", "max_value", "max_date", "min_value", "min_date", "average");
        }
    
        return [
            "selected_data" => $selected_data,
            "comparison_data" => $comparison_data,
            "selected_analytics" => calculateAnalytics($selected_data),
            "comparison_analytics" => calculateAnalytics($comparison_data, true),
            "date_comparison_between"   =>  [
                'selected_date'     =>  $selectedMonthKey,
                'comparison_date'   =>  $comparisonMonthKey
            ],

        ];
    }
    
    
    
    public function get_data_between_ai_good_1($date_from, $date_to){
        $dates = [];
        $current_date = strtotime($date_from);
        $today = Carbon::today();
    
        while ($current_date <= strtotime($date_to)) {
            $dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }
    
        ////////////////////////////////
        // Build query
        $query = [
            ['store_id', '=', $this->store->id],
            ['type', '=', 0],
        ];
    
        // 📌 Get the first day of the selected month and previous month
        $selectedMonthKey = Carbon::parse($date_from)->format('Y-m-01'); // e.g., "2025-02-01"
        $comparisonMonthKey = Carbon::parse($date_from)->subMonth()->format('Y-m-01'); // e.g., "2025-01-01"
    
        // 📌 Get Data for the Selected Month
        $packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 Get Data for the Previous Month (By Date Range)
        $previous_month_from = Carbon::parse($date_from)->subMonth()->toDateString();
        $previous_month_to = Carbon::parse($date_to)->subMonth()->toDateString();
    
        $previous_packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$previous_month_from, $previous_month_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 Get Data for the Last 7 Days of the Month Before the Previous One
        $last_week_of_two_months_ago_from = Carbon::parse($previous_month_from)->subWeek()->toDateString();
        $last_week_of_two_months_ago_to = Carbon::parse($previous_month_from)->subDay()->toDateString();
    
        $fallback_packings = Packing::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$last_week_of_two_months_ago_from, $last_week_of_two_months_ago_to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->get();
    
        // 📌 **Build Data Arrays for ApexCharts**
        $selected_data = [];
        $comparison_data = [];
    
        foreach ($dates as $date) {
            if (Carbon::parse($date)->isToday() || Carbon::parse($date)->isPast()) {
                // Get this month's value
                $existingData = $packings->firstWhere('date', $date);
                $quantity = $existingData ? (int)$existingData->quantity : 0;
                $selected_data[] = [$date, $quantity];
    
                // Get the **same weekday** in the previous month
                $same_weekday_last_month = Carbon::parse($date)->subMonth();
                while ($same_weekday_last_month->format('l') !== Carbon::parse($date)->format('l')) {
                    $same_weekday_last_month->subDay(); // Keep moving back until the weekday matches
                }
    
                // Check if we have data for the previous month's same weekday
                $previousData = $previous_packings->firstWhere('date', $same_weekday_last_month->toDateString());
    
                if ($previousData) {
                    $previousQuantity = (int)$previousData->quantity;
                } else {
                    // If missing, take data from the last week of two months ago
                    $fallbackData = $fallback_packings->firstWhere('date', $same_weekday_last_month->toDateString());
                    $previousQuantity = $fallbackData ? (int)$fallbackData->quantity : 0;
                }
    
                $comparison_data[] = [$date, $previousQuantity]; // 🔥 Keep the same date
            }
        }
    
        // 📌 **Handle Future Dates (If Current Month)**
        if (Carbon::parse($date_from)->isCurrentMonth()) {
            $last_available_date = collect($selected_data)->last()[0] ?? $date_from;
    
            while (Carbon::parse($last_available_date)->lt(Carbon::parse($date_to))) {
                $last_available_date = Carbon::parse($last_available_date)->addDay()->toDateString();
    
                // Get the same weekday from the previous month
                $same_weekday_last_month = Carbon::parse($last_available_date)->subMonth();
                while ($same_weekday_last_month->format('l') !== Carbon::parse($last_available_date)->format('l')) {
                    $same_weekday_last_month->subDay();
                }
    
                // Get data from previous month for the same weekday
                $previousData = $previous_packings->firstWhere('date', $same_weekday_last_month->toDateString());
    
                if ($previousData) {
                    $previousQuantity = (int)$previousData->quantity;
                } else {
                    // Fallback: take from the last week of two months ago
                    $fallbackData = $fallback_packings->firstWhere('date', $same_weekday_last_month->toDateString());
                    $previousQuantity = $fallbackData ? (int)$fallbackData->quantity : 0;
                }
    
                $comparison_data[] = [$last_available_date, $previousQuantity];
            }
        }
    
        // 📌 **Return Data with Custom Keys**
        return [
            $selectedMonthKey => $selected_data, // Example: "2025-02-01"
            $comparisonMonthKey => $comparison_data, // Example: "2025-01-01"
        ];
    }
    
    
    
    
    public function get_data_between_manual($date_from, $date_to){
        $dates = [];
        $current_date = strtotime($date_from);

        while($current_date <= strtotime($date_to)){
            $dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }
        ////////////////////////////////
        $query = array();
        array_push($query, ['store_id', '=', $this->store->id]);
        array_push($query, ['type', '=', 0]);
        //array_push($query, ['created_by_user_id', '=', auth()->user()->id]);
        //array_push($query, ['created_at', ">=", date('Y-m-d 00:00:00', strtotime($date_from))]);
        //array_push($query, ['created_at', "<=", date('Y-m-d 23:59:59', strtotime($date_to))]);
        
        $packings = Packing::select(
                //'shipping_company_id',
                DB::raw('DATE(created_at) as date')
            )
            ->selectRaw('SUM(quantity) as quantity')
            ->where($query)
            ->whereBetween('created_at', [$date_from, $date_to])
            //->groupBy('platform_shop_id', 'shipping_company_id', DB::raw('DATE(created_at)'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'ASC')
            ->with('platform_shop')
            ->get();

        $packing_data = [];
        
        foreach($dates as $date){
            // Check if it is the future, do not created data for plotting graph
            if(Carbon::parse($date)->isToday() || Carbon::parse($date)->isPast()){
                $existingData = $packings->firstWhere('date', $date);
                if ($existingData) {
                    // If the date is in the data, add it
                    $packing_data[] = [$date, (int)$existingData->quantity];
                } else {
                    // If the date is missing, add it with quantity 0
                    $packing_data[] = [$date, 0];
                }
            }
        }

        return $packing_data;

    }

    public function prepare_option_list(Request $request)
    {
        $admin_stores = AdminStore::where([['store_id', '=', $this->store->id]])->get();
        $admin_list = [];
        foreach($admin_stores as $admin){
            $temp = ['id'=> $admin->user->id, 'name'=>$admin->user->name];
            array_push($admin_list, $temp);
        }

        $order_channels = config('order.channels');
        $order_channel_list = [];
        foreach($order_channels as $channel){
            $temp = ['id'=>intval($channel['id']), 'text'=>$channel['text']];
            array_push($order_channel_list, $temp);
        }

        $data = [];
        $data['admin_list'] = $admin_list;
        $data['order_channel_list'] = $order_channel_list;
        return $data;
    }

    public function initiate_query(Request $request)
    {
        $query = array();
        array_push($query, ['store_id', '=', $this->store->id]);
        array_push($query, ['enabled', '=', 1]);

        return $query;
    }
}
