<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $addresses = Address::query()->where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'success',
            'getAllAddresses' => $addresses,
        ], 201);
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

        $validator = Validator::make($request->all(), [
            'city' => ['nullable', 'string'],
            'street' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $address = Address::create([
            'user_id' => $user->id,
            'city' => $request->input('city'),
            'street' => $request->input('street'),
            'phone' => $request->input('phone'),
        ]);

        $addresses = Address::query()->where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'success',
            'addAddress' => $addresses,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $addressId = $request->input('address');
        if (!$addressId) {
            return response()->json(['message' => 'address is required'], 400);
        }

        $deleted = Address::query()->where('id', $addressId)->where('user_id', $user->id)->delete();
        if ($deleted === 0) {
            return response()->json(['message' => 'Address was not found'], 404);
        }

        $addresses = Address::query()->where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'success',
            'removeAddress' => $addresses,
        ], 201);
    }
}
