<?php

namespace App\Http\Middleware;

use App\Support\WebService\ServiceResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class VerifyServiceKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('webservice.key');
        $supplied = (string) $request->header('X-Service-Key', '');


        if ($expected === '') {
            return ServiceResponse::error('Web service is not configured.', $request->input('requestId'), 503);
        }


        if (! hash_equals($expected, $supplied)) {
            return ServiceResponse::error('Invalid or missing service key.', $request->input('requestId'), 401);
        }

        return $next($request);
    }
}
