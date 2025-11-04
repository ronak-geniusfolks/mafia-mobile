<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Checkuser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->id === 4) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Unauthorized Access');
    }
}
