<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::query()->get()->map(fn (Category $c) => $this->toNodeCategory($c))->values();

        return response()->json([
            'message' => 'success',
            'getAllCategories' => $categories,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $imageName = $this->storeImageFromRequest($request, 'category');

        $category = Category::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'image' => $imageName,
        ]);

        return response()->json([
            'message' => 'success',
            'addcategory' => $this->toNodeCategory($category),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Nettoyer l'ID en supprimant les espaces et autres caractères indésirables
        $id = trim($id);
        
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'category was not found'], 404);
        }

        return response()->json([
            'message' => 'success',
            'category' => $this->toNodeCategory($category),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Nettoyer l'ID en supprimant les espaces et autres caractères indésirables
        $id = trim($id);
        
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'category was not found'], 404);
        }

        $payload = [];
        if ($request->filled('name')) {
            $payload['name'] = $request->input('name');
            $payload['slug'] = Str::slug($request->input('name'));
        }

        $imageName = $this->storeImageFromRequest($request, 'category');
        if ($imageName) {
            $payload['image'] = $imageName;
        }

        $category->fill($payload);
        $category->save();

        return response()->json([
            'message' => 'success',
            'updateCategory' => $this->toNodeCategory($category->fresh()),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Nettoyer l'ID en supprimant les espaces et autres caractères indésirables
        $id = trim($id);
        
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'category was not found'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'success'], 200);
    }

    private function toNodeCategory(Category $category): array
    {
        $image = $category->image;
        $imagePath = is_string($image) && $image !== ''
            ? (str_starts_with($image, 'uploads/') || str_starts_with($image, '/uploads/') ? $image : ('uploads/category/' . $image))
            : null;

        return [
            '_id' => (string) $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'Image' => $imagePath,
            'createdAt' => optional($category->created_at)->toISOString(),
            'updatedAt' => optional($category->updated_at)->toISOString(),
        ];
    }

    private function storeImageFromRequest(Request $request, string $folder): ?string
    {
        if ($request->hasFile('Image')) {
            return $this->storeUploadedFile($request->file('Image'), $folder);
        }

        $image = $request->input('Image');
        if (!is_string($image) || $image === '') {
            return null;
        }

        if (!str_starts_with($image, 'data:image/')) {
            return null;
        }

        return $this->storeBase64Image($image, $folder);
    }

    private function storeUploadedFile($file, string $folder): string
    {
        $dir = public_path('uploads/' . $folder);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $name);
        return $name;
    }

    private function storeBase64Image(string $dataUrl, string $folder): ?string
    {
        $dir = public_path('uploads/' . $folder);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,/', $dataUrl, $m)) {
            return null;
        }

        $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $raw = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $bytes = base64_decode($raw);
        if ($bytes === false) {
            return null;
        }

        $name = Str::uuid()->toString() . '.' . $ext;
        File::put($dir . '/' . $name, $bytes);
        return $name;
    }
}
