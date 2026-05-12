<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->isSecure()) {
            $httpsUrl = 'https://' . $request->getHttpHost() . $request->getRequestUri();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'HTTPS required.'], 426);
            }

            return redirect()->to($httpsUrl, 308);
        }

        return $next($request);
    }
}
