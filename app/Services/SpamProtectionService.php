<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpamProtectionService
{
    /**
     * Run all spam checks.
     * Returns null if clean, or error message string if spam detected.
     */
    public function check(Request $request): ?string
    {
        // Layer 1: Honeypot — hidden field bots will auto-fill
        if ($this->isHoneypotFilled($request)) {
            $this->logSpam('honeypot', $request);

            return 'spam_detected';
        }

        // Layer 2: Time-based — form submitted too fast (< 3 seconds)
        if ($this->isSubmittedTooFast($request)) {
            $this->logSpam('too_fast', $request);

            return '表單提交過快，請稍後再試。';
        }

        // Layer 3: Content filtering — spam keywords, excessive URLs, suspicious chars
        $contentResult = $this->detectSpamContent($request);
        if ($contentResult) {
            $this->logSpam("content:{$contentResult}", $request);

            return '訊息內容包含可疑內容，請修改後重試。';
        }

        // Layer 4: reCAPTCHA v3 (optional, configurable via settings)
        if ($this->isRecaptchaEnabled()) {
            if (! $this->verifyRecaptcha($request)) {
                $this->logSpam('recaptcha_failed', $request);

                return '安全驗證失敗，請重新整理頁面後再試。';
            }
        }

        return null;
    }

    /* ===== Layer 1: Honeypot ===== */

    protected function isHoneypotFilled(Request $request): bool
    {
        // The field "website_url" is hidden via CSS, humans will never fill it
        return $request->filled('website_url');
    }

    /* ===== Layer 2: Time-based Token ===== */

    protected function isSubmittedTooFast(Request $request): bool
    {
        $token = $request->input('_form_token');
        if (! $token) {
            return true; // Missing token = suspicious
        }

        try {
            $timestamp = decrypt($token);
            $elapsed = time() - (int) $timestamp;

            return $elapsed < 3; // Less than 3 seconds is bot-like
        } catch (\Exception $e) {
            return true; // Invalid/tampered token
        }
    }

    /* ===== Layer 3: Content Spam Detection ===== */

    protected function detectSpamContent(Request $request): ?string
    {
        $name = $request->input('name', '');
        $email = $request->input('email', '');
        $message = $request->input('message', '');
        $combined = "{$name} {$email} {$message}";

        // 3a. Excessive URLs (> 2 in total)
        $urlCount = preg_match_all('/https?:\/\/|www\./i', $combined);
        if ($urlCount > 2) {
            return 'too_many_urls';
        }

        // 3b. Common English spam keywords
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|lottery|slot.?machine)\b/i',
            '/\b(bitcoin.{0,20}invest|crypto.{0,20}profit|earn.{0,20}money.{0,20}fast)\b/i',
            '/\b(SEO\s+service|buy\s+backlinks|link\s+building|cheap\s+traffic|web\s+traffic)\b/i',
            '/\b(click\s+here|act\s+now|limited\s+time\s+offer|congratulations.{0,20}winner)\b/i',
            '/\b(payday\s+loan|work\s+from\s+home|make\s+money\s+online|free\s+money)\b/i',
            '/\b(adult|porn|xxx|sex\s+toy|dating\s+site)\b/i',
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $combined)) {
                return 'spam_keywords';
            }
        }

        // 3c. Cyrillic-heavy content (common in spam targeting Asian sites)
        $cyrillicCount = preg_match_all('/[\x{0400}-\x{04FF}]/u', $combined);
        if ($cyrillicCount > 5) {
            return 'suspicious_characters';
        }

        // 3d. Excessive ALL-CAPS in message (> 70% uppercase, min 20 chars)
        if (strlen($message) > 20) {
            $upperCount = preg_match_all('/[A-Z]/', $message);
            $letterCount = preg_match_all('/[a-zA-Z]/', $message);
            if ($letterCount > 10 && ($upperCount / $letterCount) > 0.7) {
                return 'excessive_caps';
            }
        }

        // 3e. Repeated characters (e.g. "aaaaaaaaaa")
        if (preg_match('/(.)\1{9,}/', $combined)) {
            return 'repeated_characters';
        }

        // 3f. Name contains URL
        if (preg_match('/https?:\/\/|www\./i', $name)) {
            return 'url_in_name';
        }

        // 3g. Too many email addresses in message body
        $emailCount = preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message);
        if ($emailCount > 2) {
            return 'too_many_emails_in_message';
        }

        return null;
    }

    /* ===== Layer 4: reCAPTCHA v3 (Optional) ===== */

    protected function isRecaptchaEnabled(): bool
    {
        return ! empty(config('services.recaptcha.secret_key'))
            && setting('recaptcha_enabled', '0') === '1';
    }

    protected function verifyRecaptcha(Request $request): bool
    {
        $token = $request->input('g-recaptcha-response');
        if (! $token) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            $success = $result['success'] ?? false;
            $score = $result['score'] ?? 0;
            $action = $result['action'] ?? '';

            // Score threshold: 0.5 (0.0 = bot, 1.0 = human)
            return $success && $score >= 0.5 && $action === 'contact';
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', ['error' => $e->getMessage()]);

            return true; // Fail open — don't block users if Google is down
        }
    }

    /* ===== Logging ===== */

    protected function logSpam(string $reason, Request $request): void
    {
        Log::channel('single')->warning('Spam blocked', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /* ===== Helper: Generate form token ===== */

    public static function generateFormToken(): string
    {
        return encrypt(time());
    }
}
