<?php

declare(strict_types=1);

namespace Ghostwriter\Compliance\Tool;

final class Infection extends AbstractTool
{
    public function command(): string
    {
        return 'composer ghostwriter:infection:run';
    }

    /**
     * @return string[]
     */
    public function configuration(): array
    {
        return ['infection.json5', 'infection.json', 'infection.json.dist', 'infection.json5.dist'];
    }
}
