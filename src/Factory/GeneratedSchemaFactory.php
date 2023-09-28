<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\AST;
use Psr\Container\ContainerInterface;

class GeneratedSchemaFactory
{
    private array $files;
    private string $schemaCacheFile;

    public function __invoke(ContainerInterface $container): Schema
    {
        $containerConfig = $container->get('config');
        $config = $containerConfig['graphQL']['serverConfig'];
        $schemaDirectories = $config['schemaDirectories'];
        $this->schemaCacheFile = $config['schemaCacheFile'];

        $source = $this->getSourceAST($schemaDirectories);

        return BuildSchema::build($source);
    }

    private function isCacheValid(): bool
    {
        return file_exists($this->schemaCacheFile);
    }

    private function buildSourceAST(array $schemaDirectories): DocumentNode
    {
       $source = $this->readGraphQLFiles($schemaDirectories);

       return Parser::parse($source);
    }

    private function getSourceAST(array $schemaDirectories): Node
    {
        if ($this->isCacheValid()) {
            return $this->readSourceASTFromCache();
        }
        $source = $this->buildSourceAST($schemaDirectories);
        $this->writeSourceASTToCache($source);

        return $source;
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

    private function readSourceASTFromCache(): Node
    {
        return AST::fromArray(require $this->schemaCacheFile);
    }

    private function writeSourceASTToCache(DocumentNode $source): void
    {
        file_put_contents($this->schemaCacheFile, "<?php\nreturn " . var_export(AST::toArray($source), true) . ";\n");
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
}
