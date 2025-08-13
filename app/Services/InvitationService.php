<?php

namespace App\Services;

use App\Models\CompanyInvitation;
use App\Models\User;

class InvitationService
{
    public function acceptInvitation(CompanyInvitation $invitation): array
    {
        $user = $invitation->user;
        $company = $invitation->company;

        // Add user as employee
        $company->employees()->attach($user->id);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Prepare response
        $response = [
            'message' => 'Invitation accepted successfully. You are now an employee of ' . $company->name,
            'company_name' => $company->name,
        ];

        // Add login details if password was generated
        if ($invitation->generated_password) {
            $response['login_details'] = [
                'email' => $user->email,
                'password' => $invitation->generated_password,
                'note' => 'Please change your password after logging in'
            ];
        }

        return $response;
    }

    public function removeEmployee(User $employee, $company): string
    {
        // Remove from company
        $company->employees()->detach($employee->id);

        // Handle auto-created users
        if ($employee->is_auto_created) {
            $employee->delete();
            return 'Employee removed and account deleted successfully';
        }
        
        return 'Employee removed from company successfully';
    }
}
