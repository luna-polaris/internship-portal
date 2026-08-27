<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InternshipStatsClient;
use App\Support\WebService\ServiceResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebServiceTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-service-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['webservice.key' => self::KEY]);
    }

    private function headers(): array
    {
        return ['X-Service-Key' => self::KEY];
    }

    private function student(): User
    {
        $user = User::create([
            'full_name' => 'Tan Wei Ming',
            'email' => 'weiming@student.tarc.edu.my',
            'password' => 'Str0ng!Passw0rd#1',
            'phone' => '0123456789',
            'role' => 'Student',
            'status' => 'Active',
        ]);

        $user->student()->create([
            'matric_no' => 'M2301234',
            'university' => 'TARUMT',
            'programme' => 'RSF',
        ]);

        return $user;
    }

    // ---------------------------------------------------------------- exposure

    public function test_get_user_info_returns_contact_details_for_flag_1(): void
    {
        $user = $this->student();

        $response = $this->withHeaders($this->headers())->postJson('/api/ws/user-info', [
            'userId' => (string) $user->user_id,
            'queryFlag' => 1,
            'timeStamp' => now()->format('Y-m-d H:i:s'),
            'requestId' => 'REQ-0001',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'S')
            ->assertJsonPath('requestId', 'REQ-0001')
            ->assertJsonPath('userName', 'Tan Wei Ming')
            ->assertJsonPath('userEmail', 'weiming@student.tarc.edu.my')
            ->assertJsonPath('userRole', 'Student')
            ->assertJsonPath('userDetails.hpNo', '0123456789');

        // Flag 1 is contact only, so profile fields must be absent.
        $response->assertJsonMissingPath('userDetails.matricNo');

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $response->json('timeStamp')
        );
    }

    public function test_get_user_info_returns_profile_for_flag_2_and_both_for_flag_3(): void
    {
        $user = $this->student();
        $base = ['userId' => (string) $user->user_id, 'timeStamp' => now()->format('Y-m-d H:i:s')];

        $this->withHeaders($this->headers())
            ->postJson('/api/ws/user-info', $base + ['queryFlag' => 2])
            ->assertOk()
            ->assertJsonPath('userDetails.matricNo', 'M2301234')
            ->assertJsonPath('userDetails.university', 'TARUMT')
            ->assertJsonMissingPath('userDetails.hpNo');

        $this->withHeaders($this->headers())
            ->postJson('/api/ws/user-info', $base + ['queryFlag' => 3])
            ->assertOk()
            ->assertJsonPath('userDetails.hpNo', '0123456789')
            ->assertJsonPath('userDetails.matricNo', 'M2301234');
    }

    public function test_unknown_user_returns_status_f(): void
    {
        $this->withHeaders($this->headers())
            ->postJson('/api/ws/user-info', [
                'userId' => '999999',
                'queryFlag' => 1,
                'timeStamp' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertNotFound()
            ->assertJsonPath('status', 'F');
    }

    public function test_invalid_request_returns_status_e_in_the_contract_envelope(): void
    {
        $this->withHeaders($this->headers())
            ->postJson('/api/ws/user-info', [
                'userId' => 'not-a-number',
                'queryFlag' => 9,
                'timeStamp' => '26-08-2026',
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'E')
            ->assertJsonStructure(['status', 'message', 'errors', 'timeStamp']);
    }

    public function test_service_key_is_required(): void
    {
        $payload = [
            'userId' => '1',
            'queryFlag' => 1,
            'timeStamp' => now()->format('Y-m-d H:i:s'),
            'requestId' => 'REQ-0002',
        ];

        $this->postJson('/api/ws/user-info', $payload)
            ->assertStatus(401)
            ->assertJsonPath('status', 'E')
            ->assertJsonPath('requestId', 'REQ-0002');

        $this->withHeaders(['X-Service-Key' => 'wrong-key'])
            ->postJson('/api/ws/user-info', $payload)
            ->assertStatus(401)
            ->assertJsonPath('status', 'E');
    }

    public function test_credentials_are_never_exposed_by_the_service(): void
    {
        $user = $this->student();

        $body = $this->withHeaders($this->headers())->postJson('/api/ws/user-info', [
            'userId' => (string) $user->user_id,
            'queryFlag' => 3,
            'timeStamp' => now()->format('Y-m-d H:i:s'),
        ])->json();

        foreach (['password', 'remember_token', 'token'] as $secret) {
            $this->assertStringNotContainsString($secret, json_encode($body));
        }
    }

    // ------------------------------------------------------------- consumption

    public function test_client_reads_a_successful_provider_response(): void
    {
        Http::fake(['*' => Http::response([
            'status' => ServiceResponse::SUCCESS,
            'totalInternships' => 25,
            'publishedInternships' => 18,
            'draftInternships' => 4,
            'closedInternships' => 3,
            'totalVacancies' => 61,
            'timeStamp' => now()->format('Y-m-d H:i:s'),
        ], 200)]);

        $stats = app(InternshipStatsClient::class)->fetch();

        $this->assertSame(25, $stats['totalInternships']);
        $this->assertSame(61, $stats['totalVacancies']);

        // The outgoing call must itself satisfy the IFA it is calling.
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Service-Key', self::KEY)
                && ! empty($request['requestId'])
                && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $request['timeStamp']) === 1;
        });
    }

    public function test_client_returns_null_when_the_provider_reports_failure(): void
    {
        Http::fake(['*' => Http::response(['status' => 'F', 'message' => 'No data'], 200)]);

        $this->assertNull(app(InternshipStatsClient::class)->fetch());
    }

    public function test_client_returns_null_when_the_provider_is_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('offline'));

        $this->assertNull(app(InternshipStatsClient::class)->fetch());
    }

    public function test_dashboard_uses_the_service_when_it_answers(): void
    {
        Http::fake(['*' => Http::response([
            'status' => ServiceResponse::SUCCESS,
            'totalInternships' => 25,
            'publishedInternships' => 18,
            'draftInternships' => 4,
            'closedInternships' => 3,
            'totalVacancies' => 61,
            'timeStamp' => now()->format('Y-m-d H:i:s'),
        ], 200)]);

        $admin = User::create([
            'full_name' => 'System Administrator',
            'email' => 'admin@internhub.test',
            'username' => 'admin',
            'password' => 'Str0ng!Passw0rd#1',
            'role' => 'Admin',
            'status' => 'Active',
        ]);
        $admin->admin()->create([]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonPath('data.total_internships', 25)
            ->assertJsonPath('data.total_vacancies', 61);
    }

    public function test_dashboard_falls_back_when_the_service_is_down(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('offline'));

        $admin = User::create([
            'full_name' => 'System Administrator',
            'email' => 'admin2@internhub.test',
            'username' => 'admin2',
            'password' => 'Str0ng!Passw0rd#1',
            'role' => 'Admin',
            'status' => 'Active',
        ]);
        $admin->admin()->create([]);

        // No internships seeded, so the local fallback legitimately reports zero.
        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonPath('data.total_internships', 0);
    }
}
