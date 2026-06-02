<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function create(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('products.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sku'         => 'nullable|string|max:255',
            'status'      => 'nullable|in:active,inactive,draft',
            'store_id'    => 'nullable|exists:stores,id',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|url',
            'categories'  => 'nullable|string',
            'attributes'  => 'nullable|array',
        ]);

        if (! empty($validated['store_id'])) {
            abort_if(
                Store::where('id', $validated['store_id'])->where('user_id', $request->user()->id)->doesntExist(),
                403
            );
        }

        $product = Product::create([
            'user_id'     => $request->user()->id,
            'store_id'    => $validated['store_id'] ?? null,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? null,
            'stock'       => $validated['stock'] ?? 0,
            'sku'         => $validated['sku'] ?? null,
            'status'      => $validated['status'] ?? 'active',
            'images'      => array_values(array_filter($validated['images'] ?? [])),
            'categories'  => array_filter(array_map('trim', explode(',', $validated['categories'] ?? ''))),
            'attributes'  => $this->parseAttributes($validated['attributes'] ?? []),
            'raw_data'    => [],
        ]);

        return redirect()->route('listings.index')->with('success', 'Produk berhasil disimpan! Pilih produk di bawah lalu push ke channel Anda.');
    }

    public function show(Request $request, Product $product)
    {
        abort_if($product->user_id !== $request->user()->id, 403);
        $product->load(['store', 'variants', 'listings.channelIntegration']);

        return view('products.show', compact('product'));
    }

    public function edit(Request $request, Product $product)
    {
        abort_if($product->user_id !== $request->user()->id, 403);
        $product->load(['variants']);

        $stores = Store::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('products.edit', compact('product', 'stores'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'sku'         => 'nullable|string|max:255',
            'status'      => 'nullable|in:active,inactive,draft',
            'store_id'    => 'nullable|exists:stores,id',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|url',
            'categories'  => 'nullable|string',
            'attributes'  => 'nullable|array',
        ]);

        if (! empty($validated['store_id'])) {
            abort_if(
                Store::where('id', $validated['store_id'])->where('user_id', $request->user()->id)->doesntExist(),
                403
            );
        }

        $product->update([
            'store_id'    => $validated['store_id'] ?? null,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? null,
            'stock'       => $validated['stock'] ?? 0,
            'sku'         => $validated['sku'] ?? null,
            'status'      => $validated['status'] ?? 'active',
            'images'      => array_values(array_filter($validated['images'] ?? [])),
            'categories'  => array_filter(array_map('trim', explode(',', $validated['categories'] ?? ''))),
            'attributes'  => $this->parseAttributes($validated['attributes'] ?? []),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_if($product->user_id !== $request->user()->id, 403);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function export(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)
            ->with('store')
            ->latest()
            ->lazy();

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Title', 'SKU', 'Price', 'Stock', 'Description', 'Categories', 'Store', 'Status']);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->id,
                    $p->title,
                    $p->sku ?? '',
                    $p->price ?? '',
                    $p->stock,
                    $p->description ?? '',
                    implode(', ', $p->categories ?? []),
                    $p->store?->name ?? 'Manual',
                    $p->status ?? '',
                ]);
            }
            fclose($out);
        }, 'products.csv', ['Content-Type' => 'text/csv']);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
        ]);

        $path = $request->file('image')->store('products', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    public function analyzeImage(Request $request)
    {
        $request->validate(['url' => 'required|string']);

        if (empty(config('services.openrouter.key'))) {
            return response()->json(['title' => '', 'description' => '', 'error' => 'OPENROUTER_API_KEY is not set in .env']);
        }

        try {
            $imageUrl   = $request->input('url');
            $publicBase = rtrim(Storage::disk('public')->url(''), '/');

            if (str_starts_with($imageUrl, $publicBase)) {
                // Locally uploaded file — read from disk
                $relativePath = ltrim(str_replace($publicBase, '', $imageUrl), '/');
                $contents     = Storage::disk('public')->get($relativePath);
                $mime         = Storage::disk('public')->mimeType($relativePath) ?: 'image/jpeg';
            } else {
                // External URL — fetch over HTTP
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; ProductBot/1.0)',
                ])->timeout(15)->get($imageUrl);

                if (! $response->successful()) {
                    throw new \RuntimeException("Could not fetch image (HTTP {$response->status()}).");
                }

                $contents = $response->body();
                $mime     = $response->header('Content-Type') ?: 'image/jpeg';
                // Strip any charset suffix: "image/jpeg; charset=..." → "image/jpeg"
                $mime = strtok($mime, ';');
            }

            $b64    = base64_encode($contents);
            $locale = $request->input('locale') ?: ($request->user()->ui_locale ?? 'en');
            $result = app(AiContentService::class)->analyzeProductImage("data:{$mime};base64,{$b64}", $locale);

            return response()->json([
                'title'          => $result['title'] ?? '',
                'description'    => $result['description'] ?? '',
                'categories'     => $result['categories'] ?? [],
                'specifications' => $result['specifications'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['title' => '', 'description' => '', 'categories' => [], 'specifications' => [], 'error' => $e->getMessage()]);
        }
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
        } elseif ($request->input('store_id') === 'none') {
            $query->whereNull('store_id');
        } elseif ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        return response()->json($query->latest()->paginate(50));
    }

    private function parseAttributes(array $raw): array
    {
        $attrs = [];
        foreach ($raw as $attr) {
            $name   = trim($attr['name'] ?? '');
            $values = array_values(array_filter(array_map('trim', explode(',', $attr['values'] ?? ''))));
            if ($name !== '' && ! empty($values)) {
                $attrs[] = ['name' => $name, 'values' => $values];
            }
        }
        return $attrs;
    }
}
