<?php

namespace IPriceGroup\OcApiPlugin\Classes;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use IPriceGroup\OcApiPlugin\Models\Resource;

class ApiControllersManager
{
    private const API_CONTROLLER_TPL_PATH = __DIR__ . '/../templates/controller.tpl';
    private const API_CONTROLLERS_DIRECTORY = __DIR__ . '/../controllers/api';
    private const API_CONTROLLER_NAMESPACE = 'IPriceGroup\OcApiPlugin\Controllers\Api';
    private const API_CONTROLLER_TPL_PLACEHOLDERS = [
        '%controller_class%',
        '%resource_name%',
        '%model_class%',
        '%eager_load%',
    ];
    private const API_CONTROLLER_REGEX_PATTERNS = [
        '/(?<=\$resourceName\s=\s\')(\w+)/m',
        '/(?<=\$eagerLoad\s=\s\[)([\w\',\s]*)/m',
    ];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $controllerTpl;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->controllerTpl = $this->filesystem->get(self::API_CONTROLLER_TPL_PATH);
    }

    public function syncControllers(Collection $resources)
    {
        $this->removeDeletedControllers($resources);
        $this->createNewControllers($resources);
    }

    private function createNewControllers(Collection $resources)
    {
        foreach ($this->getResourcesNeedingController($resources) as $resource) {
            $this->createController($resource);
        }
    }

    public function createController(Resource $resource)
    {
        $controllerClass = self::getControllerClass($resource);

        $replacements = [
            'controller' => $controllerClass,
            'resource' => Str::singular(basename($resource->base_endpoint)),
            'model' => $resource->model_class,
            'eagerload' => $this->stringifyEagerLoad($resource),
        ];

        $controllerFullPath = self::API_CONTROLLERS_DIRECTORY . '/' . $controllerClass . '.php';

        try {
            $existingFile = $this->filesystem->get($controllerFullPath);
            $fileContent = preg_replace(
                self::API_CONTROLLER_REGEX_PATTERNS,
                [
                    $replacements['resource'],
                    $replacements['eagerload'],
                ],
                $existingFile
            );
        } catch (\Exception $e) {
            $fileContent = str_replace(self::API_CONTROLLER_TPL_PLACEHOLDERS, $replacements, $this->controllerTpl);
        }

        $this->filesystem->put($controllerFullPath, $fileContent);
    }

    private function removeDeletedControllers(Collection $resources)
    {
        $controllerFilenamesToBeRemoved = array_diff(
            $this->getExistingControllers(),
            $this->getControllerFilenamesFromResources($resources)
        );

        $controllerAbsolutePaths = [];

        foreach ($controllerFilenamesToBeRemoved as $controllerFilename) {
            $controllerAbsolutePaths[] = self::API_CONTROLLERS_DIRECTORY . '/' . $controllerFilename;
        }

        $this->filesystem->delete($controllerAbsolutePaths);
    }

    private function getExistingControllers(): array
    {
        $existingControllers = [];

        foreach ($this->filesystem->files(self::API_CONTROLLERS_DIRECTORY) as $existingController) {
            if ($existingController->getFilename() !== 'BaseApiController.php') {
                $existingControllers[] = $existingController->getFilename();
            }
        }

        return $existingControllers;
    }

    private function getControllerFilenamesFromResources(Collection $resources)
    {
        $controllersFromResources = [];

        foreach ($resources as $resource) {
            $controllersFromResources[] = self::getControllerClass($resource) . '.php';
        }

        return $controllersFromResources;
    }

    private function getResourcesNeedingController(Collection $resources)
    {
        $existingControllers = $this->getExistingControllers();

        $resourcesNeedingController = [];

        foreach ($resources as $resource) {
            if (!in_array(self::getControllerClass($resource), $existingControllers)) {
                $resourcesNeedingController[] = $resource;
            }
        }

        return $resourcesNeedingController;
    }

    private function stringifyEagerLoad(Resource $resource)
    {
        $quotedEagerLoads = [];

        foreach ($resource->eager_load as $eagerLoad) {
            $quotedEagerLoads[] = "'$eagerLoad'";
        }

        return implode(', ', $quotedEagerLoads);
    }

    public static function getControllerClass(Resource $resource)
    {
        return str_replace('\\', '', $resource->model_class) . 'Controller';
    }

    public static function getFullyQualifiedControllerClass(Resource $resource)
    {
        return self::API_CONTROLLER_NAMESPACE . '\\' . self::getControllerClass($resource);
    }
}
