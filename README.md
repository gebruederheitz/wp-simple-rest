# Wordpress Simple REST Trait

_A trait to help you set up REST endpoints in Wordpress._

---

Helps you with registering and processing REST endpoints in Wordpress projects.

 - [Installation](#installation)
 - [Usage](#usage)
   - [Getting the defined routes' full URLs](#getting-the-defined-routes-full-urls)
   - [Changing the route base path](#changing-the-route-base-path)
 - [Upgrading](#upgrading)

## Installation

via composer:
```shell
> composer require gebruederheitz/wp-simple-rest
```

Make sure you have Composer autoload or an alternative class loader present.

## Usage

```php
use Gebruederheitz\Traits\withREST;
/* optional */
use Gebruederheitz\Wordpress\Rest\RestRoute;

class MyClass {
    // use the trait
    use withREST;
    
    public function __construct() {
        // initialize the REST functionality
        $this->initInstanceRestApi();
    }
    
    // Callbacks for your routes must be public
    public function myRestCallback() {}
    
    // Define your Routes by implementing the abstract method
    public function getInstanceRestRoutes()
    {
        return [
            // Using the RestRoute helper object
            RestRoute::create(
                'A description of what your route does.',
                '/rest-example'
            )
                ->setCallback([$this, 'myRestCallback'])
                ->setMethods('POST')
                ->allowAnyone()
                ->addArgument(
                    'parameter_one',
                    'A description of what the argument is for.',
                    'a default value',
                    'string',
                    [$this, 'sanitizeParameterOne'],
                    [$this, 'validateParameterOne']
                )
                ->addArgument(
                    'other_param',
                    'A parameter that is essential to this route.',
                )
            ,
            // Using the legacy array format
            [
                'name' => 'A description of what your route does',
                'route' => '/rest-example',
                'config' => [
                    'methods' => 'POST',
                    'callback' => [$this, 'myRestCallback'],
                    'permission_callback' => function () {
                        return current_user_can('edit_posts');
                    },
                    'args' => [
                        'parameter_one' => [
                            'description' => 'A description of what the argument is for',
                            'default' => '',
                            'type' => 'string',
                            'sanitize_callback' => 'esc_url_raw',
                        ]
                    ]
                ]
            ],
            // Restricting access
            RestRoute::create(
                'Do dangerous things with the database',
                '/danger-zone/database',
            )
                ->setCallback([$this, 'restDangerousDbOperation'])
                // only users with 'install_plugins' capabilities
                ->allowOnlyAdmins()
                // or only users with 'edit_posts' capabilities
                ->allowOnlyEditors()
                // or a custom callback allowing you to check for capabilities
                // ...with a closure
                ->setPermissionCallback(function() {
                    return current_user_can('read_private_pages');
                })
                // ...with a class method
                ->setPermissionCallback([$this, 'canUserAccessDbDangerZone'])
                // ...with a static class method
                ->setPermissionCallback([self::class, 'canUserAccessStatic'])
            ,
        ];
    }
    
    // Must have an implementation, in this case it's just a dummy
    protected static function getRestRoutes(): array 
    {
        return [];
    }
}
```

You could now execute your callback with the following request:
```
POST https://example.com/wp-json/ghwp/v1/rest-example
```

Alternatively you can use the static variant allowing you to call only static 
methods on your class:

```php
use Gebruederheitz\Traits\withREST;

class MyClass {
    // use the trait
    use withREST;
    
    public static function init() {
        // initialize the REST functionality
        self::initRestApi();
    }
    
    public static function myRestCallback() {}
    
    // This is the dummy this time around
    public function getInstanceRestRoutes()
    {
        return [];
    }
    
    // Define your Routes by implementing the abstract method
    protected static function getRestRoutes(): array 
    {
        return [
            [
                'name' => 'A description of what your route does',
                'route' => '/rest-example',
                'config' => [
                    'methods' => 'POST',
                    'callback' => [self::class, 'myRestCallback'],
                    'permission_callback' => function () {
                        return current_user_can('edit_posts');
                    },
                    'args' => [
                        'parameter_one' => [
                            'description' => 'A description of what the argument is for',
                            'default' => '',
                            'type' => 'string',
                            'sanitize_callback' => 'esc_url_raw',
                        ]
                    ]
                ]
            ],
        ];
    }
}
```

### Setting the route's access

You should always explicitly define your route's access privileges, otherwise
Wordpress will wiggle its finger in your face. You can choose one of the
convenience methods provided by `RestRoute` or supply your own callback:

```php
RestRoute::create('Name', '/path')
    // public route
    ->allowAnyone()
    // or only users with 'install_plugins' capabilities
    ->allowOnlyAdmins()
    // or only users with 'edit_posts' capabilities
    ->allowOnlyEditors()
    // or a custom callback allowing you to check for capabilities
    // ...with a closure
    ->setPermissionCallback(function() {
        return current_user_can('read_private_pages');
    })
    // ...with a class method
    ->setPermissionCallback([$this, 'canUserAccessDbDangerZone'])
    // ...with a static class method
    ->setPermissionCallback([self::class, 'canUserAccessStatic'])
```

### Getting the defined routes' full URLs

```php
// list only the static routes
MyClass::getRestEndpoints();
// Both static and instance routes
$myClassInstance->getAllRestEndpoints();
```


### Changing the route base path

```php
class MyClass {
    use \Gebruederheitz\Traits\withREST;
    
    public static $restNamespaceBase = 'my-rest-routes';
}
```

Your routes will now be available at `/wp-json/my-rest-routes/v1/rest-example`.


## Upgrading

### To version 2.x

From v1.x to v2.0 the namespaces used in the library changed. You will need to
update your `use` statements to reflect those changes:
```php
/* BEFORE */
use Gebruederheitz\Traits\Rest\withREST
/* AFTER */
use Gebruederheitz\Wordpress\Rest\Traits\withREST
```
