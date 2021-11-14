Webonxy GraphQL Middleware
==========================

To use the middleware in Laminas Mezzio, configure the factories

in `config/autoload/dependencies.global.php`

```php
return [
    'dependencies' => [
        'factories'  => [
            \GraphQL\Server\StandardServer::class => \Xaddax\GraphQL\Factory\StandardServerFactory::class,
            \Zestic\GraphQL\Middleware\GraphQLMiddleware::class => \Xaddax\GraphQL\Factory\GraphQLMiddlewareFactory::class,
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
        'schema' => \Path\To\Schema::class, // optional, defaults to webonxy Schema
        'schemaConfig' => [], // optional, if not configured expected in Schema class constructor
        'server' => \Path\To\Server::class, // not yet implemented, defaults to webonxy StandardServer
        'serverConfig' => [

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
