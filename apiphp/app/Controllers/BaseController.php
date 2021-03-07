<?php
declare(strict_types=1);

namespace Meetingg\Controllers;

use Meetingg\Exception\PublicException;
use Meetingg\Http\StatusCodes;
use Phalcon\Mvc\Controller;

use Meetingg\Interfaces\SharedConstInterface;
use Meetingg\Models\BaseModel;

class BaseController extends Controller implements SharedConstInterface
{

    /**
     * Index : Get List of Routes
     *
     * @return array
     */
    public function index() : array
    {
        $matched = $this->router->getMatchedRoute();
        $matched = $matched->getPattern() ?? "/";
        
        return [
            'routes'=> array_values(array_filter(self::getRoutes($this), function ($item) use ($matched) {
                return strpos(strval($item), $matched) !== false;
            }))
        ];
    }

    /**
     * Get List of Routes
     *
     * @param Micro $app
     * @return array|null
     */
    public static function getRoutes(BaseController $controller) :? array
    {
        $routes = array_map(function ($item) {
            $route = $item->getPattern();
            $routeParts = explode('/', $route) ?? [];
            foreach ($routeParts as $i => $v) {
                if (strpos(strval($v), ':') !== false && strpos(strval($v), '{') !== false) {
                    $partArray = explode('{', explode(':', $v)[0])[1];
                    $routeParts[$i] = ":$partArray";
                }
            }

            return  join('/', $routeParts) ?? $route;
        }, $controller ->router->getRoutes());
        sort($routes);

        return $routes;
    }

    /**
     * Filter Data & return allowed fields data
     *
     * @param array $dataArray
     * @param array $excludes
     * @param array $includes
     * @return array|null
     */
    public static function filterData(array $dataArray, array $excludes = [], array $includes = []) :? array
    {
        if (count($includes)) {
            return array_filter($dataArray, function ($key) use ($includes) {
                return in_array($key, $includes);
            }, ARRAY_FILTER_USE_KEY);
        }

        
        return array_filter($dataArray, function ($key) use ($excludes) {
            return !in_array($key, $excludes);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Validate UUID or Throw Exception
     *
     * @param string $uuid
     * @return boolean
     */
    public static function validUUIDOrThrowException(string $uuid) : bool
    {
        if (false === BaseModel::validUUID($uuid)) {
            throw new PublicException("Parameter given is not a valid uuid", StatusCodes::HTTP_BAD_REQUEST);
        }

        return true;
    }
}
