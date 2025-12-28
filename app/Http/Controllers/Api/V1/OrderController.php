<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::query()->with(['items.product'])->orderByDesc('created_at')->get();
        return response()->json([
            'message' => 'success',
            'orders' => $orders->map(fn (Order $o) => $this->toNodeOrder($o))->values(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        $user = $request->user();

        $cart = Cart::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->with(['items'])
            ->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart was not found'], 404);
        }

        $shipping = (array) $request->input('shippingAddress', []);
        $shippingStreet = $shipping['street'] ?? $request->input('shipping_street');
        $shippingCity = $shipping['city'] ?? $request->input('shipping_city');
        $shippingPhone = $shipping['phone'] ?? $request->input('shipping_phone');

        $order = DB::transaction(function () use ($user, $cart, $shippingStreet, $shippingCity, $shippingPhone) {
            $cart->loadMissing('items');
            $total = $cart->total_price_after_discount !== null ? (float) $cart->total_price_after_discount : (float) $cart->total_price;

            $order = Order::create([
                'user_id' => $user->id,
                'shipping_street' => $shippingStreet,
                'shipping_city' => $shippingCity,
                'shipping_phone' => $shippingPhone,
                'payment_method' => 'cash',
                'is_paid' => false,
                'is_delivered' => false,
                'order_status' => 'processing',
                'total_order_price' => $total,
                'order_at' => now(),
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total_product_discount' => $cartItem->total_product_discount,
                ]);

                Product::query()->where('id', $cartItem->product_id)->update([
                    'quantity' => DB::raw('GREATEST(quantity - ' . ((int) $cartItem->quantity) . ', 0)'),
                    'sold' => DB::raw('sold + ' . ((int) $cartItem->quantity)),
                ]);
            }

            $cart->items()->delete();
            $cart->delete();

            return $order->fresh(['items.product']);
        });

        return response()->json([
            'message' => 'success',
            'order' => $this->toNodeOrder($order),
        ], 201);
    }

    public function storeFromUserCart(Request $request)
    {
        $user = $request->user();

        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->with(['items'])
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart was not found'], 404);
        }

        $shipping = (array) $request->input('shippingAddress', []);
        $shippingStreet = $shipping['street'] ?? $request->input('shipping_street');
        $shippingCity = $shipping['city'] ?? $request->input('shipping_city');
        $shippingPhone = $shipping['phone'] ?? $request->input('shipping_phone');

        $order = DB::transaction(function () use ($user, $cart, $shippingStreet, $shippingCity, $shippingPhone) {
            $cart->loadMissing('items');
            $total = $cart->total_price_after_discount !== null ? (float) $cart->total_price_after_discount : (float) $cart->total_price;

            $order = Order::create([
                'user_id' => $user->id,
                'shipping_street' => $shippingStreet,
                'shipping_city' => $shippingCity,
                'shipping_phone' => $shippingPhone,
                'payment_method' => 'cash',
                'is_paid' => false,
                'is_delivered' => false,
                'order_status' => 'processing',
                'total_order_price' => $total,
                'order_at' => now(),
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total_product_discount' => $cartItem->total_product_discount,
                ]);

                Product::query()->where('id', $cartItem->product_id)->update([
                    'quantity' => DB::raw('GREATEST(quantity - ' . ((int) $cartItem->quantity) . ', 0)'),
                    'sold' => DB::raw('sold + ' . ((int) $cartItem->quantity)),
                ]);
            }

            $cart->items()->delete();
            $cart->delete();

            return $order->fresh(['items.product']);
        });

        return response()->json([
            'message' => 'success',
            'order' => $this->toNodeOrder($order),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $order = Order::query()
            ->where('user_id', $user->id)
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'message' => 'success',
            'order' => $order ? $this->toNodeOrder($order) : null,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json(['message' => 'Not Implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Not Implemented'], 501);
    }

    public function checkOut(Request $request, string $id)
    {
        return response()->json([
            'message' => 'success',
            'sessions' => null,
        ], 200);
    }

    private function toNodeOrder(Order $order): array
    {
        $order->loadMissing(['items.product']);

        return [
            '_id' => (string) $order->id,
            'userId' => (string) $order->user_id,
            'cartItem' => $order->items->map(function (OrderItem $item) {
                return [
                    '_id' => (string) $item->id,
                    'productId' => $item->product ? [
                        '_id' => (string) $item->product->id,
                        'title' => $item->product->title,
                        'price' => (float) $item->product->price,
                        'imgCover' => $item->product->img_cover,
                    ] : (string) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'totalProductDiscount' => $item->total_product_discount,
                ];
            })->values(),
            'shippingAddress' => [
                'street' => $order->shipping_street,
                'city' => $order->shipping_city,
                'phone' => $order->shipping_phone,
            ],
            'orderStatus' => $order->order_status,
            'paymentMethod' => $order->payment_method,
            'isPaid' => (bool) $order->is_paid,
            'paidAt' => optional($order->paid_at)->toISOString(),
            'isDelivered' => (bool) $order->is_delivered,
            'deliveredAt' => optional($order->delivered_at)->toISOString(),
            'totalOrderPrice' => (float) $order->total_order_price,
            'createdAt' => optional($order->created_at)->toISOString(),
            'updatedAt' => optional($order->updated_at)->toISOString(),
        ];
    }
}
