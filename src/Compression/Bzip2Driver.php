<?php

declare(strict_types=1);

namespace Pinimize\Bzip2\Compression;

use Exception;
use Pinimize\Compression\AbstractCompressionDriver;
use RuntimeException;

/**
 * @phpstan-type Bzip2ConfigArray array{
 *     disk: string|null,
 *     block_size?: int,
 *     work_factor?: int,
 *     use_less_memory?: bool
 * }
 */
class Bzip2Driver extends AbstractCompressionDriver
{
    public function getDefaultEncoding(): int
    {
        return 0; // Bzip2 doesn't use encoding
    }

    protected function compressString(string $string, int $level, int $encoding): string
    {
        if (! function_exists('bzcompress')) {
            throw new RuntimeException('Bzip2 extension is not installed');
        }

        try {
            /** @var string|false $compressed */
            $compressed = bzcompress($string);
        } catch (Exception) {
            throw new RuntimeException('Failed to compress string');
        }

        if ($compressed === '' || $compressed === '0' || $compressed === false) {
            throw new RuntimeException('Failed to compress string');
        }

        return $compressed;
    }

    public function getSupportedAlgorithms(): array
    {
        return [0]; // Bzip2 only has one algorithm
    }

    public function getFileExtension(): string
    {
        return 'bz2';
    }

    protected function compressStream($input, $output, array $options): void
    {
        if (! function_exists('bzcompress')) {
            throw new RuntimeException('Bzip2 extension is not installed');
        }

        while (! feof($input)) {
            $chunk = fread($input, 8192);
            if ($chunk === false) {
                throw new RuntimeException('Failed to read from input stream');
            }

            $compressed = bzcompress($chunk);
            fwrite($output, $compressed);
        }
    }
}
