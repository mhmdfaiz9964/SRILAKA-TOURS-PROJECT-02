<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userCount = \App\Models\User::count();
        $bankCount = \App\Models\Bank::count();
        $chequeCount = \App\Models\Cheque::count();
        $recentCheques = \App\Models\Cheque::with('bank')->latest()->take(5)->get();

        return view('home', compact('userCount', 'bankCount', 'chequeCount', 'recentCheques'));
    }
}
