<?php

declare(strict_types=1);

namespace App;

use PHPUnit\Framework\TestCase as UnitTestCase;
use Slim\App;

abstract class TestCase extends UnitTestCase
{
    /**
     * @var App Slim application
     */
    protected static $app;

    abstract public function createApplication(): App;

    protected static function getApp(): App
    {
        self::$app = static::createApplication();

        return self::$app;
    }

    protected function refreshApplication(): void
    {
        self::$app = $this->createApplication();
    }

    protected function setUp(): void
    {
        if (self::$app) {
            return;
        }

        $this->refreshApplication();
    }
}
