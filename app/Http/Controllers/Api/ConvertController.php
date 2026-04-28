<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConvertController extends Controller
{
    public function convert(Request $request)
    {
        $request->validate([
            'html' => 'required|string|max:50000',
        ]);

        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file.',
            ], 500);
        }

        $html = $request->input('html');

        $prompt = "Convert this HTML into a clean Vue 3 component using the Options API and TailwindCSS. Do NOT use <script setup>. Use export default with data() and methods(). Remove inline styles and replace them with TailwindCSS utility classes. Return ONLY the Vue component code, no explanations.\n\nHTML:\n" . $html;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a Vue.js expert. Convert HTML to clean Vue 3 components using the Options API and TailwindCSS. Always use export default with data() and methods(). Never use <script setup> or Composition API. Output only the Vue component code wrapped in <template>, <script>, and optionally <style> tags.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.3,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'OpenAI API error: ' . $response->body(),
                ], 422);
            }

            $result = $response->json();
            $vueCode = $result['choices'][0]['message']['content'] ?? '';

            // Clean up markdown code fences if present
            $vueCode = preg_replace('/^```(?:vue|html|javascript)?\s*\n?/m', '', $vueCode);
            $vueCode = preg_replace('/\n?```\s*$/m', '', $vueCode);

            return response()->json([
                'vue_code' => trim($vueCode),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to convert HTML: ' . $e->getMessage(),
            ], 500);
        }
    }
}
