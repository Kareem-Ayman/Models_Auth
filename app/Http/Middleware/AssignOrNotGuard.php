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

class AssignOrNotGuard
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
            if(isset($token)){

                $request->headers->set('token', (string) $token, true);
                $request->headers->set('Authorization', 'Bearer '.$token, true);
                try {
                    if (Auth::guard($guard)->user()) {
                        $token = JWTAuth::parseToken()->authenticate();
                        if(! $token){
                            return $this->returnErrorResponse("Unauthenticated user!", 401);
                        }
                    }else{
                        return $this->returnErrorResponse("You are not user!", 401);
                    }

                    //$user = $this->auth->authenticate($request);  //check authenticted user
                    //$user = JWTAuth::parseToken()->authenticate();
                    return $next($request);

                } catch (TokenExpiredException $e) {
                    return  $this -> returnErrorResponse('Token Expired',401);
                } catch (JWTException $e) {
                    //return $this->returnData("data", dd($e));
                    return  $this -> returnErrorResponse('Unauthenticated user',401);
                }

            }else{
                return $next($request);

            }

        }


    }
}