<?php

namespace Tests;

use Eptic\ApplicationMenu\ApplicationMenuServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [ApplicationMenuServiceProvider::class];
    }
}
