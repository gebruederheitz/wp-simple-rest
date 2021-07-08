# Wordpress Simple REST Trait

_A trait to help you set up REST endpoints in Wordpress._

---

Helps you with registering and processing REST endpoints in Wordpress projects.

# Installation

via composer:
```shell
> composer require gebruederheitz/wp-simple-rest
```

Make sure you have Composer autoload or an alternative class loader present.

# Usage

```php
use Gebruederheitz\Traits\withREST;

class MyClass {
    // use the trait
    use withREST;
    
    public function __construct() {
        // initialize the REST functionality
        $this->initInstanceRestApi();
    }
    
    public function myRestCallback() {}
    
    // Define your Routes by implementing the abstract method
    public function getInstanceRestRoutes()
    {
        return [
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
