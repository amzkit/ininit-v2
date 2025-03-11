<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Models\Partner;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class PartnerController extends Controller
{
    protected $store_id;

    public function __construct()
    {
        // Middleware ensures the user is authenticated before accessing any function
        $this->store_id = auth()->user()->store->id;

    }

    // 🔹 Search partners by name or mobile number (Filtered by store_id)
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        $partners = Partner::where('store_id', $this->store_id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('mobile_number', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($partners);
    }

    // 🔹 Create a new partner (Auto-assign store_id)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mobile_number' => 'nullable|string|max:20|unique:partners,mobile_number',
            ]);

            $partner = Partner::create([
                'store_id' => $this->store_id,  // Auto-assign store_id
                'name' => $validated['name'],
                'mobile_number' => $validated['mobile_number'] ?? null,
            ]);

            return response()->json([
                'message' => 'Partner created successfully',
                'partner' => $partner
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    // 🔹 Update partner details (Only for the logged-in user's store)
    public function update(Request $request, $id)
    {
        try {
            $partner = Partner::where('store_id', $this->store_id)->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mobile_number' => 'nullable|string|max:20|unique:partners,mobile_number,' . $partner->id,
            ]);

            $partner->update($validated);

            return response()->json([
                'message' => 'Partner updated successfully',
                'partner' => $partner
            ]);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
