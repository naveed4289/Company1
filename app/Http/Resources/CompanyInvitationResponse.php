<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInvitationResponse extends JsonResource
{
    protected $message;
    protected $extra;

    public function __construct($message, $extra = [])
    {
        parent::__construct(null);
        $this->message = $message;
        $this->extra = $extra;
    }

    public function toArray($request)
    {
        return array_merge([
            'status' => 'success',
            'message' => $this->message,
        ], $this->extra);
    }

    // ✅ Short methods
    public static function invitationSent($userType)
    {
        return new self('Invitation sent successfully', [
            'user_type' => $userType,
        ]);
    }

    public static function invitationAccepted($companyName, $loginDetails = null)
    {
        return new self(
            "Invitation accepted successfully. You are now an employee of {$companyName}",
            [
                'company_name' => $companyName,
                'login_details' => $loginDetails,
            ]
        );
    }

    public static function employeeRemoved($message)
    {
        return new self($message);
    }

    public static function companyData($company, $user, $employees, $pendingInvitations)
    {
        return new self('Company data fetched successfully', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                ],
                'employee_count' => $employees->count(),
                'employees' => $employees,
                'pending_invitations_count' => $pendingInvitations->count(),
                'pending_invitations' => $pendingInvitations,
            ]
        ]);
    }
}
