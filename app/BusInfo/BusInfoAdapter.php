<?php

namespace App\BusInfo;

class BusInfoAdapter
{
    public function services()
    {
        return ['1','2','3'];
    }

    public function stops($serviceId)
    {
    }

    public function times($stopId, $serviceId)
    {
    }
}
?>