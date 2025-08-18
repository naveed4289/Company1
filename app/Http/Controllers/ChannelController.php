<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChannelResponse;
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
     * Fetch all channels accessible to the authenticated user.
     * Includes public channels and private channels the user is a member of.
     */
    public function getChannels(Request $request)
    {
        $user = Auth::user();
        $company = $request->resolved_company;
        $channels = Channel::fetchAllForUser($user, $company);

        return ChannelResponse::channelsFetched($channels);
    }

    /**
     * Fetch public channels for the user's company.
     */
    public function getPublicChannels(Request $request)
    {
        $company = $request->resolved_company;
        $channels = Channel::fetchPublicForCompany($company);

        return ChannelResponse::channelsFetched($channels);
    }

    /**
     * Fetch private channels for the authenticated user.
     */
    public function getPrivateChannels(Request $request)
    {
        $user = Auth::user();
        $company = $request->resolved_company;
        $channels = Channel::fetchPrivateForUser($user, $company);

        return ChannelResponse::channelsFetched($channels);
    }

    /**
     * Create a new channel for the company.
     * Handles private channel membership automatically.
     */
    public function createChannels(CreateChannelRequest $request)
    {
        $user = Auth::user();
        $company = $request->resolved_company;
        $channel = Channel::createForCompany($user, $company, $request->name, $request->type);

        return ChannelResponse::channelCreated($channel);
    }

    /**
     * Update an existing channel.
     * Only accessible by the channel creator (middleware protected).
     */
    public function updateChannels(UpdateChannelRequest $request, $id)
    {
        $channel = $request->attributes->get('channel');
        $channel->update($request->only(['name', 'type']));
        $channel->load(['company', 'creator']);

        return ChannelResponse::channelUpdated($channel);
    }

    /**
     * Delete a channel.
     * Only accessible by the channel creator (middleware protected).
     */
    public function removeChannels($id)
    {
        $channel = request()->attributes->get('channel');
        $channelName = $channel->name;
        $channel->delete();

        return ChannelResponse::channelDeleted($channelName);
    }

    /**
     * Add a member to a private channel.
     */
    public function addMember(\App\Http\Requests\AddChannelMemberRequest $request)
    {
        $channel = $request->attributes->get('channel');
        $memberUser = $request->member_user;
        $channel->addMember($memberUser);

        return ChannelResponse::memberAdded($memberUser);
    }

    /**
     * Remove a member from a private channel.
     */
    public function removeMember(\App\Http\Requests\RemoveChannelMemberRequest $request)
    {
        $channel = $request->attributes->get('channel');
        $memberUser = $request->member_user;
        $channel->removeMember($memberUser);

        return ChannelResponse::memberRemoved($memberUser);
    }
}
