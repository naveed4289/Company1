<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ValidateEmployeeExists
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $user->company;
        $employeeId = $request->employee_id;

        $employee = User::find($employeeId);
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found'
            ], 404);
        }

        // Check if user is actually an employee of this company
        if (!$company->employees()->where('user_id', $employee->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an employee of this company'
            ], 400);
        }

        // Add employee to request for use in controller
        $request->merge(['employee_model' => $employee]);

        return $next($request);
    }
}
