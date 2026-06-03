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

    public function generateAdCopy(array $product, string $channelType, string $extraContext = '', string $locale = 'en'): string
    {
        $prompt = $this->buildAdPrompt($product, $channelType, $extraContext);
        $prompt .= "\n" . $this->getLanguageInstruction($locale);

        return $this->chat([['role' => 'user', 'content' => $prompt]]);
    }

    public function improveDescription(array $product, string $channelType, string $locale = 'en'): string
    {
        $prompt = "Rewrite the following product description to be compelling and optimised for {$channelType}. "
            . "Keep it factual. Product: {$product['title']}. "
            . "Current description: {$product['description']}\n"
            . $this->getLanguageInstruction($locale);

        return $this->chat([['role' => 'user', 'content' => $prompt]]);
    }

    public function analyzeProductImage(string $imageDataUrl, string $locale = 'en'): array
    {
        $langInstruction = $this->getLanguageInstruction($locale);
        $raw = $this->chat([
            [
                'role'    => 'user',
                'content' => [
                    ['type' => 'image_url', 'image_url' => ['url' => $imageDataUrl]],
                    [
                        'type' => 'text',
                        'text' => 'You are a professional e-commerce copywriter. Analyze this product image and write concise retail copy. '
                            . 'Respond with ONLY a valid JSON object, no extra text, with exactly these keys: '
                            . '{"title": "...", "description": "...", "categories": ["...", "..."], "specifications": [{"name": "...", "values": ["..."]}]}. '
                            . 'title: concise, specific product name (max 80 characters). '
                            . 'description: 2 paragraphs separated by \n\n, around 80-120 words total. '
                            . 'Paragraph 1 (2-3 sentences): what the product is, its key features and main benefit. '
                            . 'Paragraph 2 (2-3 sentences): who it is for, when to use it, and why they should buy it. '
                            . 'categories: array of 1-3 broad e-commerce category strings (e.g. "Electronics", "Audio"). '
                            . 'specifications: array of up to 5 relevant spec objects with "name" and "values" array. Only include specs clearly visible in the image. '
                            . 'Tone: direct, professional. No hype words, no markdown, no bullet points. '
                            . $langInstruction,
                    ],
                ],
            ],
        ], 800);

        // Strip markdown code fences some models add around JSON
        $clean   = trim(preg_replace('/^```(?:json)?\s*/i', '', preg_replace('/\s*```$/m', '', trim($raw))));
        $decoded = json_decode($clean, true);

        if (is_array($decoded) && (isset($decoded['title']) || isset($decoded['description']))) {
            return [
                'title'          => trim($decoded['title'] ?? ''),
                'description'    => trim($decoded['description'] ?? ''),
                'categories'     => array_values(array_filter((array) ($decoded['categories'] ?? []))),
                'specifications' => array_values(array_filter((array) ($decoded['specifications'] ?? []), fn($s) => !empty($s['name']))),
            ];
        }

        return ['title' => '', 'description' => trim($raw), 'categories' => [], 'specifications' => []];
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

    private function getLanguageInstruction(string $locale): string
    {
        return match($locale) {
            'nl' => 'Write your response in Dutch (Nederlands).',
            'id' => 'Write your response in Bahasa Indonesia.',
            default => 'Write your response in English.',
        };
    }
}
