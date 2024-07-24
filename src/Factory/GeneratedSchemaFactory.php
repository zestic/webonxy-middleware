<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\AST;
use Laminas\ConfigAggregator\ConfigAggregator;
use Psr\Container\ContainerInterface;

class GeneratedSchemaFactory
{
    private bool $cacheEnabled = false;
    private string $directoryChangeCacheFile;
    private array $parserOptions;
    private string $schemaCacheFile;
    private array $schemaDirectories;
    private array $schemaFiles;

    public function __invoke(ContainerInterface $container): Schema
    {
        $containerConfig = $container->get('config');
        $config = $containerConfig['graphQL']['generatedSchema'];
        $cacheConfig = $config['cache'] ?? [];
        $alwaysEnabled = $cacheConfig['alwaysEnabled'] ?? false;
        $systemEnabled = $containerConfig[ConfigAggregator::ENABLE_CACHE ] ?? false;
        $this->cacheEnabled = $alwaysEnabled || $systemEnabled;
        $this->schemaDirectories = $config['schemaDirectories'];
        $cacheDirectory = $cacheConfig['directory'] ?? getcwd() . '/' . $containerConfig['cache_directory'] . '/graphql';
        $directoryChangeFilename = $cacheConfig['directoryChangeFilename'] ?? 'schema-directory-cache.php';
        $this->directoryChangeCacheFile = $cacheDirectory . '/' . $directoryChangeFilename;
        $schemaFilename = $cacheConfig['schemaFilename'] ?? 'schema-cache.php';
        $this->schemaCacheFile = $cacheDirectory . '/' . $schemaFilename;
        $this->parserOptions = $config['parserOptions']?? [];

        return BuildSchema::build($this->getSourceAST());
    }

    private function isCacheValid(): bool
    {
        return
            $this->cacheEnabled &&
            !$this->isDirectoryChangeDetected() &&
            file_exists($this->schemaCacheFile);
    }

    private function isDirectoryChangeDetected(): bool
    {
        $currentFiles = $this->schemaFiles;
        $previousFiles = $this->readDirectoryChangeCache();

        return $currentFiles !== $previousFiles;
    }

    private function readDirectoryChangeCache(): array
    {
        if (file_exists($this->directoryChangeCacheFile)) {
            return require $this->directoryChangeCacheFile;
        }

        return [];
    }

    private function buildSourceAST(): DocumentNode
    {
       $source = $this->readGraphQLFiles();

       return Parser::parse($source, $this->parserOptions);
    }

    private function getSourceAST(): Node
    {
        // the directories need to be scanned for both cache checking
        // and source building, so do it before anything else
        $this->scanDirectories($this->schemaDirectories);

        if ($this->isCacheValid()) {
            return $this->readSourceASTFromCache();
        }
        $source = $this->buildSourceAST();
        $this->writeSourceASTToCache($source);
        $this->writeDirectoryChangeCache();

        return $source;
    }

    private function readGraphQLFiles(): string
    {
        $source = '';
        $schemaFiles = array_keys($this->schemaFiles);
        foreach ($schemaFiles as $file) {
            $source .= file_get_contents($file);
        }

        return $source;
    }

    private function readSourceASTFromCache(): Node
    {
        return AST::fromArray(require $this->schemaCacheFile);
    }

    private function writeDirectoryChangeCache(): void
    {
        file_put_contents($this->directoryChangeCacheFile, "<?php\nreturn " . var_export($this->schemaFiles, true) . ";\n");
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
                            $key = realpath($filePath);
                            $this->schemaFiles[$key] = filemtime($key);
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
