<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private const SUPPORTED = ['ar', 'en'];

    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'ar';
        }

        session(['locale' => $locale]);

        return redirect()->back()->withInput();
    }
}
