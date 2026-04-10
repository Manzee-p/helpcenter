<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect user to their role-specific dashboard.
     */
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'vendor' => redirect()->route('vendor.dashboard'),
            default  => redirect()->route('client.dashboard'),
        };
    }
}