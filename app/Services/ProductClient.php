<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProductClient
{
    public function getProduct(int $productId): array
    {
        $base = rtrim((string) config('services.products.base_url'), '/');
        $url = $base . '/items/' . $productId;

        $resp = Http::acceptJson()->get($url);

        if (!$resp->successful()) {
            throw new \RuntimeException("Product not found or product-service error (status {$resp->status()}).");
        }

        $json = $resp->json();

        // pričakujemo { data: { id, name, price, ... } }
        $p = $json['data'] ?? null;
        if (!$p || !isset($p['name'], $p['price'], $p['id'])) {
            throw new \RuntimeException('Unexpected product-service response.');
        }

        return [
            'id' => (int) $p['id'],
            'name' => (string) $p['name'],
            'price' => (int) $p['price'],
        ];
    }
}