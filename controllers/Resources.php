<?php

namespace IPriceGroup\OcApiPlugin\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use IPriceGroup\OcApiPlugin\Classes\ApiGenerator;

class Resources extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['ipricegroup.ocapiplugin.manage_apis'];

    /**
     * @var ApiGenerator
     */
    private $apiGenerator;

    public function __construct(ApiGenerator $apiGenerator)
    {
        parent::__construct();
        BackendMenu::setContext('iPriceGroup.OcApiPlugin', 'iprice-api-main', 'iprice-api-resources');

        $this->apiGenerator = $apiGenerator;
    }
}
