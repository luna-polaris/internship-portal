<?php

namespace App\Http\Middleware;

use App\Support\WebService\ServiceResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates a calling module on the /api/ws endpoints.
 *
 * These are server-to-server calls, so there is no Sanctum token and no signed-in
 * user to check. The caller presents the shared key from config/webservice.php in
 * the X-Service-Key header instead.
 */
class VerifyServiceKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('webservice.key');
        $supplied = (string) $request->header('X-Service-Key', '');

        // Refuse outright if no key has been configured, so a blank .env value can
        // never be matched by an empty header and leave the services wide open.
        if ($expected === '') {
            return ServiceResponse::error('Web service is not configured.', $request->input('requestId'), 503);
        }

        // hash_equals compares in constant time, so a caller cannot recover the key
        // by measuring how long a wrong guess takes to be rejected.
        if (! hash_equals($expected, $supplied)) {
            return ServiceResponse::error('Invalid or missing service key.', $request->input('requestId'), 401);
        }

        return $next($request);
    }
}
