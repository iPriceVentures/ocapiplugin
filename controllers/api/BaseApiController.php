<?php

namespace IPriceGroup\OcApiPlugin\Controllers\Api;

use Cms\Classes\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Request;
use IPriceGroup\OcApiPlugin\Controllers\Api\Exceptions\ResourceIdNotSpecified;
use October\Rain\Database\Builder;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseApiController extends Controller
{
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PAGE = 1;
    private const ERROR_RESOURCE_NOT_FOUND = 'Resource not found';
    private const ERROR_RESOURCE_ID_NOT_SPECIFIED = 'Resource ID not specified';
    private const FILTER_EXCEPT_PARAMS = ['page', 'limit', 'token'];
    private const DEFAULT_FILTER_OPERATOR = '=';
    private const FILTER_OPERATORS_MAPPING = [
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'eq' => '=',
        'neq' => '<>',
        'contains' => 'like'
    ];

    /** @var string */
    protected $resourceName = '';

    /** @var string */
    protected $modelClass = '';

    /** @var array $eagerLoad */
    protected $eagerLoad = [];

    /** @var array $customFilter */
    protected $customFilter = [];

    /** @var Builder */
    protected $queryBuilder;

    public function __construct($theme = null)
    {
        $this->initializeQueryBuilder();

        parent::__construct($theme);
    }

    public function index()
    {
        $limit = Request::get('limit', self::DEFAULT_LIMIT);
        $page = Request::get('page', self::DEFAULT_PAGE) ?: self::DEFAULT_PAGE;
        $offset = ($page - 1) * $limit;

        $this->queryBuilder
            ->limit($limit)
            ->offset($offset);

        $this->applyFilter();
        $this->applyCustomFilter();
        $this->eagerLoadRelations();

        return response()->json(['data' => $this->queryBuilder->get()]);
    }

    public function show()
    {
        $this->eagerLoadRelations();

        return $this->getResponseToResourceRequest();
    }

    public function store()
    {
        $data = Request::route()->parameters() + Request::all();

        /** @var Model $resource */
        $resource = call_user_func($this->modelClass . '::make', $data);

        $resource->save();

        return response()
            ->json(['data' => $resource])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update()
    {
        return $this->getResponseToResourceRequest(function (Model $resource) {
            $resource
                ->fill(Request::all())
                ->save();
        });
    }

    public function destroy()
    {
        return $this->getResponseToResourceRequest(function (Model $resource) {
            $resource->delete();
        });
    }

    protected function applyCustomFilter() {}

    private function initializeQueryBuilder()
    {
        $this->queryBuilder = call_user_func($this->modelClass . '::query');

        $paramExceptResource = collect(Request::route()->parameters())->except($this->resourceName);

        $this->queryBuilder->where($paramExceptResource->toArray());
    }

    private function getResource(): Model
    {
        $resourceId = Request::route()->parameter($this->resourceName);

        if ($resourceId === null) {
            throw new ResourceIdNotSpecified();
        }

        return $this->queryBuilder->findOrFail($resourceId);
    }

    private function getResponseToResourceRequest(?callable $callback = null)
    {
        try {
            $resource = $this->getResource();

            if ($callback !== null) {
                $callback($resource);
            }

            return response()
                ->json(['data' => $resource]);
        } catch (ResourceIdNotSpecified $e) {
            return response()
                ->json(['error' => self::ERROR_RESOURCE_ID_NOT_SPECIFIED])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json(['error' => self::ERROR_RESOURCE_NOT_FOUND])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }

    private function applyFilter()
    {
        $filters = Request::except(array_merge(self::FILTER_EXCEPT_PARAMS, $this->customFilter));
        foreach ($filters as $filterField => $filterValues) {
            $filterValues = (array) $filterValues;
            array_walk(
                $filterValues,
                function ($value, $operator) use ($filterField) {
                    $operator = self::FILTER_OPERATORS_MAPPING[$operator] ?? self::DEFAULT_FILTER_OPERATOR;
                    $this->queryBuilder->where($filterField, $operator, $value);
                }
            );
        }

    }

    private function eagerLoadRelations()
    {
        if (count($this->eagerLoad)) {
            $this->queryBuilder->with($this->eagerLoad);
        }
    }
}
