<?php

namespace App\Http\Controllers\Api\WebService;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebService\GetUserInfoRequest;
use App\Models\User;
use App\Support\WebService\ServiceResponse;
use Illuminate\Http\JsonResponse;


class UserInfoServiceController extends Controller
{
    public function getUserInfo(GetUserInfoRequest $request): JsonResponse
    {
        $data = $request->validated();
        $requestId = $data['requestId'] ?? null;

        $user = User::with(['student', 'employer.company'])->find($data['userId']);

        if (! $user) {
            // F, not E: the request was perfectly well-formed, it just found nothing.
            return ServiceResponse::fail('No user exists with that userId.', $requestId);
        }

        $flag = (int) $data['queryFlag'];
        $details = [];

        if ($flag === 1 || $flag === 3) {
            $details += $this->contactDetails($user);
        }

        if ($flag === 2 || $flag === 3) {
            $details += $this->profileDetails($user);
        }

        return ServiceResponse::success([
            'userName' => $user->full_name,
            'userEmail' => $user->email,
            'userRole' => $user->role,
            'userStatus' => $user->status,
            'userDetails' => (object) $details,
        ], $requestId);
    }

    /** @return array<string, string|null> */
    private function contactDetails(User $user): array
    {
        return ['hpNo' => $user->phone];
    }

    /**
     * Role-specific fields. Nulls are stripped so a consumer
     * @return array<string, string>
     */
    private function profileDetails(User $user): array
    {
        $details = match ($user->role) {
            'Student' => [
                'matricNo' => $user->student?->matric_no,
                'university' => $user->student?->university,
                'programme' => $user->student?->programme,
            ],
            'Employer' => [
                'position' => $user->employer?->position,
                'department' => $user->employer?->department,
                'companyName' => $user->employer?->company?->company_name,
            ],
            default => [],
        };

        return array_filter($details, fn ($value) => $value !== null && $value !== '');
    }
}
