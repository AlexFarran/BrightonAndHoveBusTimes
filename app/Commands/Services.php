<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\BusInfo\BusInfoAdapter;

class Services extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'services';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Lists all the bus services';

    private $busInfo;

    public function __construct(BusInfoAdapter $busInfo)
    {
        $this->busInfo = $busInfo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('List of services');
        foreach($this->busInfo->services() as $service) {
            $this->info($service);
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
