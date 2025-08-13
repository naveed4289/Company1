<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\User;
use App\Http\Requests\RemoveEmployeeRequest;
use App\Http\Requests\AcceptInvitationRequest;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyInvitationController extends Controller
{
    protected InvitationService $invitationService;
    
    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }
    
    public function acceptInvitation(AcceptInvitationRequest $request)
    {
        $invitation = $request->invitation_model; // Coming from middleware
        
        $response = $this->invitationService->acceptInvitation($invitation);
        
        return response()->json($response);
    }

    public function removeEmployee(RemoveEmployeeRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;
        $employee = $request->employee_model; // Coming from middleware

        $message = $this->invitationService->removeEmployee($employee, $company);

        return response()->json(['message' => $message]);
    }

    public function getCompanyData()
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;

        // Get employees with their details
        $employees = $company->employees()->select('users.id', 'first_name', 'last_name', 'email', 'is_auto_created', 'users.created_at')->get();

        // Get pending invitations
        $pendingInvitations = $company->invitations()
            ->whereNull('accepted_at')
            ->select('id', 'email', 'created_at')
            ->get();

        return response()->json([
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
