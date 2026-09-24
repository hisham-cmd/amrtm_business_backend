<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ComponentPlaygroundController extends Controller
{
    public function index(): View
    {
        abort_if(! config('app.debug'), 404);

        return view('update_service.components', [
            'pageTitle' => 'مصفوفة مكونات لوحة التحكم',
        ]);
    }
}