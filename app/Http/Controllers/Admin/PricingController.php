<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingCategory;
use App\Models\PricingFeature;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PricingController extends Controller
{
    public function index(): View
    {
        $categories = PricingCategory::ordered()->with(['features' => fn($q) => $q->ordered()])->get();
        $universalFeatures = PricingFeature::universal()->ordered()->get();

        return view('admin.pricing.index', compact('categories', 'universalFeatures'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price_min' => 'required|numeric|min:0',
            'base_price_max' => 'required|numeric|min:0|gte:base_price_min',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['order'] = PricingCategory::max('order') + 1;
        $validated['is_active'] = $request->boolean('is_active', true);

        PricingCategory::create($validated);

        return redirect()->route('admin.pricing.index')->with('success', '分類已建立');
    }

    public function updateCategory(Request $request, PricingCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price_min' => 'required|numeric|min:0',
            'base_price_max' => 'required|numeric|min:0|gte:base_price_min',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $category->update($validated);

        return redirect()->route('admin.pricing.index')->with('success', '分類已更新');
    }

    public function destroyCategory(PricingCategory $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.pricing.index')->with('success', '分類已刪除');
    }

    public function storeFeature(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price_min' => 'required|numeric|min:0',
            'price_max' => 'required|numeric|min:0|gte:price_min',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['order'] = PricingFeature::where('pricing_category_id', $request->pricing_category_id)->max('order') + 1;
        $validated['is_active'] = $request->boolean('is_active', true);

        PricingFeature::create($validated);

        return redirect()->route('admin.pricing.index')->with('success', '功能項目已建立');
    }

    public function updateFeature(Request $request, PricingFeature $feature): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price_min' => 'required|numeric|min:0',
            'price_max' => 'required|numeric|min:0|gte:price_min',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $feature->update($validated);

        return redirect()->route('admin.pricing.index')->with('success', '功能項目已更新');
    }

    public function destroyFeature(PricingFeature $feature): RedirectResponse
    {
        $feature->delete();
        return redirect()->route('admin.pricing.index')->with('success', '功能項目已刪除');
    }

    public function reorderCategories(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:pricing_categories,id']);
        foreach ($request->ids as $index => $id) {
            PricingCategory::where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function reorderFeatures(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:pricing_features,id']);
        foreach ($request->ids as $index => $id) {
            PricingFeature::where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }
}
