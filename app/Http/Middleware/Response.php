<?php

namespace App\Http\Middleware;

use Closure;
use App\Plate;

class Response
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
        $response = $next($request);

        //Check if the response is JSON

        if ($request->user->can('menu dashboard')) {
            $response->setContent(
                json_encode(
                    array_merge(
                        json_decode($response->content(), true),
                        [
                            'plate_number' => Plate::all('name', 'quantity')
                        ]
                    )
                )
            );
        }
        return $response;
    }
}
