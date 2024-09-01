<?php

declare(strict_types=1);

namespace Pinimize\Bzip2\Decompression;

use Exception;
use Pinimize\Decompression\AbstractDecompressionDriver;
use Pinimize\Exceptions\InvalidCompressedDataException;
use RuntimeException;

/**
 * @phpstan-type Bzip2ConfigArray array{
 *     disk: string|null,
 *     block_size?: int,
 *     work_factor?: int,
 *     use_less_memory?: bool
 * }
 */
class Bzip2Driver extends AbstractDecompressionDriver
{
    public function getDefaultEncoding(): int
    {
        return 0; // Bzip2 doesn't use encoding
    }

    protected function decompressString(string $string, array $options): string
    {
        if (! function_exists('bzdecompress')) {
            throw new RuntimeException('Bzip2 extension is not installed');
        }

        try {
            /** @var string|int|false $result */
            $result = bzdecompress($string);
        } catch (Exception) {
            throw new InvalidCompressedDataException('Failed to decompress Bzip2 data');
        }

        if ($result === false ||is_int($result)) {
            throw new InvalidCompressedDataException('Failed to decompress Bzip2 data');
        }

        return $result;
    }

    protected function decompressStream($input, $output, array $options): void
    {
        if (! function_exists('bzdecompress')) {
            throw new RuntimeException('Bzip2 extension is not installed');
        }

        while (! feof($input)) {
            $chunk = fread($input, 8192);
            if ($chunk === false) {
                throw new RuntimeException('Failed to read from input stream');
            }

            /** @var string|false $decompressed */
            $decompressed = bzdecompress($chunk);
            if ($decompressed === false) {
                throw new InvalidCompressedDataException('Failed to decompress Bzip2 data');
            }

            fwrite($output, $decompressed);
        }
    }
}
