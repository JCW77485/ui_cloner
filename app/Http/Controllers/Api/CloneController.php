<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CloneController extends Controller
{
    public function clone(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; UICloner/1.0)',
                ])
                ->get($request->input('url'));

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch the URL. Status: ' . $response->status(),
                ], 422);
            }

            $html = $response->body();
            $cleanedHtml = $this->sanitizeHtml($html);

            return response()->json([
                'html' => $cleanedHtml,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch the URL: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function sanitizeHtml(string $html): string
    {
        // Remove <script> tags and their content
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        // Remove <noscript> tags and their content
        $html = preg_replace('/<noscript\b[^>]*>(.*?)<\/noscript>/is', '', $html);

        // Remove inline event handlers (onclick, onload, onerror, etc.)
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $html);

        // Remove javascript: URLs
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html);

        // Remove <iframe> tags
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);

        // Remove <object> and <embed> tags
        $html = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $html);
        $html = preg_replace('/<embed\b[^>]*\/?>/is', '', $html);

        // Remove <form> tags but keep content
        $html = preg_replace('/<\/?form\b[^>]*>/i', '', $html);

        // Extract only the <body> content if present
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        return trim($html);
    }
}
