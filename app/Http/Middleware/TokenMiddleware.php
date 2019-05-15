<?php

namespace App\Http\Middleware;

use closure;

use Illuminate\Support\Facades\Redis;

class TokenMiddleware
{
    public function handle($request, Closure $next){
        $token = $_GET['token'];
        $id = $_GET['id'];
        $redis_token_key = "login_token";
        $redisToken=Redis::get($redis_token_key);
//        print_r($redisToken);die;
        if($token!=$redisToken){
            $response=[
                'error'=>50007,
                'msg'=>'Token值失效'
            ];
            return $response;
        }else{
            $response=[
                'error'=>0,
                'msg'=>'成功'
            ];
//            return response($response);
        }
        return $next($request);
    }
}
?>