<?php

namespace IPriceGroup\OcApiPlugin\Classes;

use Illuminate\Database\Eloquent\Collection;
use IPriceGroup\OcApiPlugin\Models\Resource;

class ApiGenerator
{
    /**
     * @var RoutesManager
     */
    private $routesManager;

    /**
     * @var ApiControllersManager
     */
    private $controllersManager;

    public function __construct(RoutesManager $routesManager, ApiControllersManager $controllersManager)
    {
        $this->routesManager = $routesManager;
        $this->controllersManager = $controllersManager;
    }

    public function syncApis(Collection $resources)
    {
        $this->syncRoutes($resources);
        $this->controllersManager->syncControllers($resources);
    }

    public function syncRoutes(Collection $resources)
    {
        $this->routesManager->syncRoutes($resources);
    }

    public function syncApiController(Resource $resource)
    {
        $this->controllersManager->createController($resource);
    }
}
