<?php

namespace App\Services;

use App\Support\WebService\ServiceResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Consumes getInternshipStats, the web service exposed by the Internship module.
 *
 * The admin dashboard reports how many postings exist and how many vacancies they
 * carry. That is the Internship module's data, so User Management asks for it over
 * the Interface Agreement rather than querying App\Models\Internship itself.
 *
 * Every failure path returns null instead of throwing: the dashboard is a
 * read-only summary, and a teammate's service being down should degrade one panel,
 * not take the whole page with it. AdminController falls back when it gets null.
 */
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
            // Unreachable host or timeout — expected while a teammate's module is offline.
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

        // The contract says to trust `status`, not the HTTP code: a provider may
        // answer 200 with status F, and that is still not usable data.
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
