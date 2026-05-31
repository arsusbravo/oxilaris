<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiContentService
{
    private string $model;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key', '');
        $this->model  = config('services.openrouter.model', 'google/gemini-flash-1.5');
    }

    public function generateAdCopy(array $product, string $channelType, string $extraContext = ''): string
    {
        $prompt = $this->buildAdPrompt($product, $channelType, $extraContext);

        return $this->chat([['role' => 'user', 'content' => $prompt]]);
    }

    public function improveDescription(array $product, string $channelType): string
    {
        $prompt = "Rewrite the following product description to be compelling and optimised for {$channelType}. "
            . "Keep it factual. Product: {$product['title']}. "
            . "Current description: {$product['description']}";

        return $this->chat([['role' => 'user', 'content' => $prompt]]);
    }

    public function analyzeProductImage(string $imageDataUrl): array
    {
        $raw = $this->chat([
            [
                'role'    => 'user',
                'content' => [
                    ['type' => 'image_url', 'image_url' => ['url' => $imageDataUrl]],
                    [
                        'type' => 'text',
                        'text' => 'Look at this product image and identify the product. '
                            . 'Respond with ONLY a valid JSON object, no extra text: '
                            . '{"title": "...", "description": "..."}. '
                            . 'title = concise product name max 100 characters. '
                            . 'description = 150-300 words suitable for an online store, no markdown.',
                    ],
                ],
            ],
        ], 600);

        // Strip markdown code fences some models add around JSON
        $clean   = trim(preg_replace('/^```(?:json)?\s*/i', '', preg_replace('/\s*```$/m', '', trim($raw))));
        $decoded = json_decode($clean, true);

        if (is_array($decoded) && (isset($decoded['title']) || isset($decoded['description']))) {
            return [
                'title'       => trim($decoded['title'] ?? ''),
                'description' => trim($decoded['description'] ?? ''),
            ];
        }

        return ['title' => '', 'description' => trim($raw)];
    }

    private function chat(array $messages, int $maxTokens = 800): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer'  => config('app.url', 'http://localhost'),
        ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'      => $this->model,
            'messages'   => $messages,
            'max_tokens' => $maxTokens,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'OpenRouter request failed: ' . $response->status() . ' — ' . $response->body()
            );
        }

        return trim($response->json('choices.0.message.content', ''));
    }

    private function buildAdPrompt(array $product, string $channelType, string $extra): string
    {
        $lines = [
            "Write a short, persuasive advertisement for the following product to be used on {$channelType}.",
            "Product name: {$product['title']}",
            "Price: " . (isset($product['price']) ? "€{$product['price']}" : 'not specified'),
            "Description: " . ($product['description'] ?? 'no description'),
        ];

        if ($extra) {
            $lines[] = "Additional context: {$extra}";
        }

        $lines[] = "The ad should be concise (2-3 sentences), highlight the key benefit, and include a call to action.";

        return implode("\n", $lines);
    }
}
