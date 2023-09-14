<?php
declare(strict_types=1);

namespace Test\Fixture;

use Psr\Container\ContainerInterface;

final class TestContainer implements ContainerInterface
{
    public function __construct(
        public array $values = [],
    ) {}

    public function get($id)
    {
        return $this->values[$id];
    }

    public function has($id): bool
    {
        return isset($this->values[$id]);
    }

    public function set($id, $value): TestContainer
    {
        $this->values[$id] = $value;

        return $this;
    }
}
