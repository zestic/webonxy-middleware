<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\AST;
use Psr\Container\ContainerInterface;

class GeneratedSchemaFactory
{
    private array $files;

    public function __invoke(ContainerInterface $container): Schema
    {
        $containerConfig = $container->get('config');
        $config = $containerConfig['graphQL']['serverConfig'];
        $schemaDirectories = $config['schemaDirectories'];

        $source = $this->getSourceAST($schemaDirectories);
        $typeConfigDecorator = function () {};

        return BuildSchema::build($source);
    }

    private function isCacheValid(): bool
    {
        return false;
    }

    private function getCacheFilename(): string
    {

    }

    private function buildSourceAST(array $schemaDirectories): DocumentNode
    {
       $source = $this->readGraphQLFiles($schemaDirectories);

       return Parser::parse($source);
    }

    private function getSourceAST(array $schemaDirectories): DocumentNode
    {
       return $this->buildSourceAST($schemaDirectories);
    }

    private function readGraphQLFiles(array $schemaDirectories): string
    {
        $this->scanDirectories($schemaDirectories);

        $source = '';
        foreach ($this->files as $file) {
            $source .= file_get_contents($file);
        }

        return $source;
    }

    private function scanDirectories(array $directories): void
    {
        $subDirectories = [];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                if ($dh = opendir($directory)) {
                    while (($file = readdir($dh)) !== false) {
                        $filePath = $directory . '/' . $file;
                        $info = pathinfo($filePath);
                        if (isset($info['extension']) && $info['extension'] === 'graphql') {
                            $this->files[] = realpath($filePath);
                        };
                        if ($info['basename'] === $info['filename']) {
                            $subDirectories[] = $filePath;
                        }
                    }
                    closedir($dh);
                }
            }
        }
        if (!empty($subDirectories)) {
            $this->scanDirectories($subDirectories);
        }
    }

    private function example(): void
    {

        $cacheFilename = 'cached_schema.php';

        if (!file_exists($cacheFilename)) {
            $source = Parser::parse(file_get_contents('./schema.graphql'));
            file_put_contents($cacheFilename, "<?php\nreturn " . var_export(AST::toArray($source), true) . ";\n");
        } else {
            $source = AST::fromArray(require $cacheFilename); // fromArray() is a lazy operation as well
        }
    }
}
