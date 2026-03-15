<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch the application locale.
     */
    public function switch(Request $request, string $locale)
    {
        if (! in_array($locale, ['zh_TW', 'en'])) {
            abort(400);
        }

        session()->put('locale', $locale);

        return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
