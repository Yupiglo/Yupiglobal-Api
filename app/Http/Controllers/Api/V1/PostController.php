<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()->get()->map(fn (Post $p) => $this->toNodePost($p))->values();
        return response()->json(['message' => 'success', 'posts' => $posts], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        $validator = Validator::make($payload, [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'author' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $imageName = $this->storeImageFromRequest($request, 'post');

        $post = Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'author' => $request->input('author'),
            'image' => $imageName,
            'is_publish' => $request->boolean('isPublish', $request->boolean('is_publish', false)),
        ]);

        return response()->json(['message' => 'success', 'post' => $this->toNodePost($post)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json(['message' => 'success', 'post' => $this->toNodePost($post)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $payload = $request->only(['title', 'content', 'author']);
        if ($request->has('isPublish') || $request->has('is_publish')) {
            $payload['is_publish'] = $request->boolean('isPublish', $request->boolean('is_publish'));
        }

        $imageName = $this->storeImageFromRequest($request, 'post');
        if ($imageName) {
            $payload['image'] = $imageName;
        }

        $post->fill($payload);
        $post->save();

        return response()->json(['message' => 'success', 'post' => $this->toNodePost($post->fresh())], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted successfully'], 200);
    }

    private function toNodePost(Post $post): array
    {
        return [
            '_id' => (string) $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'author' => $post->author,
            'isPublish' => (bool) $post->is_publish,
            'Image' => $post->image,
            'createdAt' => optional($post->created_at)->toISOString(),
            'updatedAt' => optional($post->updated_at)->toISOString(),
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
