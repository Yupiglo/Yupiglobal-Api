<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = (int) request()->input('page', 1);
        $limit = (int) request()->input('limit', 50);
        if ($limit <= 0) {
            $limit = 50;
        }

        $paginator = Coupon::query()->paginate($limit, ['*'], 'page', $page);

        $coupons = collect($paginator->items())
            ->map(fn (Coupon $c) => array_merge($c->toArray(), ['_id' => (string) $c->id]))
            ->values();

        return response()->json([
            'page' => $page,
            'message' => 'success',
            'getAllCoupons' => $coupons,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        $validator = Validator::make($payload, [
            'code' => ['required', 'string'],
            'expires' => ['required'],
            'discount' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $coupon = Coupon::create($payload);

        return response()->json([
            'message' => 'success',
            'createCoupon' => array_merge($coupon->toArray(), ['_id' => (string) $coupon->id]),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon was not found'], 404);
        }

        return response()->json([
            'message' => 'success',
            'getSpecificCoupon' => array_merge($coupon->toArray(), ['_id' => (string) $coupon->id]),
            'url' => null,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon was not found'], 404);
        }

        $coupon->fill($request->all());
        $coupon->save();

        return response()->json([
            'message' => 'success',
            'updateCoupon' => array_merge($coupon->fresh()->toArray(), ['_id' => (string) $coupon->id]),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon was not found'], 404);
        }

        $coupon->delete();
        return response()->json(['message' => 'success'], 200);
    }
}
