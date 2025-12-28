<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::query()->orderBy('sort_order')->get()->map(fn (Banner $b) => $this->toNodeBanner($b))->values();
        return response()->json(['message' => 'Success', 'banners' => $banners], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'title1' => ['required', 'string'],
            'title2' => ['required', 'string'],
            'sub_title1' => ['required', 'string'],
            'btn' => ['required', 'string'],
            'category' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $imageName = $this->storeImageFromRequest($request, 'banner');
        if (!$imageName) {
            return response()->json(['message' => 'Image is required'], 400);
        }

        $banner = Banner::create([
            'image' => $imageName,
            'title1' => $request->input('title1'),
            'title2' => $request->input('title2'),
            'sub_title1' => $request->input('sub_title1'),
            'btn' => $request->input('btn'),
            'category' => $request->input('category'),
            'is_active' => $request->boolean('is_active', true),
            'top_banner' => $request->boolean('top_banner', false),
            'promotional_banner' => $request->boolean('promotional_banner', false),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return response()->json([
            'message' => 'Banner added successfully',
            'banner' => $this->toNodeBanner($banner),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        return response()->json(['message' => 'Success', 'banner' => $this->toNodeBanner($banner)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        $payload = $request->all();
        $imageName = $this->storeImageFromRequest($request, 'banner');
        if ($imageName) {
            $payload['image'] = $imageName;
        }

        $banner->fill($payload);
        $banner->save();

        return response()->json([
            'message' => 'Banner updated successfully',
            'banner' => $this->toNodeBanner($banner->fresh()),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        $banner->delete();
        return response()->json(['message' => 'Banner deleted successfully'], 200);
    }

    private function toNodeBanner(Banner $banner): array
    {
        $image = $banner->image;
        $imagePath = is_string($image) && $image !== ''
            ? (str_starts_with($image, 'uploads/') || str_starts_with($image, '/uploads/') ? $image : ('uploads/banner/' . $image))
            : null;

        return [
            '_id' => (string) $banner->id,
            'Image' => $imagePath,
            'title1' => $banner->title1,
            'title2' => $banner->title2,
            'subTitle1' => $banner->sub_title1,
            'sub_title1' => $banner->sub_title1,
            'btn' => $banner->btn,
            'category' => $banner->category,
            'is_active' => (bool) $banner->is_active,
            'top_banner' => (bool) $banner->top_banner,
            'promotional_banner' => (bool) $banner->promotional_banner,
            'sort_order' => (int) $banner->sort_order,
            'createdAt' => optional($banner->created_at)->toISOString(),
            'updatedAt' => optional($banner->updated_at)->toISOString(),
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
