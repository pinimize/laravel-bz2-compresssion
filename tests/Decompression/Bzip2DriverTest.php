<?php

declare(strict_types=1);

namespace Pinimize\Bzip2\Tests\Decompression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Pinimize\Bzip2\Decompression\Bzip2Driver;
use Pinimize\Bzip2\Tests\TestCase;
use Pinimize\Exceptions\InvalidCompressedDataException;
use RuntimeException;

class Bzip2DriverTest extends TestCase
{
    private Bzip2Driver $bzip2Driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bzip2Driver = new Bzip2Driver([]);
    }

    #[Test]
    #[DataProvider('decompressionDataProvider')]
    public function it_can_decompress_string(string $input, string $expectedOutput): void
    {
        if (! function_exists('bzdecompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $decompressed = $this->bzip2Driver->string($input);
        $this->assertEquals($expectedOutput, $decompressed);
        $this->assertNotEquals($input, $decompressed);
    }

    public static function decompressionDataProvider(): array
    {
        return [
            'simple string' => [bzcompress('Hello, World!'), 'Hello, World!'],
            'empty string' => [bzcompress(''), ''],
            'long string' => [bzcompress(str_repeat('a', 1000)), str_repeat('a', 1000)],
        ];
    }

    #[Test]
    public function it_throws_exception_when_bz2_not_installed(): void
    {
        if (function_exists('bzdecompress')) {
            $this->markTestSkipped('Bzip2 extension is installed');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bzip2 extension is not installed');

        $this->bzip2Driver->string('Test');
    }

    #[Test]
    public function it_throws_exception_for_invalid_compressed_data(): void
    {
        if (! function_exists('bzdecompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $this->expectException(InvalidCompressedDataException::class);
        $this->expectExceptionMessage('Failed to decompress Bzip2 data');

        $this->bzip2Driver->string('Invalid compressed data');
    }

    #[Test]
    public function it_can_decompress_stream(): void
    {
        if (! function_exists('bzdecompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $compressed = bzcompress('Hello, World!');
        $input = fopen('php://temp', 'r+');
        fwrite($input, $compressed);
        rewind($input);

        $resource = $this->bzip2Driver->resource($input, []);

        $this->assertIsResource($resource);
        $this->assertEquals('Hello, World!', stream_get_contents($resource));

        fclose($input);
        fclose($resource);

    }
}
