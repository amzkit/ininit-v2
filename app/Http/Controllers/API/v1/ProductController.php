<?php

namespace App\Http\Controllers\API\v1;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Store;
use App\Models\Product;

class ProductController extends Controller
{

    private $store;

    public function __construct(Request $request)
    {
        $this->store = Store::find($request->attributes->get('store_id'));
    }

    // Display a listing of the products
    public function index(Request $request)
    {
        // Initialize query with store_id filtering
        $query = Product::query();

        // Filter by store_id
        if ($request->has('store_id')) {
            $query->where('store_id', $this->store->id);
        }

        // Additional Filtering
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        // Return JSON response
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    // Show the form for creating a new product
    public function create(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku'   =>  'required|string|max:255',
            'image_url' => 'nullable|url',
        ]);

        try {
            $validated['store_id'] = $this->store->id;
            // Get the max product_id of the same store_id and add 1
            $maxProductId = Product::where('store_id', $validated['store_id'])->max('product_id');
            $newProductId = $maxProductId ? $maxProductId + 1 : 1;

            // Set default values for nullable fields
            $validated['category'] = $validated['category'] ?? 'Uncategorized';
            $validated['image_url'] = $validated['image_url'] ?? 'https://via.placeholder.com/150';
            $validated['product_id'] = $newProductId;

            // Create Product
            $product = Product::create($validated);

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            // Error response
            return response()->json([
                'success' => false,
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Find the product
        $product = Product::findOrFail($id);
    
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|url',
            'sku' => 'nullable|string|max:255',
            'alias' => 'nullable|string|max:255',
            'store_id' => 'required|integer|exists:stores,id'
        ]);
    
        try {
            // Check if store_id has changed
            if ($product->store_id != $validated['store_id']) {
                // Generate new product_id for the new store
                $maxProductId = Product::where('store_id', $validated['store_id'])->max('product_id');
                $newProductId = $maxProductId ? $maxProductId + 1 : 1;
                $validated['product_id'] = $newProductId;
            }
    
            // Set default values for nullable fields
            $validated['category'] = $validated['category'] ?? 'Uncategorized';
            $validated['image'] = $validated['image'] ?? 'https://via.placeholder.com/150';
            $validated['sku'] = $validated['sku'] ?? null;
            $validated['alias'] = $validated['alias'] ?? null;
    
            // Update Product
            $product->update($validated);
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            // Error response
            return response()->json([
                'success' => false,
                'message' => 'Product update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Remove the specified product from storage
    public function destroy($id)
    {
        try {
            // Find the product by id
            $product = Product::findOrFail($id);
    
            // Delete the product
            $product->delete();
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Error response
            return response()->json([
                'success' => false,
                'message' => 'Product deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}