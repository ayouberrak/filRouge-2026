<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\Brief\Domain\Events\BriefAssignedToClassrooms;
use App\Modules\Brief\Application\Listeners\SendBriefAssignedNotification;
use App\Modules\Activity\Domain\Events\ActivityAssignedToStudents;
use App\Modules\Activity\Application\Listeners\SendActivityAssignedNotification;
use App\Modules\Report\Domain\Events\DailyReportSubmitted;
use App\Modules\Report\Application\Listeners\SendDailyReportNotification;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
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

        // Daily Report Notifications
        Event::listen(
            DailyReportSubmitted::class,
            SendDailyReportNotification::class
        );
    }
}
