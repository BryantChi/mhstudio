<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * 每分鐘最多允許的登入嘗試次數
     */
    protected int $maxAttempts = 5;

    /**
     * 鎖定時間（秒）
     */
    protected int $decaySeconds = 60;

    /**
     * 顯示登入表單
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * 處理登入請求（含速率限制）
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        // 速率限制檢查
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => "登入嘗試次數過多，請在 {$seconds} 秒後重試。",
            ]);
        }

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // 判斷輸入是 email 還是使用者名稱
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $fieldType => $login,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            flash_success('登入成功');

            return redirect()->intended(route('admin.dashboard'));
        }

        // 記錄失敗嘗試
        RateLimiter::hit($throttleKey, $this->decaySeconds);

        throw ValidationException::withMessages([
            'login' => '提供的憑證與我們的記錄不符。',
        ]);
    }

    /**
     * 登出
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        flash_success('已成功登出');

        return redirect()->route('login');
    }

    /**
     * 生成速率限制 Key
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('login')) . '|' . $request->ip()
        );
    }
}
