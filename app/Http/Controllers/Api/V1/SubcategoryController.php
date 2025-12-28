<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoryId = request()->route('categoryId');

        $query = Subcategory::query();
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $subcategories = $query->get();

        return response()->json([
            'message' => 'success',
            'getAllSubCategories' => $subcategories,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        $categoryId = $request->route('categoryId') ?? $request->input('categoryId') ?? $request->input('category_id');

        $validator = Validator::make($payload, [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        if (!$categoryId) {
            return response()->json(['message' => 'categoryId is required'], 400);
        }

        $subcategory = Subcategory::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'category_id' => $categoryId,
        ]);

        return response()->json([
            'message' => 'success',
            'addSubcategory' => $subcategory,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'subcategory was not found'], 404);
        }

        return response()->json([
            'message' => 'success',
            'subcategory' => $subcategory,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'subcategory was not found'], 404);
        }

        $payload = $request->all();
        if ($request->filled('name')) {
            $payload['slug'] = Str::slug($request->input('name'));
        }

        $subcategory->fill($payload);
        $subcategory->save();

        return response()->json([
            'message' => 'success',
            'updateSubCategory' => $subcategory->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'subcategory was not found'], 404);
        }

        $subcategory->delete();
        return response()->json(['message' => 'success'], 200);
    }
}
