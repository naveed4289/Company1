<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'channel.owner' => \App\Http\Middleware\ChannelOwnerMiddleware::class,
            'auth.token' => \App\Http\Middleware\AuthToken::class, 
            'user.has.company' => \App\Http\Middleware\EnsureUserHasCompany::class,
            'email.verified' => \App\Http\Middleware\EnsureEmailVerified::class,
            'login.email.verification' => \App\Http\Middleware\HandleLoginEmailVerification::class,
            'prevent.duplicate.invitation' => \App\Http\Middleware\PreventDuplicateInvitation::class,
            'validate.auth.token' => \App\Http\Middleware\ValidateAuthToken::class,
            'validate.employee.exists' => \App\Http\Middleware\ValidateEmployeeExists::class,
            'validate.invitation.token' => \App\Http\Middleware\ValidateInvitationToken::class,
            'validate.email.verification' => \App\Http\Middleware\ValidateEmailVerification::class,
            'validate.user.exists' => \App\Http\Middleware\ValidateUserExists::class,
            'validate.password.reset.token' => \App\Http\Middleware\ValidatePasswordResetToken::class,
            'company.associated' => \App\Http\Middleware\EnsureUserAssociatedWithCompany::class,
            'company.access' => \App\Http\Middleware\ValidateCompanyAccess::class,
            'channel.load' => \App\Http\Middleware\LoadChannel::class,
            'channel.manage' => \App\Http\Middleware\EnsureCanManageChannel::class,
            'channel.private' => \App\Http\Middleware\EnsurePrivateChannel::class,
            'channel.member.same_company' => \App\Http\Middleware\EnsureMemberSameCompany::class,
            'channel.access' => \App\Http\Middleware\EnsureCanAccessChannel::class,
            'message.delete' => \App\Http\Middleware\EnsureCanDeleteMessage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
