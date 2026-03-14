<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Infrastructure\Repositories\UserRepository;

use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;
use App\Modules\Classroom\Infrastructure\Repositories\ClassroomRepository;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;
use App\Modules\Squad\Infrastructure\Repositories\SquadRepository;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Infrastructure\Repositories\BriefRepository;

use App\Modules\Deliverable\Domain\Repositories\DeliverableRepositoryInterface;
use App\Modules\Deliverable\Infrastructure\Repositories\DeliverableRepository;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Infrastructure\Repositories\ActivityRepository;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Infrastructure\Repositories\AbsenceRepository;

use App\Modules\Report\Domain\Repositories\DailyReportRepositoryInterface;
use App\Modules\Report\Infrastructure\Repositories\DailyReportRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClassroomRepositoryInterface::class, ClassroomRepository::class);
        $this->app->bind(SquadRepositoryInterface::class, SquadRepository::class);
        $this->app->bind(BriefRepositoryInterface::class, BriefRepository::class);
        $this->app->bind(DeliverableRepositoryInterface::class, DeliverableRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, ActivityRepository::class);
        $this->app->bind(AbsenceRepositoryInterface::class, AbsenceRepository::class);
        $this->app->bind(DailyReportRepositoryInterface::class, DailyReportRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
