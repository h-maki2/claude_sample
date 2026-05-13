<?php

namespace Modules\MeetingRoomManagement\Providers;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomFetcher;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Modules\MeetingRoomManagement\Infrastructure\Fetcher\Eloquent\MeetingRoom\EloquentMeetingRoomFetcher;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\EloquentMeetingRoomRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MeetingRoomManagementServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->bind(
            MeetingRoomRepository::class,
            EloquentMeetingRoomRepository::class,
        );
        $this->app->bind(
            MeetingRoomFetcher::class,
            EloquentMeetingRoomFetcher::class,
        );
    }

    /**
     * The name of the module.
     */
    protected string $name = 'MeetingRoomManagement';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'meetingroommanagement';

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
