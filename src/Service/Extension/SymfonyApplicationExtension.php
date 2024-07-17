<?php

declare(strict_types=1);

namespace Ghostwriter\Compliance\Service\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Override;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

use const DIRECTORY_SEPARATOR;

use function dirname;
use function is_a;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function str_starts_with;

/**
 * @implements ExtensionInterface<SymfonyApplication>
 */
final readonly class SymfonyApplicationExtension implements ExtensionInterface
{
    public function __construct(
        private FilesystemInterface $filesystem,
    ) {
    }

    /**
     * @param SymfonyApplication $service
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): SymfonyApplication
    {
        $service->setAutoExit(false);
        $service->setCatchErrors(false);
        $service->setCatchExceptions(false);

        $namespace = str_replace('Service\\Extension', 'Command', __NAMESPACE__ . '\\');

        foreach ($this->filesystem->findIn(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Command') as $file) {
            $path = $file->getBasename('.php');

            if (str_starts_with($path, 'Abstract')) {
                continue;
            }

            if (! str_ends_with($path, 'Command')) {
                continue;
            }

            $class = sprintf('%s%s', $namespace, $path);
            if (! is_a($class, Command::class, true)) {
                continue;
            }

            $service->add($container->get($class));
        }

        $service->setDefaultCommand('run');

        return $service;
    }
}
