<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class ValidateCompanyAccess
{
	public function handle(Request $request, Closure $next)
	{
		$user = Auth::user();
		$companyId = $request->company_id;

		$company = Company::find($companyId);
		if (!$company) {
			return response()->json(['message' => 'Company not found'], 404);
		}

		$hasAccess = $company->user_id === $user->id
			|| $company->employees()->where('user_id', $user->id)->exists();

		if (!$hasAccess) {
			return response()->json([
				'message' => 'You do not have permission to create channels for this company'
			], 403);
		}

		$request->merge(['resolved_company' => $company]);

		return $next($request);
	}
}


