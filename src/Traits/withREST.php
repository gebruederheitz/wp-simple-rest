<?php

namespace Gebruederheitz\Wordpress\Rest\Traits;

use Gebruederheitz\Wordpress\Rest\RestRoute;

trait withREST
{
    public static $restNamespaceBase = 'ghwp';
    public static $restApiVersion = '1';

    public static function initRestApi()
    {
        // Register REST API route
        add_action('rest_api_init', [static::class, 'registerRestRoutes']);
    }

    public function initInstanceRestApi ()
    {
        add_action('rest_api_init', [$this, 'registerInstanceRestRoutes']);
    }

    /**
     * Loops over items provided through getRestRoutes() and registers a REST API
     * route for each.
     */
    public static function registerRestRoutes ()
    {
        foreach (static::getRestRoutes() as $routeDefinition) {
            [$route, $options] = self::parseRoute($routeDefinition);
            register_rest_route(self::getRestNamespace(), $route, $options);
        }
    }

    public function registerInstanceRestRoutes()
    {
        foreach ($this->getInstanceRestRoutes() as $routeDefinition) {
            [$route, $options] = self::parseRoute($routeDefinition);
            register_rest_route(self::getRestNamespace(), $route, $options);
        }
    }

    /**
     * Make this function return an array of configurations to have the REST API
     * routes automatically set up.
     *
     * [
     *   'route' => (string) '/route/for/api/calls',
     *   'config' => (array) WPRestCondigArray
     *   'name' => (string) 'A Human-Readable Name for the endpoint',
     * ];
     *
     * Alternatively you can return an array of RestRoute objects.
     *
     * @return array[] | RestRoute[]
     */
    abstract protected static function getRestRoutes (): array;

    /**
     * @return array[] | RestRoute[]
     */
    abstract protected function getInstanceRestRoutes(): array;

    /**
     * Get the endpoints to use for REST API calls on the class implementing
     * this trait.
     *
     * @return string[] The full URLs to the class' REST endpoints
     */
    public static function getRestEndpoints(): array
    {
        $out = [];
        foreach (static::getRestRoutes() as $routeDefinition) {
            [$route, $options, $name] = self::parseRoute($routeDefinition);
            $out[$name] = get_rest_url(null, self::getRestNamespace() . $route);
        }
        return $out;
    }

    public function getAllRestEndpoints(): array
    {
        $out = [];

        foreach ($this->getInstanceRestRoutes() as $routeDefinition) {
            [$route, $options, $name] = self::parseRoute($routeDefinition);
            $out[$name] = get_rest_url(null, self::getRestNamespace() . $route);
        }

        return array_merge($out, self::getRestEndpoints());
    }

    public static function getRestNamespace() {
        return static::$restNamespaceBase . '/v' . static::$restApiVersion;
    }

    protected static function parseRoute($routeDefinition): array
    {
        $route = '';
        $options = [];
        $name = '';

        if (is_subclass_of($routeDefinition, RestRoute::class)) {
            $route = $routeDefinition->getPath();
            $options = $routeDefinition->getConfig();
            $name = $routeDefinition->getName();
        } else {
            $route = $routeDefinition['route'];
            $options = $routeDefinition['config'];
            $name = $routeDefinition['name'];
        }

        return [$route, $options, $name];
    }

}
