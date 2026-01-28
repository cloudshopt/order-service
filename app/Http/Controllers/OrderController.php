<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
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

    public function createFromCart(Request $request)
    {
        $userId = $this->userId($request);
        $cart = $this->activeCart($userId);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $order = DB::transaction(function () use ($cart, $userId) {
            $total = $cart->items->sum(fn ($i) => $i->unit_price_snapshot * $i->qty);

            $order = Order::query()->create([
                'user_id' => $userId,
                'status' => 'pending_payment',
                'total_price' => (int) $total,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name_snapshot' => $item->name_snapshot,
                    'unit_price_snapshot' => $item->unit_price_snapshot,
                    'qty' => $item->qty,
                ]);
            }

            // počistimo košarico
            $cart->items()->delete();

            return $order;
        });

        $order->load('items');

        return response()->json(['data' => $order], 201);
    }

    public function index(Request $request)
    {
        $userId = $this->userId($request);

        $orders = Order::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, int $orderId)
    {
        $userId = $this->userId($request);

        $order = Order::query()
            ->where('user_id', $userId)
            ->where('id', $orderId)
            ->firstOrFail();

        $order->load('items');

        return response()->json(['data' => $order]);
    }
}