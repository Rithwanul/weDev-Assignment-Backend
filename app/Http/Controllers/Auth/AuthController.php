<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SignupActivate;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
   public function SignUp(Request $request){

       // Validation Rules
       $rules = [
           'name'           =>          'required|string',
           'email'          =>          'required|string|email|unique:users',
           'password'       =>          'required|string|confirmed'
       ];

       // Validating request with rules
       $request->validate($rules);

       // Create User model
       $user = new User();
       $user->name                  =           $request->name;
       $user->email                 =           $request->email;
       $user->password              =           bcrypt($request->password);
       $user->activation_token      =           Str::random(60);
       $result                      =           $user->save();

       $mail = new SignupActivate($user);
       $user->notify($mail);


       return response()->json([
           'message'        =>      $result
       ], 201);
   }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] access_token
     */
    public function login(Request $request){
        // Validation rules
        $rules  =   [
            'email'         =>          'required|string|email',
            'password'      =>          'required|string',
            'remember_me'   =>          'boolean'
        ];

        //Validating request
        $request->validate($rules);

        // Setting Credentials for login
        $credantials['email']           =           $request->email;
        $credantials['password']        =           $request->password;
        $credantials['active']          =           1;
        $credantials['deleted_at']      =           null;


        // Check is user authenticate or not
        if (!Auth::attempt($credantials)){
            return response()->json([
                'message'       =>          'Unauthorized'
            ], 401);
        }

        //creating token for the authenticate user
        $user           =       $request->user();
        $tokenResult    =       $user->createToken('Personal Access Token');
        $token          =       $tokenResult->token;

        //Setting time for JWT token
        if($request->remember_me){
            $token->expires_at      =           Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'message'           =>      'Success',
            'user'              =>      $user,
            'access_token'      =>      $tokenResult->accessToken,
            'token_type'        =>      'Bearer',
            'expires_at'        =>      Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Logout(Request $request){
        $result = $request->user()->token()->revoke();

        return response()->json([
            'message'       =>          $result
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [json] user object
     */
    public function User(Request $request){
        return response()->json($request->user());
    }

    /**
     * Get the authenticated User
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse [json] user object
     */
    public function SignupActivate($token){

        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }

        if($token == $user->activation_token){
            $user->active = true;
            $user->activation_token = '';
        }else{
            return response()->json([
                'message'   =>  'Account not Activated'
            ]);
        }
        $user->save();
        return response()->json([
            'message'       =>      true
        ]);
    }
}
