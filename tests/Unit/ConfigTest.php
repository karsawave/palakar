<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Config;
use Resources\RunException;

final class ConfigTest extends TestCase
{
    public function testBuiltInConfigFiles(): void
    {
        $this->assertSame('Home', Config::main()['defaultController']);
        $this->assertIsArray(Config::cache());
        $this->assertIsArray(Config::database());
        $this->assertIsArray(Config::security());
        $this->assertIsArray(Config::session());
    }

    public function testCustomConfigFile(): void
    {
        $this->assertSame('halo', Config::myowncfg()['greeting']);
    }

    public function testMissingConfigFileThrows(): void
    {
        $this->expectException(RunException::class);
        Config::doesNotExist();
    }
}
