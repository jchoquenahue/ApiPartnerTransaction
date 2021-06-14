<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Response;
class AuthenticateAccess
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validSecrets = explode(',',env('ACCEPTED_SECRETS'));
        if(in_array($request->header('Authorization'),$validSecrets)){
            return $next($request);
        }
        return $this->errorResponse('No autorizado',Response::HTTP_UNAUTHORIZED);

    }
}
