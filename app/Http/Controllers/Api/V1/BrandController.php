<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
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

        $paginator = Brand::query()->paginate($limit, ['*'], 'page', $page);
        return response()->json([
            'page' => $page,
            'message' => 'success',
            'getAllBrands' => $paginator->items(),
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

        $imageName = $this->storeImageFromRequest($request, 'brand');

        $brand = Brand::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'image' => $imageName,
        ]);

        return response()->json([
            'message' => 'success',
            'addBrand' => $this->toNodeBrand($brand),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand was not found'], 404);
        }

        return response()->json(['message' => 'success', 'brand' => $this->toNodeBrand($brand)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand was not found'], 404);
        }

        $payload = [];
        if ($request->filled('name')) {
            $payload['name'] = $request->input('name');
            $payload['slug'] = Str::slug($request->input('name'));
        }

        $imageName = $this->storeImageFromRequest($request, 'brand');
        if ($imageName) {
            $payload['image'] = $imageName;
        }

        $brand->fill($payload);
        $brand->save();

        return response()->json([
            'message' => 'success',
            'updateBrand' => $this->toNodeBrand($brand->fresh()),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand was not found'], 404);
        }

        $brand->delete();
        return response()->json(['message' => 'success'], 200);
    }

    private function toNodeBrand(Brand $brand): array
    {
        $image = $brand->image;
        $imagePath = is_string($image) && $image !== ''
            ? (str_starts_with($image, 'uploads/') || str_starts_with($image, '/uploads/') ? $image : ('uploads/brand/' . $image))
            : null;

        return [
            '_id' => (string) $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'Image' => $imagePath,
            'createdAt' => optional($brand->created_at)->toISOString(),
            'updatedAt' => optional($brand->updated_at)->toISOString(),
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
