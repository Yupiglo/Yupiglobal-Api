<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['message' => 'Not Implemented'], 501);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $productId = $request->input('productId');
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product was not found'], 404);
        }

        $cart = DB::transaction(function () use ($user, $product, $quantity) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['total_price' => 0]
            );

            $item = $cart->items()->where('product_id', $product->id)->first();
            if ($item) {
                $item->quantity += $quantity;
                $item->price = $product->price;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            $this->recalculateCart($cart);
            return $cart->fresh(['items.product']);
        });

        return response()->json([
            'message' => 'success',
            'result' => $this->toNodeCart($cart),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $cart = Cart::query()->where('user_id', $user->id)->with(['items.product'])->first();
        return response()->json([
            'message' => 'success',
            'cart' => $cart ? $this->toNodeCart($cart) : null,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product was not found'], 404);
        }

        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 0) {
            $quantity = 0;
        }

        $cart = Cart::query()->where('user_id', $user->id)->with('items')->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $item = $cart->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->quantity = $quantity;
            $item->price = $product->price;
            $item->save();
        }

        $this->recalculateCart($cart);
        $cart = $cart->fresh(['items.product']);

        return response()->json([
            'message' => 'success',
            'cart' => $this->toNodeCart($cart),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $userId = request()->user()->id;
        $cart = Cart::query()->where('user_id', $userId)->with('items')->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $item = CartItem::query()->where('id', $id)->where('cart_id', $cart->id)->first();
        if (!$item) {
            return response()->json(['message' => 'Item was not found'], 404);
        }

        $item->delete();
        $this->recalculateCart($cart);
        $cart = $cart->fresh(['items.product']);

        return response()->json([
            'message' => 'success',
            'cart' => $this->toNodeCart($cart),
        ], 200);
    }

    public function applyCoupon(Request $request)
    {
        $user = $request->user();
        $code = $request->input('code') ?? $request->input('couponCode');

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('expires', '>', now())
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 404);
        }

        $cart = Cart::query()->where('user_id', $user->id)->with(['items.product'])->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->discount = $coupon->discount;
        $this->recalculateCart($cart);
        $cart = $cart->fresh(['items.product']);

        return response()->json([
            'message' => 'success',
            'cart' => $this->toNodeCart($cart),
        ], 201);
    }

    private function recalculateCart(Cart $cart): void
    {
        $cart->loadMissing('items');
        $total = 0;
        foreach ($cart->items as $item) {
            $total += ((int) $item->quantity) * ((float) $item->price);
        }

        $cart->total_price = $total;
        if ($cart->discount !== null) {
            $cart->total_price_after_discount = $total - ($total * ((float) $cart->discount)) / 100;
        } else {
            $cart->total_price_after_discount = null;
        }

        $cart->save();
    }

    private function toNodeCart(Cart $cart): array
    {
        $cart->loadMissing(['items.product']);

        return [
            '_id' => (string) $cart->id,
            'userId' => (string) $cart->user_id,
            'cartItem' => $cart->items->map(function (CartItem $item) {
                return [
                    '_id' => (string) $item->id,
                    'productId' => $item->product ? $this->toNodeProduct($item->product) : (string) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'totalProductDiscount' => $item->total_product_discount,
                ];
            })->values(),
            'totalPrice' => (float) $cart->total_price,
            'totalPriceAfterDiscount' => $cart->total_price_after_discount !== null ? (float) $cart->total_price_after_discount : null,
            'discount' => $cart->discount !== null ? (float) $cart->discount : null,
            'createdAt' => optional($cart->created_at)->toISOString(),
            'updatedAt' => optional($cart->updated_at)->toISOString(),
        ];
    }

    private function toNodeProduct(Product $product): array
    {
        return [
            '_id' => (string) $product->id,
            'title' => $product->title,
            'description' => $product->description,
            'type' => $product->type,
            'brand' => $product->brand,
            'category' => $product->category,
            'price' => (float) $product->price,
            'new' => (bool) $product->is_new,
            'sale' => (bool) $product->is_sale,
            'discount' => (float) $product->discount,
            'imgCover' => $product->img_cover,
            'variants' => $product->variants ?? [],
            'images' => $product->images ?? [],
            'quantity' => (int) $product->quantity,
            'sold' => (int) $product->sold,
            'createdAt' => optional($product->created_at)->toISOString(),
            'updatedAt' => optional($product->updated_at)->toISOString(),
        ];
    }
}
