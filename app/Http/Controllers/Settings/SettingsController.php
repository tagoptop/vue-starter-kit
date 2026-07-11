<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the appearance settings page.
     */
    public function appearance(): View
    {
        return view('settings.appearance');
    }
}

