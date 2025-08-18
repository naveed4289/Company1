<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\User;
use App\Http\Requests\SendInvitationRequest;
use App\Http\Requests\RemoveEmployeeRequest;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Resources\CompanyInvitationResponse;
use App\Mail\CompanyInvitationMail;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyInvitationController extends Controller
{
    /**
     * Send a company invitation to a user.
     * 
     * Steps:
     * 1. Get the authenticated user's company.
     * 2. Find existing user by email or create a new auto-created user.
     * 3. Create a new invitation for the company.
     * 4. Send invitation email.
     * 5. Return a short JSON response with user type.
     */
    public function sendCompanyInvitation(SendInvitationRequest $request)
    {
        $company = Auth::user()->company;

        // Find or create user based on email
        $userData = User::findOrCreateByEmail($request->email);

        // Create invitation for the company
        $invitation = CompanyInvitation::createForCompany(
            $company, 
            $userData['user'], 
            $userData['password']
        );

        // Send invitation email
        Mail::to($request->email)->send(new CompanyInvitationMail($invitation));

        // Return short API response
        return CompanyInvitationResponse::invitationSent($userData['type']);
    }

    /**
     * Accept a company invitation.
     * 
     * Steps:
     * 1. Get invitation, user, and company from the request.
     * 2. Attach user to company's employees.
     * 3. Mark invitation as accepted.
     * 4. Generate login details if user was auto-created.
     * 5. Return short JSON response.
     */
    public function acceptCompanyInvitation(AcceptInvitationRequest $request)
    {
        $invitation = $request->invitation_model;
        $user = $invitation->user;
        $company = $invitation->company;

        // Attach user to company's employees
        $company->employees()->attach($user->id);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Generate login details (if auto-created)
        $loginDetails = CompanyInvitation::generateLoginDetails($invitation);

        // Return short API response
        return CompanyInvitationResponse::invitationAccepted($company->name, $loginDetails);
    }

    /**
     * Remove an employee from the company.
     * 
     * Steps:
     * 1. Get the employee model from request.
     * 2. Get the authenticated user's company.
     * 3. Remove employee and delete account if auto-created.
     * 4. Return a short JSON response.
     */
    public function removeCompanyEmployee(RemoveEmployeeRequest $request)
    {
        $employee = $request->employee_model;
        $company = Auth::user()->company;

        // Call static method on User model to handle removal
        $message = User::removeFromCompany($employee, $company);

        // Return short API response
        return CompanyInvitationResponse::employeeRemoved($message);
    }

    /**
     * Fetch company data including employees and pending invitations.
     * 
     * Steps:
     * 1. Get the authenticated user and their company.
     * 2. Fetch employees using Company model static helper.
     * 3. Fetch pending invitations using Company model static helper.
     * 4. Return company data in a short JSON response.
     */
    public function getCompanyData()
    {
        $user = Auth::user();
        $company = $user->company;

        // Fetch employees of the company
        $employees = Company::fetchEmployees($company);

        // Fetch pending invitations of the company
        $pendingInvitations = Company::fetchPendingInvitations($company);

        // Return short API response with all company data
        return CompanyInvitationResponse::companyData(
            $company, 
            $user, 
            $employees, 
            $pendingInvitations
        );
    }
}
