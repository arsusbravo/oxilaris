<?php

namespace App\Http\Controllers;

use App\Services\AiContentService;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function index(Request $request)
    {
        $used      = $request->session()->get('demo_scans', 0);
        $scansLeft = max(0, 2 - $used);

        $platforms = array_keys(array_filter([
            'tiktok_shop' => (bool) config('services.tiktok_shop.app_key'),
            'shopee'      => (bool) config('services.shopee.partner_id'),
            'shopify'     => (bool) config('services.shopify.client_id'),
            'woocommerce' => (bool) config('services.woocommerce.app_name'),
        ]));

        return view('demo', compact('scansLeft', 'platforms'));
    }

    public function scan(Request $request)
    {
        if ($request->session()->get('demo_scans', 0) >= 2) {
            return response()->json([
                'error' => 'Scan gratis Anda sudah digunakan. Daftar untuk mendapatkan akses penuh.',
            ], 429);
        }

        $request->validate([
            'image_data' => 'required_without:url|nullable|string',
            'url'        => 'required_without:image_data|nullable|url',
        ]);

        $imageData = $request->input('image_data') ?? $request->input('url');

        if (!config('services.openrouter.key')) {
            return response()->json(['error' => 'Layanan AI belum dikonfigurasi.'], 503);
        }

        try {
            $result = app(AiContentService::class)->analyzeProductImage($imageData, 'id');
            $request->session()->put('demo_scans', 1);
            return response()->json($result);
        } catch (\Exception) {
            return response()->json(['error' => 'Gagal menganalisis gambar. Coba lagi.'], 500);
        }
    }
}
