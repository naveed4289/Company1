<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Company;
use App\Models\User;
use App\Http\Requests\CreateChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * Get all channels for user's company
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get user's company (either owned or employed)
            $company = $user->company; // Company owned by user
            if (!$company) {
                // Check if user is an employee of any company
                $company = $user->companies()->first();
            }

            if (!$company) {
                return response()->json([
                    'message' => 'You are not associated with any company'
                ], 404);
            }

            // Get all public channels and private channels where user is a member
            $publicChannels = $company->channels()
                ->where('type', 'public')
                ->with(['creator:id,first_name,last_name'])
                ->get();

            $privateChannels = $company->channels()
                ->where('type', 'private')
                ->whereHas('members', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['creator:id,first_name,last_name', 'members:id,first_name,last_name'])
                ->get();

            $channels = $publicChannels->merge($privateChannels);

            return response()->json([
                'data' => $channels
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching channels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

            // If it's a private channel, add the creator as a member
            if ($channel->type === 'private') {
                $channel->addMember($user);
            }

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

    /**
     * Add a member to a private channel
     */
    public function addMember(Request $request, $channelId)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = auth()->user();
            $channel = Channel::findOrFail($channelId);

            // Only channel creator or company owner can add members
            if (!$channel->canBeManaged($user)) {
                return response()->json([
                    'message' => 'You do not have permission to manage this channel'
                ], 403);
            }

            // Check if channel is private
            if ($channel->type !== 'private') {
                return response()->json([
                    'message' => 'Can only add members to private channels'
                ], 400);
            }

            $memberUser = User::findOrFail($request->user_id);

            // Check if user belongs to the same company
            if (!$channel->isUserInSameCompany($memberUser)) {
                return response()->json([
                    'message' => 'User must be part of the same company'
                ], 400);
            }

            $channel->addMember($memberUser);

            return response()->json([
                'message' => 'Member added to channel successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding member to channel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a member from a private channel
     */
    public function removeMember(Request $request, $channelId)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = auth()->user();
            $channel = Channel::findOrFail($channelId);

            // Only channel creator or company owner can remove members
            if (!$channel->canBeManaged($user)) {
                return response()->json([
                    'message' => 'You do not have permission to manage this channel'
                ], 403);
            }

            // Check if channel is private
            if ($channel->type !== 'private') {
                return response()->json([
                    'message' => 'Can only remove members from private channels'
                ], 400);
            }

            $memberUser = User::findOrFail($request->user_id);
            $channel->removeMember($memberUser);

            return response()->json([
                'message' => 'Member removed from channel successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error removing member from channel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
