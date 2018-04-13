<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
class CheckAdmin
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
        //如果管理员没有登录,则返回登录界面
        if( !Auth::guard('admin')->check() ){
        return redirect('admin/login')->withErrors(['您尚未登录,请登录后访问']);
    }
        //登陆后返回上一个页面
        return $next($request);
    }

}


