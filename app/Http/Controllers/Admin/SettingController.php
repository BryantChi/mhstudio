<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * 顯示設定總覽
     */
    public function index(): View
    {
        $groups = Setting::select('group')
            ->distinct()
            ->pluck('group');

        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return view('admin.settings.index', compact('groups', 'settings'));
    }

    /**
     * 顯示一般設定
     */
    public function general(): View
    {
        $settings = Setting::getGroup('general');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * 更新一般設定
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'site_keywords' => 'nullable|string',
            'site_logo' => 'nullable|string',
            'site_favicon' => 'nullable|string',
            'admin_email' => 'required|email',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
        ]);

        Setting::setMany($validated, 'general');

        flash_success('一般設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示 SEO 設定
     */
    public function seo(): View
    {
        $settings = Setting::getGroup('seo');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * 更新 SEO 設定
     */
    public function updateSeo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // 預設 Meta Tags
            'default_meta_title'       => 'nullable|string|max:60',
            'default_meta_description' => 'nullable|string|max:160',
            'default_meta_keywords'    => 'nullable|string|max:500',
            'default_og_image'         => 'nullable|url|max:500',
            // 社群媒體
            'facebook_app_id'          => 'nullable|string|max:50',
            'twitter_username'         => 'nullable|string|max:50',
            'twitter_card_type'        => 'nullable|string|in:summary,summary_large_image',
            // 搜尋引擎驗證
            'google_verification'      => 'nullable|string|max:100',
            'bing_verification'        => 'nullable|string|max:100',
            'yandex_verification'      => 'nullable|string|max:100',
            // Schema.org
            'enable_schema'            => 'nullable|boolean',
            'schema_type'              => 'nullable|string|in:Article,BlogPosting,NewsArticle',
            'organization_name'        => 'nullable|string|max:255',
            'organization_logo'        => 'nullable|url|max:500',
            // 索引設定
            'allow_indexing'           => 'nullable|boolean',
            'auto_generate_meta'       => 'nullable|boolean',
            'generate_canonical'       => 'nullable|boolean',
            // Sitemap
            'sitemap_priority'         => 'nullable|numeric|between:0,1',
            'sitemap_changefreq'       => 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never',
        ]);

        // 處理 checkbox — 未勾選時不會出現在 request 中
        foreach (['enable_schema', 'allow_indexing', 'auto_generate_meta', 'generate_canonical'] as $boolKey) {
            if (!$request->has($boolKey)) {
                $validated[$boolKey] = '0';
            } else {
                $validated[$boolKey] = '1';
            }
        }

        Setting::setMany($validated, 'seo');

        flash_success('SEO 設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示分析設定
     */
    public function analytics(): View
    {
        $settings = Setting::getGroup('analytics');

        return view('admin.settings.analytics', compact('settings'));
    }

    /**
     * 更新分析設定
     */
    public function updateAnalytics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'analytics_enabled' => 'boolean',
            'analytics_google_id' => 'nullable|string',
            'analytics_view_id' => 'nullable|string',
            'analytics_track_admin' => 'boolean',
        ]);

        Setting::setMany($validated, 'analytics');

        flash_success('分析設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示郵件設定
     */
    public function mail(): View
    {
        $settings = Setting::getGroup('mail');

        return view('admin.settings.mail', compact('settings'));
    }

    /**
     * 更新郵件設定
     */
    public function updateMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        Setting::setMany($validated, 'mail');

        flash_success('郵件設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示前台設定
     */
    public function frontend(): View
    {
        $settings = Setting::getGroup('frontend');

        return view('admin.settings.frontend', compact('settings'));
    }

    /**
     * 更新前台設定
     */
    public function updateFrontend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title'              => 'required|string|max:255',
            'hero_subtitle'           => 'required|string|max:255',
            'hero_tagline'            => 'required|string|max:255',
            'hero_description'        => 'required|string',
            'stats_years_experience'  => 'required|integer|min:0',
            'stats_projects_completed'=> 'required|integer|min:0',
            'stats_happy_clients'     => 'required|integer|min:0',
            'stats_ontime_delivery'   => 'required|integer|min:0|max:100',
            'contact_email'           => 'required|email',
            'contact_location'        => 'required|string|max:255',
            'social_github'           => 'nullable|string|max:500',
            'social_github_enabled'   => 'nullable|boolean',
            'social_linkedin'         => 'nullable|string|max:500',
            'social_linkedin_enabled' => 'nullable|boolean',
            'social_line'             => 'nullable|string|max:500',
            'social_line_enabled'     => 'nullable|boolean',
            'line_id'                 => 'nullable|string|max:100',
            'line_qrcode_url'         => 'nullable|string|max:500',
            'social_facebook'         => 'nullable|string|max:500',
            'social_facebook_enabled' => 'nullable|boolean',
            'social_twitter'          => 'nullable|string|max:500',
            'social_twitter_enabled'  => 'nullable|boolean',
            'social_instagram'        => 'nullable|string|max:500',
            'social_instagram_enabled'=> 'nullable|boolean',
            'social_youtube'          => 'nullable|string|max:500',
            'social_youtube_enabled'  => 'nullable|boolean',
            'section_stats_enabled'     => 'nullable|boolean',
            'section_services_enabled'  => 'nullable|boolean',
            'section_portfolio_enabled' => 'nullable|boolean',
            'section_process_enabled'   => 'nullable|boolean',
            'section_techstack_enabled' => 'nullable|boolean',
            'newsletter_enabled'        => 'nullable|boolean',
            'social_embed_enabled'      => 'nullable|boolean',
            'social_youtube_embed'    => 'nullable|string|max:500',
            'social_instagram_embed'  => 'nullable|string|max:500',
            'tech_stack_names'        => 'nullable|array',
            'tech_stack_names.*'      => 'nullable|string|max:100',
            'tech_stack_types'        => 'nullable|array',
            'tech_stack_types.*'      => 'nullable|string|max:100',
        ]);

        // Remove tech_stack fields from validated (handled separately)
        unset($validated['tech_stack_names'], $validated['tech_stack_types']);

        Setting::setMany($validated, 'frontend');

        // Handle tech_stack as array input
        $techNames = $request->input('tech_stack_names', []);
        $techTypes = $request->input('tech_stack_types', []);
        $techStack = [];
        foreach ($techNames as $i => $name) {
            $name = trim($name);
            $type = trim($techTypes[$i] ?? '');
            if ($name && $type) {
                $techStack[] = ['name' => $name, 'type' => $type];
            }
        }
        $setting = Setting::firstOrNew(['key' => 'tech_stack']);
        $setting->value = json_encode($techStack);
        $setting->group = 'frontend';
        $setting->type = 'json';
        $setting->save();

        flash_success('前台設定更新成功');

        return redirect()->back();
    }

    /**
     * 顯示公司資訊設定
     */
    public function company(): View
    {
        $settings = Setting::getGroup('company');

        return view('admin.settings.company', compact('settings'));
    }

    /**
     * 更新公司資訊
     */
    public function updateCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_name_full' => 'nullable|string|max:255',
            'company_owner' => 'required|string|max:255',
            'company_phone' => 'required|string|max:50',
            'company_email' => 'required|email|max:255',
            'company_address' => 'required|string|max:500',
            'company_id_number' => 'nullable|string|max:50',
            'company_website' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:20',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
        ]);

        Setting::setMany($validated, 'company');

        flash_success('公司資訊更新成功');

        return redirect()->back();
    }

    /**
     * 清除快取
     */
    public function clearCache(): RedirectResponse
    {
        \Illuminate\Support\Facades\Cache::flush();

        flash_success('快取已清除');

        return redirect()->back();
    }

    /**
     * 建立自訂設定
     */
    public function create(): View
    {
        return view('admin.settings.create');
    }

    /**
     * 儲存自訂設定
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => 'required|string|max:50',
            'key' => 'required|string|max:100|unique:settings',
            'value' => 'required|string',
            'type' => 'required|in:string,integer,boolean,array,json',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
            'is_editable' => 'boolean',
        ]);

        Setting::create($validated);

        flash_success('設定建立成功');

        return redirect(admin_list_url('admin.settings.index'));
    }

    /**
     * 編輯自訂設定
     */
    public function edit(Setting $setting): View
    {
        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * 更新自訂設定
     */
    public function update(Request $request, Setting $setting): RedirectResponse
    {
        if (!$setting->is_editable) {
            flash_error('此設定不可編輯');
            return redirect()->back();
        }

        $validated = $request->validate([
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $setting->update($validated);

        flash_success('設定更新成功');

        return redirect(admin_list_url('admin.settings.index'));
    }

    /**
     * 刪除自訂設定
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        if (!$setting->is_editable) {
            flash_error('此設定不可刪除');
            return redirect()->back();
        }

        $setting->delete();

        flash_success('設定刪除成功');

        return redirect(admin_list_url('admin.settings.index'));
    }
}
