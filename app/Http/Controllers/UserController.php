<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);
        $apiToken = $this->apiTokenGenerator();
        $validatedData['api_token'] = $apiToken;
        $validatedData['password'] = Hash::make($validatedData['password']);
        User::create($validatedData);
        return response()->json([
            'status' => 'success',
            'message' => 'ثبت نام با موفقیت انجام شد',
            'api_token' => $apiToken
        ]);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($validatedData)) {
            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'message' => 'با موفقیت وارد شدید',
                'api_token' => $user->api_token
            ]);
        }
        else {
            return response()->json([
                'status' => 'error',
                'message' => 'ایمیل یا کلمه عبور نادرست است',
            ]);
        }
    }

    protected function apiTokenGenerator()
    {
        do {
            $token = Str::random(60);
            $checkTokenExistsOrNot = User::where('api_token' , $token)->exists();
        } while ($checkTokenExistsOrNot);

        return $token;
    }
}
