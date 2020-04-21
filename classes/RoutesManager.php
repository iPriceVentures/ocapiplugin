<?php

namespace IPriceGroup\OcApiPlugin\Classes;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use IPriceGroup\OcApiPlugin\Models\Resource;
use Tymon\JWTAuth\Http\Middleware\Authenticate;

class RoutesManager
{
    private const ROUTE_PATH = __DIR__ . '/../routes.php';
    private const ROUTE_TEMPLATE_PATH = __DIR__ . '/../templates/route.tpl';
    private const ROUTE_API_RESOURCE_TEMPLATE_PATH = __DIR__ . '/../templates/route_api_resource.tpl';
    private const ROUTE_TEMPLATE_PLACEHOLDERS = [
        '%router_method%',
        '%base_endpoint%',
        '%controller_class%',
        '%options%',
        '%middleware_string%',
    ];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $routeTemplate;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->routeTemplate = $this->filesystem->get(self::ROUTE_TEMPLATE_PATH);
        $this->routeApiResourceTemplate = $this->filesystem->get(self::ROUTE_API_RESOURCE_TEMPLATE_PATH);
    }

    public function syncRoutes(Collection $resources): bool
    {
        $routesString = "<?php\n\n";

        foreach ($resources as $resource) {
            $routesString .= $this->compileRoute($resource);
        }

        return $this->filesystem->put(self::ROUTE_PATH, $routesString);
    }

    private function compileRoute(Resource $resource): string
    {
        $replacements = [
            $resource->router_method,
            $resource->base_endpoint,
            ApiControllersManager::getFullyQualifiedControllerClass($resource),
            $this->getOptions($resource),
            $this->getMiddleWareString($resource),
        ];

        return str_replace(self::ROUTE_TEMPLATE_PLACEHOLDERS, $replacements, $this->getRouteTemplate($resource));
    }

    private function getRouteTemplate(Resource $resource): string
    {
        $variable = sprintf('route%sTemplate', ucwords($resource->router_method));
        return $this->$variable ?: $this->routeTemplate;
    }

    private function getOptions(Resource $resource): string
    {
        if ($resource->is_auth_required) {
            return "'middleware' => '" . Authenticate::class . "'";
        }

        return '';
    }

    private function getMiddleWareString(Resource $resource): string
    {
        if ($resource->is_auth_required) {
            return sprintf("->middleware('%s')", Authenticate::class);
        }

        return '';
    }
}
