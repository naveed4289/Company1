<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\User;
use App\Http\Requests\SendInvitationRequest;
use App\Http\Requests\RemoveEmployeeRequest;
use App\Http\Requests\AcceptInvitationRequest;
use App\Mail\CompanyInvitationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyInvitationController extends Controller
{
    public function sendCompanyInvitation(SendInvitationRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;

        $existingUser = User::where('email', $request->email)->first();
        $generatedPassword = null;
        $createdUser = null;
        $userType = 'existing';

        if (!$existingUser) {
            $generatedPassword = Str::random(12);
            $createdUser = User::create([
                'first_name' => 'New',
                'last_name' => 'Employee',
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
                'is_auto_created' => true,
                'email_verified_at' => now(),
            ]);
            $userType = 'new';
        }

        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => $request->email,
            'token' => Str::random(40),
            'generated_password' => $generatedPassword,
            'user_id' => $createdUser ? $createdUser->id : $existingUser->id,
        ]);

        Mail::to($request->email)->send(new CompanyInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation sent successfully',
            'user_type' => $userType,
        ]);
    }

    public function acceptCompanyInvitation(AcceptInvitationRequest $request)
    {
        $invitation = $request->invitation_model;
        $user = $invitation->user;
        $company = $invitation->company;

        $company->employees()->attach($user->id);
        $invitation->update(['accepted_at' => now()]);

        $response = [
            'message' => 'Invitation accepted successfully. You are now an employee of ' . $company->name,
            'company_name' => $company->name,
        ];

        if ($invitation->generated_password) {
            $response['login_details'] = [
                'email' => $user->email,
                'password' => $invitation->generated_password,
                'note' => 'Please change your password after logging in'
            ];
        }

        return response()->json($response);
    }

    public function removeCompanyEmployee(RemoveEmployeeRequest $request)
    {
        $employee = $request->employee_model;
        $company = Auth::user()->company;

        $company->employees()->detach($employee->id);

        $message = 'Employee removed from company successfully';
        if ($employee->is_auto_created) {
            $employee->delete();
            $message = 'Employee removed and account deleted successfully';
        }

        return response()->json(['message' => $message]);
    }

    public function getCompanyData()
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;

        $employees = $company->employees()
            ->select('users.id', 'first_name', 'last_name', 'email', 'is_auto_created', 'users.created_at')
            ->get();

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