<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class EnsureMemberSameCompany
{
	public function handle(Request $request, Closure $next)
	{
        $channel = $request->attributes->get('channel');
		$memberId = $request->user_id;
		$memberUser = User::find($memberId);

		if (!$memberUser) {
			return response()->json(['message' => 'User not found'], 404);
		}

		if (!$channel->isUserInSameCompany($memberUser)) {
			return response()->json(['message' => 'User must be part of the same company'], 400);
		}

		$request->merge(['member_user' => $memberUser]);
		return $next($request);
	}
}


