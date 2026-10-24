<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class AutoloaderTest extends TestCase
{
    public function testComposerLoadsFrameworkClasses(): void
    {
        $this->assertTrue(class_exists(\Resources\Validation::class));
        $this->assertTrue(class_exists(\Drivers\Database\PanadaPDO::class));
    }

    public function testPanadaLoaderFindsAppClassesComposerDoesNotKnow(): void
    {
        // Composer only knows Resources\ and Drivers\. Classes of the app
        // (Controllers, Models, Libraries, Modules) live wherever APP points,
        // so Panada's own loader finds them.
        $this->assertTrue(class_exists(\Libraries\FixtureOnly::class));
    }

    public function testCustomNamespaceFromMainConfig(): void
    {
        $this->assertTrue(class_exists(\Fixture\Thing::class));
    }

    public function testLoaderNeverThrowsForUnknownClasses(): void
    {
        \Gear::loader('Nope\\Missing\\Thing');
        \Gear::loader('Controllers\\DoesNotExist');

        $this->assertFalse(class_exists('Controllers\\DoesNotExist'));
    }
}
