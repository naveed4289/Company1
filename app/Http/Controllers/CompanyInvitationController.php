<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyInvitationController extends Controller
{
    public function acceptInvitation($token)
    {
        $invitation = CompanyInvitation::where('token', $token)->with(['company', 'user'])->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invalid invitation token'], 404);
        }

        if ($invitation->accepted_at) {
            return response()->json(['message' => 'Invitation already accepted'], 400);
        }

        $user = $invitation->user;
        $company = $invitation->company;

        if (!$user || !$company) {
            return response()->json(['message' => 'Invalid invitation data'], 400);
        }

        // Check if user is already an employee
        if ($company->employees()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User already an employee'], 400);
        }

        // Add user as employee
        $company->employees()->attach($user->id);

        // Mark invitation as accepted
        $invitation->accepted_at = now();
        $invitation->save();

        // Prepare response with login details if password was generated
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

    public function removeEmployee(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:users,id',
            ]);

            $user = auth()->user();
            $company = $user->company;

            if (!$company) {
                return response()->json(['message' => 'You do not have a company'], 403);
            }

            $employee = User::find($validated['employee_id']);

            // Check if user is actually an employee
            if (!$company->employees()->where('user_id', $employee->id)->exists()) {
                return response()->json(['message' => 'User is not an employee of this company'], 400);
            }

            // Remove from company
            $company->employees()->detach($employee->id);

            // If user was auto-created, delete the account
            if ($employee->is_auto_created) {
                $employee->delete();
                $message = 'Employee removed and account deleted successfully';
            } else {
                $message = 'Employee removed from company successfully';
            }

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getCompanyData()
    {
        try {
            $user = auth()->user();
            $company = $user->company;

            if (!$company) {
                return response()->json(['message' => 'You do not have a company'], 403);
            }

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

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
