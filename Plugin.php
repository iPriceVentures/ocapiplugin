<?php namespace IPriceGroup\OcApiPlugin;

use IPriceGroup\OcApiPlugin\Classes\ResourceObserver;
use IPriceGroup\OcApiPlugin\Models\Resource;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['RLuders.JWTAuth'];

    public function pluginDetails()
    {
        return [
            'name' => 'iprice.api::lang.plugin.name',
            'description' => 'iprice.api::lang.plugin.description',
            'author' => 'iPrice',
            'icon' => 'icon-cloud'
        ];
    }

    public function boot()
    {
        Resource::observe(ResourceObserver::class);
    }
}
