<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ManagerAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        return Auth()->user()->type() == "EM" ? $next($request) : abort(403);
    }
}
