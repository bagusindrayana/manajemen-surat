<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function auth(Request $request)
    {
        //check auth
        $credentials = $request->only('username', 'password');

        if (auth()->attempt($credentials)) {
            // Authentication passed...
            //check intended url
            if ($request->session()->has('url.intended')) {
                return redirect()->intended();
            }
            return redirect()->route('home');
        }

        return redirect()->route('login')->with('error', 'Username or password is incorrect');
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }
}