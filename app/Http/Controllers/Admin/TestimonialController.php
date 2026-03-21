<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use ReordersItems;
    public function index(Request $request): View|JsonResponse
    {
        // 排序模式：回傳精簡 JSON 資料
        if ($request->has('_sortable')) {
            $items = Testimonial::ordered()->get(['id', 'client_name', 'order']);
            return response()->json($items);
        }

        $testimonials = Testimonial::ordered()->paginate(15);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'avatar' => 'nullable|string',
            'project_type' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        // 表單送出 1-based，轉為 0-based 儲存
        $validated['order'] = isset($validated['order']) ? max(0, $validated['order'] - 1) : (Testimonial::max('order') ?? -1) + 1;

        $testimonial = Testimonial::create($validated);
        $this->syncOrder(Testimonial::class, $testimonial->id, $testimonial->order);

        flash_success('客戶評價建立成功');

        return redirect(admin_list_url('admin.testimonials.index'));
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'avatar' => 'nullable|string',
            'project_type' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        // 表單送出 1-based，轉為 0-based 儲存
        if (isset($validated['order'])) {
            $validated['order'] = max(0, $validated['order'] - 1);
        }

        $testimonial->update($validated);
        $this->syncOrder(Testimonial::class, $testimonial->id, $testimonial->order);

        flash_success('客戶評價更新成功');

        return redirect(admin_list_url('admin.testimonials.index'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();
        flash_success('客戶評價已刪除');

        return redirect(admin_list_url('admin.testimonials.index'));
    }

    /**
     * AJAX 拖曳排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:testimonials,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Testimonial::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
