<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Exception;
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
            $token = $request->header('token');
            $request->headers->set('token', (string) $token, true);
            $request->headers->set('Authorization', 'Bearer '.$token, true);
            try {
                if (Auth::guard($guard)->user()) {
                    $token = JWTAuth::parseToken()->authenticate();
                    if(! $token){
                        return $this->returnError("s001", "Unauthenticated user!");
                    }
                }else{
                    return $this->returnError("s001", "You are not user!");
                }

                //$user = $this->auth->authenticate($request);  //check authenticted user
                //$user = JWTAuth::parseToken()->authenticate();
                return $next($request);

            } catch (TokenExpiredException $e) {
                return  $this -> returnError('401','Token Expired');
            } catch (JWTException $e) {
                //return $this->returnData("data", dd($e));
                return  $this -> returnError('401', 'Unauthenticated user');
            }

        }


    }
}
