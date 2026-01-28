<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\ProductClient;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function userId(Request $request): int
    {
        return (int) $request->attributes->get('user_id');
    }

    private function activeCart(int $userId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['user_id' => $userId, 'status' => 'active']
        );
    }

    public function show(Request $request)
    {
        $cart = $this->activeCart($this->userId($request));
        $cart->load('items');

        $total = $cart->items->sum(fn ($i) => $i->unit_price_snapshot * $i->qty);

        return response()->json([
            'data' => [
                'id' => $cart->id,
                'items' => $cart->items,
                'total_price' => (int) $total,
            ],
        ]);
    }

    public function addItem(Request $request, ProductClient $products)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $userId = $this->userId($request);
        $cart = $this->activeCart($userId);

        $p = $products->getProduct((int) $data['product_id']);

        $item = CartItem::query()->where('cart_id', $cart->id)
            ->where('product_id', (int) $data['product_id'])
            ->first();

        if ($item) {
            $item->qty = $item->qty + (int) $data['qty'];
            // osveži snapshot (optional)
            $item->name_snapshot = $p['name'];
            $item->unit_price_snapshot = $p['price'];
            $item->save();
        } else {
            $item = CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => (int) $p['id'],
                'name_snapshot' => $p['name'],
                'unit_price_snapshot' => (int) $p['price'],
                'qty' => (int) $data['qty'],
            ]);
        }

        return response()->json(['data' => $item], 201);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $userId = $this->userId($request);
        $cart = $this->activeCart($userId);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->qty = (int) $data['qty'];
        $item->save();

        return response()->json(['data' => $item]);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $userId = $this->userId($request);
        $cart = $this->activeCart($userId);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->delete();

        return response()->json(['status' => 'ok']);
    }
}