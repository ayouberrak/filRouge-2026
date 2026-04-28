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

use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Infrastructure\Repositories\LivrableRepository;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Infrastructure\Repositories\ActivityRepository;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Infrastructure\Repositories\AbsenceRepository;

use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Infrastructure\Repositories\QuizRepository;

use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;
use App\Modules\Chat\Infrastructure\Repositories\ChatRepository;


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
        $this->app->bind(LivrableRepositoryInterface::class, LivrableRepository::class);
        $this->app->bind(AbsenceRepositoryInterface::class, AbsenceRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, ActivityRepository::class);
        $this->app->bind(QuizRepositoryInterface::class, QuizRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
