<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * 列出所有媒體檔案
     */
    public function index(Request $request): View
    {
        $query = MediaItem::with('uploader');

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        // 類型篩選
        if ($request->filled('type')) {
            if ($request->type === 'images') {
                $query->images();
            } elseif ($request->type === 'documents') {
                $query->documents();
            }
        }

        $mediaItems = $query->latest()->paginate(24);

        // 統計資訊
        $stats = [
            'total_count' => MediaItem::count(),
            'total_size' => MediaItem::sum('size'),
            'images_count' => MediaItem::images()->count(),
            'documents_count' => MediaItem::documents()->count(),
        ];

        return view('admin.media.index', compact('mediaItems', 'stats'));
    }

    /**
     * 上傳檔案
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/' . date('Y/m'), $filename, 'public');

        // 驗證檔案確實寫入成功
        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => '檔案寫入失敗，請檢查 storage 目錄權限',
            ], 500);
        }

        $mediaItem = MediaItem::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => 'public',
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '檔案上傳成功',
            'media' => [
                'id' => $mediaItem->id,
                'original_name' => $mediaItem->original_name,
                'url' => $mediaItem->url,
                'human_size' => $mediaItem->human_size,
                'is_image' => $mediaItem->is_image,
                'mime_type' => $mediaItem->mime_type,
                'created_at' => $mediaItem->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * 更新替代文字
     */
    public function update(Request $request, MediaItem $mediaItem): JsonResponse
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
        ]);

        $mediaItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => '替代文字更新成功',
        ]);
    }

    /**
     * 刪除媒體檔案
     */
    public function destroy(MediaItem $mediaItem): JsonResponse
    {
        // 刪除實體檔案
        Storage::disk($mediaItem->disk)->delete($mediaItem->path);

        $mediaItem->delete();

        return response()->json([
            'success' => true,
            'message' => '檔案已刪除',
        ]);
    }

    /**
     * AJAX 瀏覽媒體（供 Media Picker 使用）
     */
    public function browse(Request $request): JsonResponse
    {
        $query = MediaItem::query();

        // 僅限圖片（picker 主要用途）
        if ($request->boolean('images_only', true)) {
            $query->images();
        }

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(24);

        return response()->json([
            'data' => $items->map(fn (MediaItem $item) => [
                'id' => $item->id,
                'url' => $item->url,
                'original_name' => $item->original_name,
                'alt_text' => $item->alt_text,
                'human_size' => $item->human_size,
                'mime_type' => $item->mime_type,
                'is_image' => $item->is_image,
                'created_at' => $item->created_at->format('Y-m-d'),
            ]),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'total' => $items->total(),
        ]);
    }

    /**
     * 批次刪除
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media_items,id',
        ]);

        $items = MediaItem::whereIn('id', $request->ids)->get();

        foreach ($items as $item) {
            Storage::disk($item->disk)->delete($item->path);
            $item->delete();
        }

        return response()->json([
            'success' => true,
            'message' => '已刪除 ' . $items->count() . ' 個檔案',
        ]);
    }
}
