<?php

namespace App\Http\Controllers;

class VendorSettingsController extends AdminSettingsController
{
    public function index()
    {
        return view('vendor.settings.index');
    }
}
