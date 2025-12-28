<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $wishlist = Wishlist::query()->where('user_id', $user->id)->with('items')->first();

        $products = $wishlist ? $wishlist->items->pluck('product_id')->map(fn ($id) => (string) $id)->values() : collect();

        return response()->json([
            'message' => 'success',
            'wishlist' => $products,
            'wishList' => [
                '_id' => $wishlist ? (string) $wishlist->id : null,
                'userId' => (string) $user->id,
                'products' => $products,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json(['message' => 'Not Implemented'], 501);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['message' => 'Not Implemented'], 501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $productId = $request->input('productId');
        if (!$productId) {
            return response()->json(['message' => 'productId is required'], 400);
        }

        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);
        WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id' => $productId,
        ]);

        $wishlist->load('items');
        $products = $wishlist->items->pluck('product_id')->map(fn ($id) => (string) $id)->values();

        return response()->json([
            'message' => 'success',
            'wishlist' => $products,
            'wishList' => [
                '_id' => (string) $wishlist->id,
                'userId' => (string) $user->id,
                'products' => $products,
            ],
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $productId = $request->input('productId');
        if (!$productId) {
            return response()->json(['message' => 'productId is required'], 400);
        }

        $wishlist = Wishlist::query()->where('user_id', $user->id)->first();
        if (!$wishlist) {
            return response()->json(['message' => 'Wishlist not found'], 404);
        }

        WishlistItem::query()
            ->where('wishlist_id', $wishlist->id)
            ->where('product_id', $productId)
            ->delete();

        $wishlist->load('items');
        $products = $wishlist->items->pluck('product_id')->map(fn ($id) => (string) $id)->values();

        return response()->json([
            'message' => 'success',
            'wishlist' => $products,
            'wishList' => [
                '_id' => (string) $wishlist->id,
                'userId' => (string) $user->id,
                'products' => $products,
            ],
        ], 200);
    }
}
