<?php

namespace App\Services;

use App\Support\WebService\ServiceResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class InternshipStatsClient
{
    /**
     * @return array{totalInternships:int, publishedInternships:int, draftInternships:int, closedInternships:int, totalVacancies:int}|null
     */
    public function fetch(): ?array
    {
        $requestId = (string) Str::uuid();
        $endpoint = (string) config('webservice.peers.internship_stats');

        if ($endpoint === '') {
            Log::warning('getInternshipStats has no configured endpoint', ['requestId' => $requestId]);

            return null;
        }

        try {
            $response = Http::timeout((int) config('webservice.timeout'))
                ->acceptJson()
                ->withHeaders(['X-Service-Key' => (string) config('webservice.key')])
                ->post($endpoint, [
                    'requestId' => $requestId,
                    'timeStamp' => ServiceResponse::now(),
                ]);
        } catch (ConnectionException $e) {
            Log::warning('getInternshipStats unreachable', [
                'requestId' => $requestId,
                'endpoint' => $endpoint,
                'reason' => $e->getMessage(),
            ]);

            return null;
        }

        $body = $response->json();

        if (! is_array($body)) {
            Log::warning('getInternshipStats returned a non-JSON body', [
                'requestId' => $requestId,
                'httpStatus' => $response->status(),
            ]);

            return null;
        }

        if (($body['status'] ?? null) !== ServiceResponse::SUCCESS) {
            Log::warning('getInternshipStats did not succeed', [
                'requestId' => $requestId,
                'status' => $body['status'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            return null;
        }

        return [
            'totalInternships' => (int) ($body['totalInternships'] ?? 0),
            'publishedInternships' => (int) ($body['publishedInternships'] ?? 0),
            'draftInternships' => (int) ($body['draftInternships'] ?? 0),
            'closedInternships' => (int) ($body['closedInternships'] ?? 0),
            'totalVacancies' => (int) ($body['totalVacancies'] ?? 0),
        ];
    }
}
