<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CompanyInvitation;

class ValidateInvitationToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');
        
        $invitation = CompanyInvitation::where('token', $token)
            ->with(['company', 'user'])
            ->first();

        if (!$invitation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid invitation token'
            ], 404);
        }

        if ($invitation->accepted_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invitation already accepted'
            ], 400);
        }

        $user = $invitation->user;
        $company = $invitation->company;

        if (!$user || !$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid invitation data'
            ], 400);
        }

        // Check if user is already an employee
        if ($company->employees()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already an employee'
            ], 400);
        }

        // Add invitation to request for use in controller
        $request->merge(['invitation_model' => $invitation]);

        return $next($request);
    }
}
