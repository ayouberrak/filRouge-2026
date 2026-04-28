<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\Brief\Domain\Events\BriefAssignedToClassrooms;
use App\Modules\Brief\Application\Listeners\SendBriefAssignedNotification;
use App\Modules\Activity\Domain\Events\ActivityAssignedToStudents;
use App\Modules\Activity\Application\Listeners\SendActivityAssignedNotification;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return env('FRONTEND_URL', 'http://localhost') . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        });

        // Brief Notifications
        Event::listen(
            BriefAssignedToClassrooms::class,
            SendBriefAssignedNotification::class
        );

        // Activity Notifications
        Event::listen(
            ActivityAssignedToStudents::class,
            SendActivityAssignedNotification::class
        );

    }
}
