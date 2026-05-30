<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)
            ->with('store')
            ->latest()
            ->paginate(50);

        return view('products.index', compact('products'));
    }

    public function apiIndex(Request $request)
    {
        $query = Product::where('user_id', $request->user()->id)->with('store');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('store_ids')) {
            $query->whereIn('store_id', (array) $request->store_ids);
        } elseif ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        return response()->json($query->latest()->paginate(50));
    }

    public function show(Request $request, Product $product)
    {
        abort_if($product->user_id !== $request->user()->id, 403);
        $product->load(['store', 'variants', 'listings.channelIntegration']);

        return view('products.show', compact('product'));
    }
}
