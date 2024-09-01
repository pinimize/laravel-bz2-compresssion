<?php

namespace Pinimize\Bzip2\Tests\Providers;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Pinimize\Bzip2\Compression\Bzip2Driver as Bzip2CompressionDriver;
use Pinimize\Bzip2\Decompression\Bzip2Driver as Bzip2DecompressionDriver;
use Pinimize\Bzip2\Tests\TestCase;
use Pinimize\Facades\Compression;
use Pinimize\Facades\Decompression;

class PinimizeBzip2ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_extends_compression_manager_with_bz2_driver(): void
    {
        $this->assertInstanceOf(Bzip2CompressionDriver::class, Compression::driver('bz2'));
    }

    #[Test]
    public function it_extends_decompression_manager_with_bz2_driver(): void
    {
        $this->assertInstanceOf(Bzip2DecompressionDriver::class, Decompression::driver('bz2'));
    }

    #[Test]
    public function it_registers_bz2_drivers_with_correct_config(): void
    {
        putenv('COMPRESSION_DRIVER=bz2');
        putenv('COMPRESSION_DISK=test');
        $this->refreshApplication();

        Config::set('pinimize.compression.drivers.bz2', ['disk' => env('COMPRESSION_DISK')]);
        $this->assertInstanceOf(Bzip2CompressionDriver::class, Compression::driver());
        $this->assertInstanceOf(Bzip2DecompressionDriver::class, Decompression::driver());

        $this->assertEquals('test', Compression::getConfig()['disk']);
        $this->assertEquals('test', Decompression::getConfig()['disk']);
    }
}
