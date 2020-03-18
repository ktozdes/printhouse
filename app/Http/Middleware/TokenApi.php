<?php

namespace App\Http\Middleware;
use App\User;
use Illuminate\Support\Facades\Auth;
use Closure;

class TokenApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');
        if (empty($token)) {
            $token = $request->get('token');
        }
        $user = User::where('api_token', $token)->first();
        if (isset($user)) {
            $user->pricing;
            $request->merge(['user' => $user ]);
            Auth::login($user);
            //add this
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            return $next($request);
        }
        return response()->json(['status' => 'error', 'message' => 'Неправильный токен.'], 401);
    }
}
