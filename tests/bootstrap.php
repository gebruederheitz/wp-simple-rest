<?php

/**
 * Minimal test bootstrap.
 *
 * Loads the Composer autoloader and stubs out the handful of WordPress
 * functions/classes this package actually calls, so tests can run without
 * a full WordPress installation.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Recorder for add_action() calls, so tests can assert on what was hooked.
 *
 * @var array<int, array{hook: string, callback: mixed, priority: int, accepted_args: int}>
 */
$GLOBALS['__test_added_actions'] = [];

/**
 * Recorder for register_rest_route() calls, so tests can assert on what
 * routes/config were registered.
 *
 * @var array<int, array{namespace: string, route: string, args: mixed}>
 */
$GLOBALS['__test_registered_routes'] = [];

if (!function_exists('add_action')) {
    function add_action(
        string $hook,
        $callback,
        int $priority = 10,
        int $accepted_args = 1
    ): bool {
        $GLOBALS['__test_added_actions'][] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        return true;
    }
}

if (!function_exists('register_rest_route')) {
    function register_rest_route(string $namespace, string $route, $args = []): bool
    {
        $GLOBALS['__test_registered_routes'][] = [
            'namespace' => $namespace,
            'route' => $route,
            'args' => $args,
        ];

        return true;
    }
}

if (!function_exists('get_rest_url')) {
    function get_rest_url(?int $blogId = null, string $path = ''): string
    {
        return 'https://example.test/wp-json/' . ltrim($path, '/');
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability): bool
    {
        return false;
    }
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        /** @var mixed */
        public $data;

        public int $status;

        /**
         * @param mixed $data
         */
        public function __construct($data = null, int $status = 200)
        {
            $this->data = $data;
            $this->status = $status;
        }
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        public string $code;

        public string $message;

        /** @var mixed */
        public $data;

        /**
         * @param mixed $data
         */
        public function __construct(string $code = '', string $message = '', $data = null)
        {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }
    }
}
