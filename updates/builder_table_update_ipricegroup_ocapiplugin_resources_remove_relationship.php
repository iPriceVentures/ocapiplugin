<?php

namespace IPriceGroup\OcApiPlugin\Updates;

use IPriceGroup\OcApiPlugin\Models\Resource;
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateIpricegroupOcapipluginResourcesRemoveRelationship extends Migration
{
    public function up()
    {
        foreach (Resource::all() as $resource) {
            $resource->eager_load = array_reduce($resource->eager_load, function ($carry, $value) {
                $carry[] = is_array($value) ? $value['relationship'] : $value;
                
                return $carry;
            }, []);

            $resource->save();
        }
    }

    public function down()
    {
        foreach (Resource::all() as $resource) {
            $resource->eager_load = array_reduce($resource->eager_load, function ($carry, $value) {
                $carry[] = ['relationship' => $value];
                
                return $carry;
            }, []);

            $resource->save();
        }
    }
}