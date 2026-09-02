<?php

namespace Gebruederheitz\Wordpress\Rest\Tests;

use Gebruederheitz\Wordpress\Rest\RestRoute;
use Gebruederheitz\Wordpress\Rest\Traits\withREST;
use PHPUnit\Framework\TestCase;

class WithRestTraitFixture
{
    use withREST;

    protected static function getRestRoutes(): array
    {
        return [
            RestRoute::create('greet', '/greet')
                ->setMethods('GET')
                ->setCallback(function () {
                    return 'hi';
                }),
        ];
    }

    protected function getInstanceRestRoutes(): array
    {
        return [];
    }
}

class WithRESTTraitTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__test_added_actions'] = [];
        $GLOBALS['__test_registered_routes'] = [];
    }

    public function testRegisterRestRoutesRegistersRouteViaStub(): void
    {
        WithRestTraitFixture::registerRestRoutes();

        $registered = $GLOBALS['__test_registered_routes'];

        $this->assertCount(1, $registered);
        $this->assertSame('ghwp/v1', $registered[0]['namespace']);
        $this->assertSame('/greet', $registered[0]['route']);
        $this->assertSame('GET', $registered[0]['args']['methods']);
    }

    public function testInitRestApiHooksRegisterRestRoutesIntoRestApiInit(): void
    {
        WithRestTraitFixture::initRestApi();

        $actions = $GLOBALS['__test_added_actions'];

        $this->assertCount(1, $actions);
        $this->assertSame('rest_api_init', $actions[0]['hook']);
        $this->assertSame(
            [WithRestTraitFixture::class, 'registerRestRoutes'],
            $actions[0]['callback']
        );
    }
}
