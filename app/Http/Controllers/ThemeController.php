<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'theme_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $user = Auth::user();
        $user->theme_color = $request->theme_color;
        $user->save();

        return response()->json([
            'success'     => true,
            'theme_color' => $user->theme_color,
        ]);
    }
}
