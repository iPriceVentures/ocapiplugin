<?php

namespace IPriceGroup\OcApiPlugin\Classes;

use IPriceGroup\OcApiPlugin\Models\Resource;

class ResourceObserver
{
    /**
     * @var ApiGenerator
     */
    private $apiGenerator;

    public function __construct(ApiGenerator $apiGenerator)
    {
        $this->apiGenerator = $apiGenerator;
    }

    public function created()
    {
        $this->syncApis();
    }

    public function updated(Resource $resource)
    {
        $this->apiGenerator->syncRoutes(Resource::all());
        $this->apiGenerator->syncApiController($resource);
    }

    public function deleted()
    {
        $this->syncApis();
    }

    private function syncApis()
    {
        $this->apiGenerator->syncApis(Resource::all());
    }
}
