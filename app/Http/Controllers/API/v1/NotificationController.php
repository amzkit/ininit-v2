<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;


class NotificationController extends Controller
{
    //
    public function inventory(Request $request)
    {
        $date = $request->input('date'); // Get the date from the request
    
        $notification = new Notification();
        $notification->inventory($date); // Pass the date to the inventory method
    
        return response()->json([
            'success' => true,
            'status' => 'Notification sent',
            'date' => $date ?? 'today', // Return the date in the response
        ]);
    }
    
}
