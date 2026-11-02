<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::with(['items' => function ($q) {
            $q->orderBy('sort_order');
        }])
            ->orderBy('sort_order')
            ->get();

        return view('pages.menu', compact('categories'));
    }
}
