<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SecretariatController extends Controller
{
    public function dashboard()
    {
        return view('secretariat.dashboard');
    }
}
