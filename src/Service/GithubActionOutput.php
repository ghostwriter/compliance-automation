<?php

declare(strict_types=1);

namespace Ghostwriter\Compliance\Service;

use Symfony\Component\Console\Output\OutputInterface;

final class GithubActionOutput
{
    /**
     * @see https://github.com/actions/toolkit/blob/457303960f03375db6f033e214b9f90d79c3fe5c/packages/core/src/command.ts#L80-L85
     */
    public const ESCAPED_DATA = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
    ];

    /**
     * @see https://github.com/actions/toolkit/blob/457303960f03375db6f033e214b9f90d79c3fe5c/packages/core/src/command.ts#L87-L94
     */
    public const ESCAPED_PROPERTIES = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
        ':' => '%3A',
        ',' => '%2C',
    ];

    public function __construct(
        private readonly OutputInterface $output
    ) {
    }

    /**
     * Output a debug log using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-a-debug-message
     */
    public function debug(
        string $message,
        string|null $file = null,
        int|null $line = null,
        int|null $col = null
    ): void {
        $this->log('debug', $message, $file, $line, $col);
    }

    /**
     * Output an error using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-an-error-message
     */
    public function error(
        string $message,
        string|null $file = null,
        int|null $line = null,
        int|null $col = null
    ): void {
        $this->log('error', $message, $file, $line, $col);
    }

    public static function isGithubActionEnvironment(): bool
    {
        return getenv('GITHUB_ACTIONS') !== false;
    }

    /**
     * Output a warning using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-a-warning-message
     */
    public function warning(
        string $message,
        string|null $file = null,
        int|null $line = null,
        int|null $col = null
    ): void {
        $this->log('warning', $message, $file, $line, $col);
    }

    private function log(
        string $type,
        string $message,
        string|null $file = null,
        int|null $line = null,
        int|null $col = null
    ): void {
        // Some values must be encoded.
        $message = strtr($message, self::ESCAPED_DATA);

        if (! $file) {
            // No file provided, output the message solely:
            $this->output->writeln(sprintf('::%s::%s', $type, $message));

            return;
        }

        $this->output->writeln(sprintf(
            '::%s file=%s,line=%s,col=%s::%s',
            $type,
            strtr($file, self::ESCAPED_PROPERTIES),
            strtr($line ?? 1, self::ESCAPED_PROPERTIES),
            strtr($col ?? 0, self::ESCAPED_PROPERTIES),
            $message
        ));
    }
}