<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function restaurant(Request $request): View
    {
        return view('restaurant');
    }

    public function stats(): View
    {
        return view('stats');
    }

    public function search(): View
    {
        return view('search');
    }
}
