<?php

declare(strict_types=1);

namespace Pinimize\Bzip2\Tests\Compression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Pinimize\Bzip2\Compression\Bzip2Driver;
use Pinimize\Bzip2\Tests\TestCase;
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
    public function it_can_get_correct_file_extension(): void
    {
        $this->assertEquals('bz2', $this->bzip2Driver->getFileExtension());
    }

    #[Test]
    #[DataProvider('compressionDataProvider')]
    public function it_can_compress_string(string $input, string $expectedOutput): void
    {
        if (! function_exists('bzcompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $compressed = $this->bzip2Driver->string($input);
        $this->assertEquals($expectedOutput, $compressed);
        $this->assertNotEquals($input, $compressed);
    }

    public static function compressionDataProvider(): array
    {
        return [
            'simple string' => ['Hello, World!', bzcompress('Hello, World!')],
            'empty string' => ['', bzcompress('')],
            'long string' => [str_repeat('a', 1000), bzcompress(str_repeat('a', 1000))],
        ];
    }

    #[Test]
    public function it_throws_exception_when_bz2_not_installed(): void
    {
        if (function_exists('bzcompress')) {
            $this->markTestSkipped('Bzip2 extension is installed');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bzip2 extension is not installed');

        $this->bzip2Driver->string('Test');
    }

    #[Test]
    public function it_can_compress_stream(): void
    {
        if (! function_exists('bzcompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $input = fopen('php://temp', 'r+');
        fwrite($input, 'Hello, World!');
        rewind($input);

        $resource = $this->bzip2Driver->resource($input, []);

        $this->assertIsResource($resource);
        $this->assertEquals(bzcompress('Hello, World!'), stream_get_contents($resource));

        fclose($input);
        fclose($resource);
    }

    #[Test]
    #[DataProvider('fileDataProvider')]
    public function it_compresses_file_and_writes_smaller_file_to_disk(string $sourceFile): void
    {
        if (! function_exists('bzcompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $targetFile = $sourceFile.'.bz2';

        // Ensure the source file exists
        $this->assertFileExists($sourceFile, 'Source file does not exist');

        $resource = fopen($sourceFile, 'r');
        $this->assertNotFalse($resource, 'Failed to open source file');

        $data = $this->bzip2Driver->string($resource);

        $bytesWritten = file_put_contents($targetFile, $data);

        // Close the resource
        fclose($resource);

        // Assert that the file was written successfully
        $this->assertNotFalse($bytesWritten, 'Failed to write compressed data to file');
        $this->assertFileExists($targetFile, 'Compressed file was not created');

        // Compare file sizes
        $originalSize = filesize($sourceFile);
        $compressedSize = filesize($targetFile);

        $this->assertLessThan($originalSize, $compressedSize, 'Compressed file is not smaller than the original');

        // Clean up: remove the compressed file
        unlink($targetFile);
    }

    #[Test]
    #[DataProvider('fileDataProvider')]
    public function it_compresses_file_using_resource_method_and_writes_smaller_file_to_disk(string $sourceFile): void
    {
        if (! function_exists('bzcompress')) {
            $this->markTestSkipped('Bzip2 extension is not installed');
        }

        $targetFile = $sourceFile.'.bz2';

        // Ensure the source file exists
        $this->assertFileExists($sourceFile, 'Source file does not exist');

        $inputResource = fopen($sourceFile, 'r');
        $this->assertNotFalse($inputResource, 'Failed to open source file');

        $outputResource = fopen($targetFile, 'w');
        $this->assertNotFalse($outputResource, 'Failed to open target file for writing');

        // Use the resource method of LzfDriver
        $compressedResource = $this->bzip2Driver->resource($inputResource);

        // Copy the compressed data to the output file
        stream_copy_to_stream($compressedResource, $outputResource);

        // Close all resources
        fclose($inputResource);
        fclose($outputResource);
        fclose($compressedResource);

        // Assert that the file was written successfully
        $this->assertFileExists($targetFile, 'Compressed file was not created');

        // Compare file sizes
        $originalSize = filesize($sourceFile);
        $compressedSize = filesize($targetFile);

        $this->assertLessThan($originalSize, $compressedSize, 'Compressed file is not smaller than the original');

        // Verify the compressed content can be decompressed
        $compressedContent = file_get_contents($targetFile);
        $decompressedContent = bzdecompress($compressedContent);
        $this->assertNotFalse($decompressedContent, 'Failed to decompress the content');
        $this->assertEquals(file_get_contents($sourceFile), $decompressedContent, 'Decompressed content does not match the original');

        // Clean up: remove the compressed file
        unlink($targetFile);
    }

    public static function fileDataProvider(): array
    {
        return [
            'CSV file' => [__DIR__.'/../Fixtures/data.csv'],
            'JSON file' => [__DIR__.'/../Fixtures/data.json'],
        ];
    }

    protected function tearDown(): void
    {
        // Ensure any leftover compressed files are removed
        foreach (self::fileDataProvider() as $testCase) {
            $targetFile = $testCase[0].'.bz2';
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
        }

        parent::tearDown();
    }
}
