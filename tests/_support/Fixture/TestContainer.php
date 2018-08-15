<?php
declare(strict_types=1);

namespace Tests\Fixture;

use Psr\Container\ContainerInterface;

final class TestContainer implements ContainerInterface
{
    private $values = [];

    public function get($id)
    {
        return $this->values[$id];
    }

    public function has($id)
    {
        return isset($this->values[$id]);
    }

    public function set($id, $value): TestContainer
    {
        $this->values[$id] = $value;

        return $this;
    }
}