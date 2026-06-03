<?php

namespace App\Http\Controllers;

use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

        if (empty($request->getContent()) && empty($request->all())) {
            return response()->json(['error' => 'Foto terlalu besar dan ditolak server. Gunakan foto yang lebih kecil.'], 413);
        }

        $request->validate([
            'image_data' => 'required_without:url|nullable|string',
            'url'        => 'required_without:image_data|nullable|url',
            'cf-turnstile-response' => 'required',
        ]);

        // Verify Turnstile CAPTCHA
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $request->input('cf-turnstile-response'),
        ]);

        if (! $response->json('success')) {
            return response()->json(['error' => 'CAPTCHA verification failed. Please try again.'], 422);
        }

        $imageData = $request->input('image_data') ?? $request->input('url');

        if (!config('services.openrouter.key')) {
            return response()->json(['error' => 'Layanan AI belum dikonfigurasi.'], 503);
        }

        try {
            $result = app(AiContentService::class)->analyzeProductImage($imageData, 'id');
            $request->session()->put('demo_scans', 1);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Demo scan failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            if (str_contains($e->getMessage(), 'unsupported image') || str_contains($e->getMessage(), 'invalid_image_format')) {
                return response()->json(['error' => 'Format gambar tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.'], 422);
            }

            return response()->json(['error' => 'Gagal menganalisis gambar: ' . $e->getMessage()], 500);
        }
    }
}
