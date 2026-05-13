<?php

namespace Modules\ReservationManagement\Providers;

use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Modules\ReservationManagement\Infrastructure\Reservation\EloquentReservationExistenceChecker;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ReservationManagementServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->bind(
            ReservationRepository::class,
            EloquentReservationRepository::class,
        );
        $this->app->bind(
            ReservationExistenceChecker::class,
            EloquentReservationExistenceChecker::class,
        );
    }

    /**
     * The name of the module.
     */
    protected string $name = 'ReservationManagement';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'reservationmanagement';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
