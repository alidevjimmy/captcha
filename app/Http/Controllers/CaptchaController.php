<?php

namespace App\Http\Controllers;


use App\Captcha;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    public function newCaptcha(Request $request)
    {
        $request->validate([
            'api_token' => 'required',
        ]);
        $user = User::where('api_token' , $request->api_token)->first();
        if (!$user || $user == null || $user == []) {
            return response()->json([
               'status' => 'error',
               'message' => 'api_token معتبر نمی باشد.'
            ]);
        }
        $authToken = $this->authTokenGenerator();
        $code = strtolower(Str::random(5));
        Captcha::create([
            'code' => $code,
            'auth_token' => $authToken,
            'user_id' => $user->id
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'کپچا ساخته شد'
        ]);
    }

    public function checkCaptcha(Request $request)
    {
        $request->validate([
            'captcha' => 'required',
            'api_token' => 'required',
            'auth_token' => 'required'
        ]);
        $user = User::where('api_token' , $request->api_token)->first();
        if (!$user || $user == null || $user == []) {
            return response()->json([
                'status' => 'error',
                'message' => 'api_token معتبر نمی باشد.',
                'exception_code' => 1002
            ]);
        }
        $captcha = Captcha::where('auth_token' , $request->auth_token)->first();
        if (!$captcha || $captcha == null || $captcha == []) {
            return response()->json([
                'status' => 'error',
                'message' => 'auth_token معتبر نمی باشد.',
                'exception_code' => 1001
            ]);
        }
        // code will expire after 5 min
        if (strtotime(Carbon::now()) > strtotime($captcha->created_at) + 5*60) {
            return response()->json([
                'status' => 'error',
                'message' => 'کد منقضی شده است',
                'exception_code' => 1003
            ]);
        }
        if ($captcha->used != 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'امکان استفاده از این کد وجود ندارد',
                'exception_code' => 1005
            ]);
        }
        if ($captcha->code == strtolower($request->captcha)) {
            Captcha::where('auth_token' , $request->auth_token)->update([
                'used' => 1
            ]);
            return response()->json([
               'status' => 'success',
               'message' => 'کد صحیح است',
                'exception_code' => 1004
            ]);
        }
        Captcha::where('auth_token' , $request->auth_token)->update([
            'used' => 1
        ]);
        return response()->json([
           'status' => 'success',
            'message' => 'کد وارد شد صحیح نمی باشد',
            'exception_code' => 1004
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'api_token' => 'required',
            'auth_token' => 'required'
        ]);
        $user = User::where('api_token' , $request->api_token)->first();
        if (!$user || $user == null || $user == []) {
            return response()->json([
                'status' => 'error',
                'message' => 'api_token معتبر نمی باشد.',
                'exception_code' => 1002
            ]);
        }
        $captcha = Captcha::where('auth_token' , $request->auth_token)->first();
        if (!$captcha || $captcha == null || $captcha == []) {
            return response()->json([
                'status' => 'error',
                'message' => 'auth_token معتبر نمی باشد.',
                'exception_code' => 1001
            ]);
        }
        $captcha->update([
            'used' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'code' => strtolower(Str::random(5))
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'کد بروز شد',
            'exception_code' => 1004
        ]);
    }

    protected function authTokenGenerator()
    {
        do {
            $token = Str::random(60);
            $checkTokenExistsOrNot = Captcha::where('auth_token' , $token)->exists();
        } while ($checkTokenExistsOrNot);

        return $token;
    }
}
