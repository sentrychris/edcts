<?php

namespace App\Http\Middleware;

use App\Mail\Transport\APITransport;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyTransport
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $transport = app('mail.manager')->driver()->getSymfonyTransport();

        if ($transport instanceof APITransport) {
            $request->merge(['isAPIMailPayload' => true]);
        }

        return $next($request);
    }
}
