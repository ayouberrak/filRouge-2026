<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Brief Notifications
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\Brief\Domain\Events\BriefAssignedToClassrooms::class,
            \App\Modules\Brief\Application\Listeners\SendBriefAssignedNotification::class
        );

        // Activity Notifications
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\Activity\Domain\Events\ActivityAssignedToStudents::class,
            \App\Modules\Activity\Application\Listeners\SendActivityAssignedNotification::class
        );

        // Daily Report Notifications
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\Report\Domain\Events\DailyReportSubmitted::class,
            \App\Modules\Report\Application\Listeners\SendDailyReportNotification::class
        );
    }
}
