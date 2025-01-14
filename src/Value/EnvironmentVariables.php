<?php

declare(strict_types=1);

namespace Ghostwriter\Compliance\Value;

use Ghostwriter\Compliance\Exception\VariableNotFoundException;
use Throwable;

use function array_key_exists;
use function getenv;

final class EnvironmentVariables
{
    private function __construct(
        private array $variables = [],
    ) {}

    public static function new(): self
    {
        $variables = [];

        foreach ($_ENV as $name => $value) {
            $variables[$name] = (string) $value;
        }

        foreach (getenv() ?: [] as $name => $value) {
            $variables[$name] = $value;
        }

        return new self($variables);
    }

    /**
     * @throws Throwable
     */
    public function get(string $name, ?string $default = null): string
    {
        return match (true) {
            array_key_exists($name, $this->variables) => $this->variables[$name],
            null !== $default => $default,
            default => throw new VariableNotFoundException($name),
        };
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    public function set(string $name, string $value): void
    {
        $this->variables[$name] = $value;

        $_ENV[$name] = $value;

        $_SERVER[$name] = $value;

        // putenv($name . '=' . $value);
    }
}
