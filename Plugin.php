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
            'name' => 'ipricegroup.ocapiplugin::lang.plugin.name',
            'description' => 'ipricegroup.ocapiplugin::lang.plugin.description',
            'author' => 'iPrice Group',
            'icon' => 'icon-cloud'
        ];
    }
}
