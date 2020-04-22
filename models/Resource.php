<?php

namespace IPriceGroup\OcApiPlugin\Models;

use IPriceGroup\OcApiPlugin\Classes\ResourceObserver;
use Model;
use RainLab\Builder\Classes\ComponentHelper;

/**
 * Model
 */
class Resource extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $dates = [];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'ipricegroup_ocapiplugin_resources';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $jsonable = [
        'eager_load'
    ];

    protected $fillable = [
        'router_method',
        'base_endpoint',
        'controller_method',
        'model_class',
        'eager_load',
        'is_auth_required',
    ];

    public function getModelClassOptions()
    {
        $globalModels = ComponentHelper::instance()->listGlobalModels();

        unset($globalModels[self::class]);

        return $globalModels;
    }

    protected static function boot()
    {
        self::observe(ResourceObserver::class);

        parent::boot();
    }
}
