<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Company;
use App\Http\Requests\CreateChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use Illuminate\Http\Request;

class ChannelController extends Controller
{

    /**
     * Store a newly created channel in storage.
     */
    public function store(CreateChannelRequest $request)
    {
        try {
            $user = auth()->user();
            
            // Verify user has access to the company
            $company = Company::find($request->company_id);
            
            // Check if user is company owner or employee
            $hasAccess = false;
            if ($company->user_id === $user->id) {
                $hasAccess = true; // Company owner
            } elseif ($company->employees()->where('user_id', $user->id)->exists()) {
                $hasAccess = true; // Company employee
            }

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'You do not have permission to create channels for this company'
                ], 403);
            }

            $channel = Channel::create([
                'name' => $request->name,
                'type' => $request->type,
                'company_id' => $request->company_id,
                'created_by' => $user->id
            ]);

            $channel->load(['company', 'creator']);

            return response()->json([
                'message' => 'Channel created successfully',
                'data' => $channel
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the channel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified channel in storage.
     * This method is protected by ChannelOwnerMiddleware
     */
    public function update(UpdateChannelRequest $request, $id)
    {
        try {
            // Channel is already loaded by middleware
            $channel = $request->attributes->get('channel');

            $channel->update($request->only(['name', 'type']));
            $channel->load(['company', 'creator']);

            return response()->json([
                'message' => 'Channel updated successfully',
                'data' => $channel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the channel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified channel from storage.
     * This method is protected by ChannelOwnerMiddleware
     */
    public function destroy($id)
    {
        try {
            // Channel is already loaded by middleware
            $channel = request()->attributes->get('channel');

            $channelName = $channel->name;
            $channel->delete();

            return response()->json([
                'message' => "Channel '{$channelName}' deleted successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the channel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
