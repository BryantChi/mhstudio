<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Client;
use App\Models\LegalPage;
use App\Models\ClientInteraction;
use App\Models\ContactMessage;
use App\Models\PricingCategory;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Mail\QuoteRequestNotification;
use App\Mail\QuoteRequestConfirmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * 顯示首頁
     */
    public function index(): View
    {
        // 首頁資料快取 10 分鐘，減少重複 DB 查詢
        $homeData = Cache::remember('homepage_data', 600, function () {
            return [
                'featuredProjects' => Project::published()->featured()->ordered()->take(3)->get(),
                'services' => Service::active()->ordered()->homepage()->get(),
                'testimonials' => Testimonial::active()->ordered()->get(),
                'latestArticles' => Article::published()
                    ->with(['author', 'category'])
                    ->orderByDesc('published_at')
                    ->take(3)
                    ->get(),
                'trustedClients' => Client::where('status', 'active')
                    ->whereNotNull('avatar')
                    ->where('avatar', '!=', '')
                    ->orderBy('name')
                    ->take(12)
                    ->get(),
            ];
        });

        return view('frontend.index', $homeData);
    }

    /**
     * 關於頁面
     */
    public function about(): View
    {
        return view('frontend.about');
    }

    /**
     * 部落格列表
     */
    public function blog(Request $request): View
    {
        $query = Article::published()
            ->with(['author', 'category', 'tags'])
            ->orderByDesc('published_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('tag')) {
            $tagSlug = $request->input('tag');
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        $articles = $query->paginate(9)->withQueryString();
        $categories = Category::active()->orderBy('order')->get();
        $popularTags = Tag::popular(15)->get();

        return view('frontend.blog.index', compact('articles', 'categories', 'popularTags'));
    }

    /**
     * 部落格文章詳情
     */
    public function blogShow(string $slug): View
    {
        $article = Article::published()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $article->incrementViews();

        $relatedArticles = Article::published()
            ->with(['author', 'category'])
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('frontend.blog.show', compact('article', 'relatedArticles'));
    }

    /**
     * 作品集列表
     */
    public function portfolio(): View
    {
        $projects = Project::published()->ordered()->get();

        return view('frontend.portfolio.index', compact('projects'));
    }

    /**
     * 作品集詳情
     */
    public function portfolioShow(string $slug): View
    {
        $project = Project::published()
            ->with(['images' => fn ($q) => $q->orderBy('order')])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProjects = \App\Models\Project::published()
            ->where('id', '!=', $project->id)
            ->where('category', $project->category)
            ->take(3)
            ->get();

        return view('frontend.portfolio.show', compact('project', 'relatedProjects'));
    }

    /**
     * 服務詳情
     */
    public function serviceShow(string $slug): View
    {
        $service = Service::active()
            ->with(['pricingCategory', 'items'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.services.show', compact('service'));
    }

    /**
     * 報價估算頁面
     */
    public function quote(Request $request): View
    {
        $categories = PricingCategory::active()
            ->ordered()
            ->with(['features' => fn($q) => $q->active()->ordered()])
            ->get();

        $preselectedCategory = $request->query('category');

        // 服務方案
        $servicePlans = Service::active()->ordered()->ofType('website')
            ->with(['items' => fn($q) => $q->active()->orderBy('order')])->get();
        $hostingPlans = Service::active()->ordered()->ofType('hosting')
            ->with(['items' => fn($q) => $q->active()->orderBy('order')])->get();
        $maintenancePlans = Service::active()->ordered()->ofType('maintenance')
            ->with(['items' => fn($q) => $q->active()->orderBy('order')])->get();
        $addonPlans = Service::active()->ordered()->ofType('addon')->get();

        return view('frontend.quote', compact(
            'categories',
            'preselectedCategory',
            'servicePlans',
            'hostingPlans',
            'maintenancePlans',
            'addonPlans'
        ));
    }

    /**
     * API: 取得定價資料 (公開)
     */
    public function pricingData(): JsonResponse
    {
        $data = Cache::remember('pricing_data', 3600, function () {
            $categories = PricingCategory::active()
                ->ordered()
                ->with(['features' => fn($q) => $q->active()->ordered()])
                ->get()
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description,
                        'base_price_min' => (float) $cat->base_price_min,
                        'base_price_max' => (float) $cat->base_price_max,
                        'icon' => $cat->icon,
                        'features' => $cat->features->map(function ($f) {
                            return [
                                'id' => $f->id,
                                'name' => $f->name,
                                'slug' => $f->slug,
                                'description' => $f->description,
                                'price_min' => (float) $f->price_min,
                                'price_max' => (float) $f->price_max,
                            ];
                        }),
                    ];
                });

            return [
                'categories' => $categories,
                'timeline_multipliers' => config('quote-pricing.timeline_multipliers'),
                'timeline_labels' => config('quote-pricing.timeline_labels'),
                'budget_labels' => config('quote-pricing.budget_labels'),
            ];
        });

        return response()->json($data);
    }

    /**
     * 處理報價請求提交
     */
    public function quoteRequestSubmit(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
            'project_type' => 'required|string|max:255',
            'selected_features' => 'required|string',
            'timeline' => 'required|string|in:1month,1-3months,3-6months,flexible',
            'budget' => 'required|string|in:under5,5-15,15-30,30plus',
            'estimated_min' => 'required|numeric|min:0',
            'estimated_max' => 'required|numeric|min:0',
        ]);

        // Decode selected_features JSON string
        $selectedFeatures = json_decode($validated['selected_features'], true) ?: [];

        // Create QuoteRequest
        $quoteRequest = QuoteRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $validated['company'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'project_type' => $validated['project_type'],
            'selected_features' => $selectedFeatures,
            'timeline' => $validated['timeline'],
            'budget' => $validated['budget'],
            'estimated_min' => $validated['estimated_min'],
            'estimated_max' => $validated['estimated_max'],
            'status' => 'pending',
        ]);

        // Client handling
        $client = Client::where('email', $validated['email'])->first();

        if ($client) {
            $quoteRequest->update(['client_id' => $client->id]);
            ClientInteraction::create([
                'client_id' => $client->id,
                'user_id' => null,
                'type' => 'other',
                'subject' => '提交網站報價請求',
                'content' => '報價請求編號：' . $quoteRequest->request_number . "\n估算金額：NT$ " . number_format($quoteRequest->estimated_min) . ' ~ NT$ ' . number_format($quoteRequest->estimated_max),
                'interaction_date' => now(),
            ]);
        } else {
            $client = Client::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'company' => $validated['company'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => 'lead',
                'source' => 'website',
                'tier' => 'standard',
            ]);
            $quoteRequest->update(['client_id' => $client->id]);
            ClientInteraction::create([
                'client_id' => $client->id,
                'user_id' => null,
                'type' => 'other',
                'subject' => '提交網站報價請求（新客戶）',
                'content' => '報價請求編號：' . $quoteRequest->request_number . "\n估算金額：NT$ " . number_format($quoteRequest->estimated_min) . ' ~ NT$ ' . number_format($quoteRequest->estimated_max),
                'interaction_date' => now(),
            ]);
        }

        // Send emails
        $adminEmail = config('quote-pricing.admin_notification_email') ?: config('mail.from.address');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new QuoteRequestNotification($quoteRequest));
        }
        Mail::to($quoteRequest->email)->send(new QuoteRequestConfirmation($quoteRequest));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '報價請求已送出！',
                'redirect' => route('quote-request.status', ['token' => $quoteRequest->token]),
            ]);
        }

        return redirect()->route('quote-request.status', ['token' => $quoteRequest->token])
            ->with('success', '報價請求已送出！我們會盡快與您聯繫。');
    }

    /**
     * 客戶查詢報價狀態
     */
    public function quoteStatus(string $token): View
    {
        $quoteRequest = QuoteRequest::where('token', $token)->with('quote')->firstOrFail();

        return view('frontend.quote-status', compact('quoteRequest'));
    }

    /**
     * 處理聯繫表單提交
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'project_type' => 'nullable|string|max:255',
            'budget' => 'nullable|string|max:50',
            'timeline' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
        ]);

        ContactMessage::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'project_type' => $request->input('project_type'),
            'budget' => $request->input('budget'),
            'timeline' => $request->input('timeline'),
            'message' => $request->input('message'),
            'status' => 'unread',
        ]);

        return redirect()->back()->with('success', '感謝您的訊息！我們會盡快與您聯繫。');
    }

    /**
     * 訂閱電子報
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        Subscriber::firstOrCreate(
            ['email' => $request->input('email')],
            [
                'name' => $request->input('name'),
                'status' => 'active',
                'subscribed_at' => now(),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => '訂閱成功！感謝您的關注。']);
        }

        return redirect()->back()->with('subscribe_success', '訂閱成功！感謝您的關注。');
    }

    /**
     * 取消訂閱電子報
     */
    public function unsubscribe(Request $request): View
    {
        $email = $request->email;
        $token = $request->token;

        if (hash('sha256', $email . config('app.key')) !== $token) {
            abort(403);
        }

        $subscriber = Subscriber::where('email', $email)->first();
        if ($subscriber) {
            $subscriber->unsubscribe();
        }

        return view('frontend.unsubscribed');
    }

    /**
     * 法律頁面（隱私權政策、服務條款等）
     */
    public function legalPage(string $slug): View
    {
        $legalPage = LegalPage::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $otherPages = LegalPage::active()
            ->where('id', '!=', $legalPage->id)
            ->ordered()
            ->get(['id', 'title', 'slug']);

        return view('frontend.legal.show', compact('legalPage', 'otherPages'));
    }
}
