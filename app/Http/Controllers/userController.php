<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class userController extends Controller
{


    public function putUserPassword(Request $request)
    {
        $validatedData=$request->validate([
        'password'=>'required|min:8|confirmed|string',
        ]);
        $email='omar12@gmail.com';    //ايميل المتقدم ,نستطيع الوصول اليه من خلال العلاقات
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }
        $user->password = Hash::make($request->password);
        $user->status='active';
        $user->save();

        return response()->json([
            'message' => 'Password put successfully.',
            'status_code' => 200
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(
                [
                    'message' => 'Envalid email Or Password. ',
                    'status_code' => 400
                ],
                400
            );
        }


        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successfully.',
            'data' => [
                'user' => $user,
            ],
            'Token' => $token,
            'status_code' => 200
        ], 200);


    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout Successfuly. ',
            'status_code' => 200
        ], 200);
    }

    
}
