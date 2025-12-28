<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SendEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $type = $request->input('type');

        if ($type === 'order') {
            return response()->json(['message' => 'Order notification sent']);
        }

        if ($type === 'user') {
            return response()->json(['message' => 'User creation email sent']);
        }

        if ($type === 'forgot') {
            return response()->json(['message' => 'Forgot password email sent']);
        }

        if ($type === 'custom') {
            return response()->json(['message' => 'Custom email sent']);
        }

        return response()->json(['error' => 'Invalid email type'], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
