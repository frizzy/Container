## Map - PHP Dependency Injection Container

### Usage

Add the following to your root composer.json file:

```json
{
    "require": {
        "frizzy/container": "~1.0"
    }
}
```

Add a factory:

```php
<?php

$container = new \Frizzy\Container\Container;

$container->set(
    'myFactory',
    function ($container) {
        return new \stdClass
    }
);


?>
```

Add a shared factory:

```php
<?php

$container = new \Frizzy\Container\Container;

$container->share(
    'mySharedFactory',
    function ($container) {
        return new \stdClass
    }
);

?>
```

Add a protected closure:

```php
<?php

$container = new \Frizzy\Container\Container;

$container->protect(
    'myProtectedClosure',
    function ($value) {
        return ucfirst($value);
    }
);

?>
```

Extend a factory:

```php

<?php

$container = new \Frizzy\Container\Container;

$container->share(
    'mySharedFactory',
    function ($container) {
        return new \stdClass
    }
);

$container->extend(
    'mySharedFactory',
    function ($container, $service) {
        $service->date = new \DateTime();
        $service->name = $container->get('otherService')->getName();
    }
);

?>
```

