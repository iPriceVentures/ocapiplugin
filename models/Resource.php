<?php

namespace IPriceGroup\OcApiPlugin\Models;

use Model;
use RainLab\Builder\Classes\ComponentHelper;

/**
 * Model
 */
class Resource extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'iprice_api_resources';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $jsonable = [
        'eager_load'
    ];

    public function getModelClassOptions()
    {
        $globalModels = ComponentHelper::instance()->listGlobalModels();

        unset($globalModels[self::class]);

        return $globalModels;
    }
}
