<?php

namespace App\Modules\Report\Application\Listeners;

use App\Modules\Report\Domain\Events\DailyReportSubmitted;
use App\Modules\Report\Application\Mails\DailyReportSubmittedMail;
use App\Modules\Report\Infrastructure\Models\DailyReportModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Mail;

class SendDailyReportNotification
{
    public function handle(DailyReportSubmitted $event): void
    {
        $report = DailyReportModel::with(['formateur', 'classroom'])->find($event->reportId);
        if (!$report) return;

        // Find admins
        $admins = UserModel::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)->send(new DailyReportSubmittedMail($report));
            }
        }
    }
}
