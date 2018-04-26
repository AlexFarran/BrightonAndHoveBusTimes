<?php

namespace App\BusInfo;

use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

class BusInfoAdapter
{
    private $client;


    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'http://www.buscms.com/']);
    }

    // http://www.buscms.com/api/XmlEntities/v1/services.aspx?clientid=BrightonBuses2016&datatype=json

    /**
     * @return array[] {
     *     Route ID, route name and service name indexed by route ID
     *
     *     @var int    $RouteId
     *     @var string $ServiceName
     *     @var string $RouteName
     * }
     */
    public function routes(): array
    {
        $response = $this->client->request('GET', 'api/XmlEntities/v1/services.aspx?clientid=BrightonBuses2016&datatype=json');
        $services = json_decode($response->getBody(), true)['Services'];

        $serviceToRoutes = function($service) {
            return array_map(function($route) use ($service) {
                $route['ServiceName'] = $service['ServiceName'];
                return $route;
            },
            $service['Routes']);
        };

        $routes = array_reduce($services, function($routes, $service) use ($serviceToRoutes) {
            return array_merge($routes, $serviceToRoutes($service));
        },
        []);

        $indexedRoutes = array_combine(array_column($routes, 'RouteId'), $routes);

        return $indexedRoutes;
    }

    /**
     * @param int $routeId
     *
     * @return array[] {
     *     @var string $stopName
     *     @var int    $stopId 
     * }
     */
    public function stops($routeId)
    {
        $response = $this->client->request('GET', 'BrightonBuses2016/operatorpages/widgets/departureboard/ssi.aspx?method=updateRouteStops&routeid=' . $routeId);
        $jsonp = (string)  $response->getBody();
        $json = substr($jsonp, 3, -2);
        $stops = json_decode($json, true)['stops'];
        
        return $stops;
    }

    /**
     * @param int    $stopId
     * @param string $serviceName
     * 
     * @return string[] Departure times
     */
    public function times($stopId, $serviceName)
    {
        $response = $this->client->request('GET', 'api/REST/html/departureboard.aspx?clientid=BrightonBuses2016&stopid=' . $stopId . '&format=jsonp&servicenamefilter=' . $serviceName . '&cachebust=123&sourcetype=siri&requestor=LD&includeTimestamp=true');
        $html =  stripslashes($response->getBody());
        $dom = HtmlDomParser::str_get_html($html);
        $departures = $dom->find('.rowServiceDeparture');
        $times = array_map(function($departure) {
            return $departure->find('.colDepartureTime', 0)->innertext;
        },
        $departures);

        return $times;
    }
}
?>