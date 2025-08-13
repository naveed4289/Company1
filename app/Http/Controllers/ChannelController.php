<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Company;
use App\Models\User;
use App\Http\Requests\CreateChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    /**
     * Get all channels for user's company
     */
    public function get(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $request->resolved_company; // ensure employees also work

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

        return response()->json(['data' => $channels]);
    }

    /**
     * Get public channels for the user's company (visible to all company users)
     */
    public function getPublic(Request $request)
    {
        $company = $request->resolved_company; // from middleware company.associated

        $channels = $company->channels()
            ->where('type', 'public')
            ->with(['creator:id,first_name,last_name'])
            ->get();

        return response()->json(['data' => $channels]);
    }

    /**
     * Get private channels for the user (creator or member only)
     */
    public function getPrivate(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $request->resolved_company; // from middleware company.associated

        $channels = $company->channels()
            ->where('type', 'private')
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['creator:id,first_name,last_name', 'members:id,first_name,last_name'])
            ->get();

        return response()->json(['data' => $channels]);
    }

    /**
     * Store a newly created channel in storage.
     */
    public function create(CreateChannelRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $company = $request->resolved_company; // from middleware

        $channel = Channel::create([
            'name' => $request->name,
            'type' => $request->type,
            'company_id' => $company->id,
            'created_by' => $user->id
        ]);

        if ($channel->type === 'private') {
            $channel->addMember($user);
        }

        $channel->load(['company', 'creator']);

        return response()->json([
            'message' => 'Channel created successfully',
            'data' => $channel
        ], 201);
    }

    /**
     * Update the specified channel in storage.
     * This method is protected by ChannelOwnerMiddleware
     */
    public function update(UpdateChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');

        $channel->update($request->only(['name', 'type']));
        $channel->load(['company', 'creator']);

        return response()->json([
            'message' => 'Channel updated successfully',
            'data' => $channel
        ]);
    }

    /**
     * Remove the specified channel from storage.
     * This method is protected by ChannelOwnerMiddleware
     */
    public function remove($id)
    {
        $channel = request()->attributes->get('channel');
        $channelName = $channel->name;
        $channel->delete();

        return response()->json(['message' => "Channel '{$channelName}' deleted successfully"]);
    }

    /**
     * Add a member to a private channel
     */
    public function addMember(\App\Http\Requests\AddChannelMemberRequest $request)
    {
        $channel = $request->attributes->get('channel');
        $memberUser = $request->member_user; // from middleware

        $channel->addMember($memberUser);

        return response()->json(['message' => 'Member added to channel successfully']);
    }

    /**
     * Remove a member from a private channel
     */
    public function removeMember(\App\Http\Requests\RemoveChannelMemberRequest $request)
    {
        $channel = $request->attributes->get('channel');
        $memberUser = $request->member_user; // from middleware
        $channel->removeMember($memberUser);

        return response()->json(['message' => 'Member removed from channel successfully']);
    }
}
