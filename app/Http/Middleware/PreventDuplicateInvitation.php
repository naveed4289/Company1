<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyInvitation;

class PreventDuplicateInvitation
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company;
        $email = $request->email;

        // Check if user is already an employee
        $isEmployee = $company->employees()->where('email', $email)->exists();
        if ($isEmployee) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already an employee'
            ], 400);
        }

        // Check for existing pending invitations
        $existingInvitation = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invitation already sent'
            ], 400);
        }

        return $next($request);
    }
}
