<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DeleteUnverifiedUsers extends Command
{
    // Command ka signature
    protected $signature = 'users:delete-unverified';
    protected $description = 'Delete users who have not verified their email within 24 hours';

    public function handle()
    {
        $users = User::whereNull('email_verified_at')->get();

        $count = $users->count();

        foreach ($users as $user) {
            \Log::info("Deleting user: {$user->email} (ID: {$user->id})");
            $user->delete(); // or $user->forceDelete() if using soft deletes
        }

        $this->info("Deleted {$count} unverified users");
        return 0;
    }
}
