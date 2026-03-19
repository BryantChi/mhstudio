<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * 開始模擬登入指定用戶
     * 僅限 super-admin 使用
     */
    public function start(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // 不能模擬自己
        if ($currentUser->id === $user->id) {
            flash_error('無法模擬登入自己的帳號');

            return redirect()->back();
        }

        // 不能模擬另一個 super-admin
        if ($user->isSuperAdmin()) {
            flash_error('無法模擬登入超級管理員帳號');

            return redirect()->back();
        }

        // 已在模擬中不能再次模擬
        if (session()->has('impersonator_id')) {
            flash_error('請先離開目前的模擬登入');

            return redirect()->back();
        }

        // 記錄原始用戶 ID 及名稱
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_name', $currentUser->name);

        Log::info('Impersonation started', [
            'impersonator' => $currentUser->id,
            'target' => $user->id,
            'target_name' => $user->name,
        ]);

        // 切換到目標用戶
        Auth::login($user);

        flash_info("正在模擬登入為「{$user->name}」");

        return redirect()->route('admin.dashboard');
    }

    /**
     * 離開模擬登入，恢復為原始用戶
     */
    public function leave(): RedirectResponse
    {
        $impersonatorId = session()->get('impersonator_id');

        if (! $impersonatorId) {
            flash_error('目前未處於模擬登入狀態');

            return redirect()->route('admin.dashboard');
        }

        $originalUser = User::find($impersonatorId);

        if (! $originalUser) {
            // 安全措施：找不到原始用戶時登出
            session()->forget(['impersonator_id', 'impersonator_name']);
            Auth::logout();

            return redirect()->route('login');
        }

        $impersonatedName = Auth::user()->name;

        Log::info('Impersonation ended', [
            'impersonator' => $impersonatorId,
            'was_impersonating' => Auth::id(),
        ]);

        // 清除模擬 session
        session()->forget(['impersonator_id', 'impersonator_name']);

        // 恢復原始用戶
        Auth::login($originalUser);

        flash_success("已離開模擬登入（{$impersonatedName}），已恢復為原始帳號");

        return redirect()->route('admin.dashboard');
    }
}
