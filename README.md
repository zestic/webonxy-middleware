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
            'context' => \Zestic\GraphQL\Context\TokenContext::class
            'schema' => \Path\To\Your\Schema::class, 
        ],
    ],
];
```

see the [WebOnyx Server Configuration Documentation](https://webonyx.github.io/graphql-php/executing-queries/#server-configuration-options) for the full options for 
the server config.

You'll need to set up the route. In `config/routes.php`
```php
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->post('/graphql', \Zestic\GraphQL\Middleware\GraphQLMiddleware::class, 'graphql');
};
```

Schema Definition Language
--------------------------
You can also use a Schema Definition Language as discussed 
[in the WebOnxy documentation](https://webonyx.github.io/graphql-php/schema-definition-language/).

In `config/autoload/graphql.global.php` change the schema in the `serverConfig` to `generatedSchema`
```php
return [
    'graphQL' => [
        'serverConfig' => [
            'schema' => 'generatedSchema',
        ],
    ],
];
```
Then inside of the `graphQL` config add the `generatedSchema` configuration
```php
return [
    'graphQL' => [
        'generatedSchema' => [
            'parserOptions' => [
                'experimentalFragmentVariables' => true, // to parse fragments
                'noLocation' => false, // default, set true for development
            ],
            'cache' => [
                'alwaysEnabled' => false, // default, set to true to cache when the system cache is not enabled
                'directoryChangeFilename' => 'directory-change-cache.php', // default
                'schemaCacheFilename' => 'schema-cache.php', // default 
            ],
            'schemaDirectories' => [
                '/full/path/to/schema-directory-1',
                '/full/path/to/schema-directory-2',
            ],
        ],
    ],
];
```
See [the documentation](https://webonyx.github.io/graphql-php/class-reference/#graphqllanguageparser) for
`parserOptions`

The cached data is stored in `data/cache/graphql`.

