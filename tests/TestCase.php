<?php

namespace Pinimize\Bzip2\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pinimize\Bzip2\Providers\PinimizeBzip2ServiceProvider;
use Pinimize\Providers\PinimizeServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PinimizeServiceProvider::class,
            PinimizeBzip2ServiceProvider::class,
        ];
    }
}
