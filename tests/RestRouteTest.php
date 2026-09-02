<?php

namespace Gebruederheitz\Wordpress\Rest\Tests;

use Gebruederheitz\Wordpress\Rest\RestRoute;
use PHPUnit\Framework\TestCase;

class RestRouteTest extends TestCase
{
    public function testBuildsExpectedConfigArray(): void
    {
        $callback = function () {
            return 'callback';
        };
        $sanitize = function ($value) {
            return $value;
        };
        $validate = function ($value) {
            return true;
        };

        $route = RestRoute::create('my-route', '/my/route')
            ->setMethods(['GET', 'POST'])
            ->setCallback($callback)
            ->addArgument('id', 'The ID', 42, 'integer', $sanitize, $validate, true);

        $config = $route->getConfig();

        $this->assertSame(['GET', 'POST'], $config['methods']);
        $this->assertSame($callback, $config['callback']);
        $this->assertArrayHasKey('id', $config['args']);
        $this->assertSame(
            [
                'description' => 'The ID',
                'default' => 42,
                'type' => 'integer',
                'sanitize_callback' => $sanitize,
                'validate_callback' => $validate,
                'required' => true,
            ],
            $config['args']['id']
        );

        $this->assertSame(
            [
                'name' => 'my-route',
                'route' => '/my/route',
                'config' => $config,
            ],
            $route->toArray()
        );
    }

    public function testAddArgumentAcceptsNullForOptionalParameters(): void
    {
        $route = RestRoute::create('nullable-route', '/nullable');

        // Type, sanitize- and validate-callback are all nullable since they
        // are implicitly-nullable (deprecated as of PHP 8.4) by default.
        $route->addArgument('name', 'A name');

        $config = $route->getConfig();

        $this->assertSame(
            ['description' => 'A name'],
            $config['args']['name']
        );
    }

    public function testPermissionHelpersSetCallableCallback(): void
    {
        $route = RestRoute::create('perm-route', '/perm');

        $route->allowAnyone();
        $config = $route->getConfig();

        $this->assertIsCallable($config['permission_callback']);
        $this->assertTrue(($config['permission_callback'])());
    }
}
