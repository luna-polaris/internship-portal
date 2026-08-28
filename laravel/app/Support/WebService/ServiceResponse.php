<?php

namespace App\Support\WebService;

use Illuminate\Http\JsonResponse;


class ServiceResponse
{
    /** The request was understood and the data is in the payload. */
    public const SUCCESS = 'S';

    /** The request was well-formed but could not be fulfilled (e.g. no such user). */
    public const FAIL = 'F';

    /** The request was rejected or something broke (validation, auth, exception). */
    public const ERROR = 'E';

    public static function success(array $payload, ?string $requestId = null): JsonResponse
    {
        return self::envelope(self::SUCCESS, $payload, $requestId, 200);
    }

    public static function fail(string $message, ?string $requestId = null, int $httpStatus = 404): JsonResponse
    {
        return self::envelope(self::FAIL, ['message' => $message], $requestId, $httpStatus);
    }

    public static function error(string $message, ?string $requestId = null, int $httpStatus = 422, array $extra = []): JsonResponse
    {
        return self::envelope(self::ERROR, ['message' => $message] + $extra, $requestId, $httpStatus);
    }

    public static function now(): string
    {
        return now()->format(config('webservice.timestamp_format'));
    }

    private static function envelope(string $status, array $payload, ?string $requestId, int $httpStatus): JsonResponse
    {

        $body = ['status' => $status];

        if ($requestId !== null && $requestId !== '') {
            $body['requestId'] = $requestId;
        }

        return response()->json($body + $payload + ['timeStamp' => self::now()], $httpStatus);
    }
}
