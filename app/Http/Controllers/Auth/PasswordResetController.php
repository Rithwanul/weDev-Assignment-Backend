<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Create user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Create(Request $request){
        // Rules for validating request
        $Rules = [
            'email'         =>          'required|string|email'
        ];

        // Validating request
        $request->validate($Rules);

        // Retrieving user info
        $user = User::where('email', $request->email)->first();

        // User not exists
        // Then through a response
        if(!$user){
            return response()->json([
                'message'       =>          "We can't find a user"
            ], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['email'        =>          $user->email],
            [
                'email'        =>          $user->email,
                'token'        =>          Str::random(60)
            ]
        );

        if ($user && $passwordReset){
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }

        return response()->json([
            'message'           =>          'We have emailed your password reset link'
        ]);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Find($token){
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        if (!$passwordReset)
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }
        return response()->json($passwordReset);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset)
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return response()->json([
                'message' => "We can't find a user with that e-mail address."
            ], 404);
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return response()->json($user);
    }
}
