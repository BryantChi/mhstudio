<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingCategory;
use App\Models\Service;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use ReordersItems;
    public function index(Request $request): View|JsonResponse
    {
        // 排序模式：回傳精簡 JSON 資料（依 type 篩選）
        if ($request->has('_sortable')) {
            $query = Service::ordered();
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            $items = $query->get(['id', 'title', 'order']);

            return response()->json($items);
        }

        $type = $request->get('type');

        $query = Service::with(['items' => fn ($q) => $q->active()->ordered()]);

        if ($type && in_array($type, ['website', 'hosting', 'maintenance', 'addon', 'consulting'])) {
            $query->ofType($type);
        }

        $services = $query->ordered()->paginate(20);

        $typeCounts = [
            'all' => Service::count(),
            'website' => Service::where('type', 'website')->count(),
            'hosting' => Service::where('type', 'hosting')->count(),
            'maintenance' => Service::where('type', 'maintenance')->count(),
            'addon' => Service::where('type', 'addon')->count(),
            'consulting' => Service::where('type', 'consulting')->count(),
        ];

        return view('admin.services.index', compact('services', 'type', 'typeCounts'));
    }

    public function create(): View
    {
        $pricingCategories = PricingCategory::active()->ordered()->get();

        return view('admin.services.create', compact('pricingCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services',
            'type' => 'nullable|string|max:50',
            'subtitle' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'tech_tags' => 'nullable|string',
            'price_range' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'price_label' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|in:once,yearly,monthly,hourly',
            'pages_min' => 'nullable|integer|min:0',
            'pages_max' => 'nullable|integer|min:0',
            'design_method' => 'nullable|string|max:255',
            'special_features_count' => 'nullable|integer|min:0',
            'cms_modules_count' => 'nullable|integer|min:0',
            'revisions' => 'nullable|integer|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'work_days_min' => 'nullable|integer|min:0',
            'work_days_max' => 'nullable|integer|min:0',
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_on_homepage' => 'boolean',
            'features_items' => 'nullable|array',
            'features_items.*' => 'nullable|string|max:500',
            'faq_questions' => 'nullable|array',
            'faq_questions.*' => 'nullable|string|max:500',
            'faq_answers' => 'nullable|array',
            'faq_answers.*' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.type' => 'nullable|in:included,highlighted,optional',
        ]);

        if (!empty($validated['tech_tags'])) {
            $validated['tech_tags'] = array_map('trim', explode(',', $validated['tech_tags']));
        }

        // Features：直接接收陣列
        $validated['features'] = array_values(array_filter(
            $request->input('features_items', []),
            fn($v) => trim($v) !== ''
        ));
        if (empty($validated['features'])) {
            $validated['features'] = null;
        }

        // FAQ：配對 Q&A
        $faqItems = [];
        $questions = $request->input('faq_questions', []);
        $answers = $request->input('faq_answers', []);
        foreach ($questions as $i => $q) {
            $q = trim($q);
            $a = trim($answers[$i] ?? '');
            if ($q && $a) {
                $faqItems[] = ['q' => $q, 'a' => $a];
            }
        }
        $validated['faq'] = !empty($faqItems) ? $faqItems : null;

        // Boolean 欄位
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['show_on_homepage'] = $request->boolean('show_on_homepage');

        // 自動指派排序值（表單送出 1-based，轉為 0-based 儲存）
        $validated['order'] = isset($validated['order']) ? max(0, $validated['order'] - 1) : (Service::max('order') ?? -1) + 1;

        // 取出 items 再建立
        $items = $validated['items'] ?? [];
        unset($validated['items'], $validated['features_items'], $validated['faq_questions'], $validated['faq_answers']);

        $service = Service::create($validated);
        $this->syncOrder(Service::class, $service->id, $service->order, $service->type ? ['type' => $service->type] : []);

        // 建立包含項目
        foreach ($items as $index => $item) {
            if (!empty($item['name'])) {
                $service->items()->create([
                    'name' => $item['name'],
                    'type' => $item['type'] ?? 'included',
                    'order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        flash_success('服務項目建立成功');

        return redirect(admin_list_url('admin.services.index'), $service->type ? ['type' => $service->type] : []);
    }

    public function edit(Service $service): View
    {
        $service->load('items');
        $pricingCategories = PricingCategory::active()->ordered()->get();

        return view('admin.services.edit', compact('service', 'pricingCategories'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,' . $service->id,
            'type' => 'nullable|string|max:50',
            'subtitle' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'tech_tags' => 'nullable|string',
            'price_range' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'price_label' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|in:once,yearly,monthly,hourly',
            'pages_min' => 'nullable|integer|min:0',
            'pages_max' => 'nullable|integer|min:0',
            'design_method' => 'nullable|string|max:255',
            'special_features_count' => 'nullable|integer|min:0',
            'cms_modules_count' => 'nullable|integer|min:0',
            'revisions' => 'nullable|integer|min:0',
            'warranty_months' => 'nullable|integer|min:0',
            'work_days_min' => 'nullable|integer|min:0',
            'work_days_max' => 'nullable|integer|min:0',
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_on_homepage' => 'boolean',
            'features_items' => 'nullable|array',
            'features_items.*' => 'nullable|string|max:500',
            'faq_questions' => 'nullable|array',
            'faq_questions.*' => 'nullable|string|max:500',
            'faq_answers' => 'nullable|array',
            'faq_answers.*' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.type' => 'nullable|in:included,highlighted,optional',
        ]);

        if (!empty($validated['tech_tags'])) {
            $validated['tech_tags'] = array_map('trim', explode(',', $validated['tech_tags']));
        }

        // Features
        $validated['features'] = array_values(array_filter(
            $request->input('features_items', []),
            fn($v) => trim($v) !== ''
        ));
        if (empty($validated['features'])) {
            $validated['features'] = null;
        }

        // FAQ
        $faqItems = [];
        $questions = $request->input('faq_questions', []);
        $answers = $request->input('faq_answers', []);
        foreach ($questions as $i => $q) {
            $q = trim($q);
            $a = trim($answers[$i] ?? '');
            if ($q && $a) {
                $faqItems[] = ['q' => $q, 'a' => $a];
            }
        }
        $validated['faq'] = !empty($faqItems) ? $faqItems : null;

        // Boolean 欄位
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['show_on_homepage'] = $request->boolean('show_on_homepage');

        // 表單送出 1-based，轉為 0-based 儲存
        if (isset($validated['order'])) {
            $validated['order'] = max(0, $validated['order'] - 1);
        }

        // 取出 items
        $items = $validated['items'] ?? [];
        unset($validated['items'], $validated['features_items'], $validated['faq_questions'], $validated['faq_answers']);

        $service->update($validated);
        $this->syncOrder(Service::class, $service->id, $service->order, $service->type ? ['type' => $service->type] : []);

        // 刪除舊項目並重建
        $service->items()->delete();
        foreach ($items as $index => $item) {
            if (!empty($item['name'])) {
                $service->items()->create([
                    'name' => $item['name'],
                    'type' => $item['type'] ?? 'included',
                    'order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        flash_success('服務項目更新成功');

        return redirect(admin_list_url('admin.services.index'), $service->type ? ['type' => $service->type] : []);
    }

    public function destroy(Service $service): RedirectResponse
    {
        $type = $service->type;
        $service->delete();
        $this->resequenceAfterDelete(Service::class, $type ? ['type' => $type] : []);
        flash_success('服務項目已刪除');

        return redirect(admin_list_url('admin.services.index'), $type ? ['type' => $type] : []);
    }

    /**
     * AJAX 拖拽排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:services,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Service::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
