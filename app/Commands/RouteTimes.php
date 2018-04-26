<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\BusInfo\BusInfoAdapter;

class RouteTimes extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'route-times';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Lists a service\'s departure times at all the stops on its route.';

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
        $route = $this->chooseRoute();
        $stops = $this->busInfo->stops($route['RouteId']);

        $this->info('Loading departure times');
        $progress = $this->output->createProgressBar(count($stops));

        $busTimes = array_map(function($stop) use ($route, $progress) {
            $progress->advance();
            return ['stopName' => $stop['stopName'], 'times' => implode(', ', $this->busInfo->times($stop['stopId'], $route['ServiceName']))];
        },
        $stops);

        $progress->finish();
        echo "\n\n";

        $this->info('Departure times on the ' . $route['RouteName'] . ' route');
        $headers = ['Stop', 'Times'];
        $this->table($headers, $busTimes);
    }

    /**
     * @return array {
     *     @var int    $RouteId
     *     @var string $ServiceName
     *     @var string $RouteName
     * }
     */
    private function chooseRoute()
    {
        $routes = $this->busInfo->routes();
        $options =  array_column($routes, 'RouteName', 'RouteId');
        $options = array_slice($options, 0, 20, true); // Limit the menu size so it fits on the screen
        $routeId = $this->menu('Choose a route', $options)->open();
        if ($routeId == NULL) die;
        return $routes[$routeId];
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
