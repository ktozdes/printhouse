<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;

use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){
        $user = User::where([
            ['name', $request->name],
        ])->first();
        $user->rollApiKey();
        if (isset($user) && Hash::check($request->password, $user->password)) {
            if ($user->active != '1'){
                return response()->json(['status' => 'error', 'message' => 'Этот пользователь не активен.'], 401);
            }
            return response()->json(['status' => 'success', 'token' =>$user->api_token ]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'Не правильный логин или пароль.'], 401);
        }
    }

    public function signBack(Request $request){
        $user = User::where('api_token', $request->api_token)->first();
        if (isset($user)) {
            //$user->rollApiKey();
            return response()->json(['status' => 'success', 'token' =>$user->api_token ]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }
    }

    public function getUser(Request $request) {
        return $request->user();
    }

    public function register(Request $request) {
        $tempRequest = $request->except(['repeatPassword', 'acceptCondition', 'trust', 'balance', 'rank']);
        
        $request->validate([
            'name'=>'required|unique:users',
            'email'=> 'required|unique:users',
            'phone1'=> 'required|unique:users',
            'company'=> 'required',
            'password'=> 'required'
        ]);

        $user = User::firstOrNew($tempRequest);
        $user->password = Hash::make($tempRequest['password']);
        $user->api_token = Str::random(60);
        $user->assignRole('client');
        
        if ($user->save()) {
            return response()->json(['status' => 'success', 'user' =>$user]);
        }
        else {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }
    }
}
