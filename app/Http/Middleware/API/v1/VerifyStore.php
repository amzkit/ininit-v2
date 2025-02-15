<?php

namespace App\Http\Middleware\API\v1;

use Closure;
use Illuminate\Http\Request;

class VerifyStore
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //$store = $request->user()->stores;

        /*
        if ($request->store_id) {
            $store = $request->user()->stores->where('id', $request->store_id)->first();
        } else {
            $store = $request->user()->stores->first();
        }
        */
        $store = auth()->user()->store??null;
        if ($store === null) {
            return response()->json(['error' => 'store not found, please create store first'])->setStatusCode(412);
            //return redirect('/store');
        }

        // Incase converting user_store_id to store_id
        $request->attributes->add(['store_id' => $store->id]);
        return $next($request);
    }
}
