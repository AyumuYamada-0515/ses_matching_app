<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDeploymentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = (string) config('deployment_access.username');
        $password = (string) config('deployment_access.password');

        if ($username === '' || $password === '' || $request->is('up')) {
            return $next($request);
        }

        $providedUsername = (string) $request->getUser();
        $providedPassword = (string) $request->getPassword();

        if (! hash_equals($username, $providedUsername) || ! hash_equals($password, $providedPassword)) {
            return response('Authentication required.', 401, [
                'WWW-Authenticate' => 'Basic realm="SES Match Preview", charset="UTF-8"',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $next($request);
    }
}
