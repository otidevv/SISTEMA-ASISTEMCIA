<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the registration form.
     * Redirects to the home page with a parameter to open the public postulation modal.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showRegistrationForm()
    {
        return redirect()->route('home', ['postula' => 1]);
    }
}

