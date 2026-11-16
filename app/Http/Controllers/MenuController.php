<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $availableOnly = $request->boolean('available');

        return view('pages.menu', [
            'categories' => MenuCategory::forMenu($availableOnly)->get(),
            'availableOnly' => $availableOnly,
        ]);
    }
}
