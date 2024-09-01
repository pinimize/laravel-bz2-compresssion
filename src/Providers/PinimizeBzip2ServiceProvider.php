<?php

declare(strict_types=1);

namespace Pinimize\Bzip2\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Pinimize\Bzip2\Compression\Bzip2Driver as Bzip2CompressionDriver;
use Pinimize\Bzip2\Decompression\Bzip2Driver as Bzip2DecompressionDriver;
use Pinimize\Contracts\CompressionContract;
use Pinimize\Contracts\DecompressionContract;
use Pinimize\Facades\Compression;
use Pinimize\Facades\Decompression;

class PinimizeBzip2ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Compression::extend(
            'bz2',
            fn (Application $application): CompressionContract => new Bzip2CompressionDriver($application['config']['pinimize']['compression']['drivers']['bz2'] ?? []),
        );
        Decompression::extend(
            'bz2',
            fn (Application $application): DecompressionContract => new Bzip2DecompressionDriver($application['config']['pinimize']['compression']['drivers']['bz2'] ?? []),
        );
    }
}
