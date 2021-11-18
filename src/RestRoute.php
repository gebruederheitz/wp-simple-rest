<?php

namespace Gebruederheitz\Wordpress\Rest;

class RestRoute
{
    protected $path = '';

    protected $config = [
        'methods' => 'GET',
        'callback' => null,
        'permission_callback' => null,
        'args' => [],
    ];

    protected $name = '';

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Builder method for fluent creation.
     *
     * @param string $name
     * @param string $path
     *
     * @return static
     */
    public static function create(string $name, string $path): self
    {
        return new RestRoute($name, $path);
    }

    /**
     * @param string|array $allowedMethods
     *
     * @return $this
     */
    public function setMethods($allowedMethods): self
    {
        $this->config['methods'] = $allowedMethods;

        return $this;
    }

    public function setCallback(callable $callback): self
    {
        $this->config['callback'] = $callback;

        return $this;
    }

    public function setPermissionCallback(callable $permissionCallback): self
    {
        $this->config['permission_callback'] = $permissionCallback;

        return $this;
    }

    /**
     * Set the permission_callback so only users with the 'edit_posts' capability
     * can call this route.
     *
     * @return $this
     */
    public function allowOnlyEditors(): self
    {
        $this->config['permission_callback'] = function() {
            return current_user_can('edit_posts');
        };

        return $this;
    }

    /**
     * Set the permission_callback so only users with the 'install_plugins'
     * capability can call this route.
     *
     * @return $this
     */
    public function allowOnlyAdmins(): self
    {
        $this->config['permission_callback'] = function() {
            return current_user_can('install_plugins');
        };

        return $this;
    }

    /**
     * Set the permission_callback so this route effectively becomes public,
     * available to anyone.
     *
     * @return $this
     */
    public function allowAnyone(): self
    {
        $this->config['permission_callback'] = function() {
            return true;
        };

        return $this;
    }

    public function addArgument(
        string $name,
        string $description,
        $default = null,
        string $type = null,
        callable $sanitizeCallback = null,
        callable $validateCallback = null
    ): self
    {
        $arg = [
            'description' => $description
        ];

        if ($default !== null) {
            $arg['default'] = $default;
        }

        if (!empty($type)) {
            $arg['type'] = $type;
        }

        if ($sanitizeCallback !== null) {
            $arg['sanitize_callback'] = $sanitizeCallback;
        }

        if ($validateCallback !== null) {
            $arg['validate_callback'] = $validateCallback;
        }

        $this->config['args'][$name] = $arg;

        return $this;
    }

    public function addArguments(array $args): self
    {
        $this->config['args'] = array_merge($this->config['args'], $args);

        return $this;
    }

    public function replaceArguments(array $args): self
    {
        $this->config['args'] = $args;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'route' => $this->path,
            'config' => $this->config,
        ];
    }

}
