Webonxy GraphQL Middleware
==========================

To use the middleware in Zend Expressive, configure the factories

in `config/autoload/dependencies.global.php`

```php
return [
    'dependencies' => [
        'factories'  => [
            \GraphQL\Server\StandardServer::class => \Xaddax\GraphQL\Factory\StandardServerFactory::class,
            \Xaddax\GraphQL\Middleware\GraphQLMiddleware::class => \Xaddax\GraphQL\Factory\GraphQLMiddlewareFactory::class,
        ],
    ],
];
```

Add configuration in `config/autoload/graphql.global.php`

```php
return [
    'graphQL' => [
        'middleware' => [
            'allowedHeaders' => [
                'application/graphql',
                'application/json',
            ],
        ],
        'schema' => \Path\To\Schema::class,
        'server' => [

        ],
    ],
];
```

see the [WebOnyx Server Configuration Documentation](http://webonyx.github.io/graphql-php/executing-queries/#server-configuration-options) for the full options for 
the server config.

You'll need to set up the route. In `config/routes.php`
```php
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->post('/graphql', \Xaddax\GraphQL\Middleware\GraphQLMiddleware::class, 'graphql');
};
```
