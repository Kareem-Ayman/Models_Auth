<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AssignGuard
{
    use GeneralTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard=null)
    {
        if($guard != null){
            auth()->shouldUse($guard); //shoud you user guard / table
            $token = $request->header('_token');
            $request->headers->set('_token', (string) $token, true);
            $request->headers->set('Authorization', 'Bearer '.$token, true);
            //return $this -> returnData("ll", Auth::guard()->user());
            try {
                $user = Auth::guard()->user();
                if(! $user){
                    return  $this -> returnError('401','Unauthenticated user!');
                }
                //$user = $this->auth->authenticate($request);  //check authenticted user
                //$user = JWTAuth::parseToken()->authenticate();
            } catch (TokenExpiredException $e) {
                return  $this -> returnError('401','Unauthenticated user');
            } catch (JWTException $e) {

                return  $this -> returnError('', 'Unauthenticated user');
            }

        }
        return $next($request);
    }
}
