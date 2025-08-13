<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class EnsureUserAssociatedWithCompany
{
	public function handle(Request $request, Closure $next)
	{
		$user = Auth::user();

		// Resolve company either by ownership or employee membership
		$company = $user->company;
		if (!$company) {
			$company = Company::whereHas('employees', function ($q) use ($user) {
				$q->where('users.id', $user->id);
			})->first();
		}

		if (!$company) {
			return response()->json([
				'message' => 'You are not associated with any company'
			], 404);
		}

		$request->merge(['resolved_company' => $company]);

		return $next($request);
	}
}


