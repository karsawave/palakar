<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Image;

final class ImageTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/panada-image-test';

        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }

        array_map('unlink', glob($this->dir . '/*'));
    }

    private function make(string $name, int $width, int $height, string $type = 'png', bool $transparent = false): void
    {
        $image = imagecreatetruecolor($width, $height);

        if ($transparent) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        }

        match ($type) {
            'png' => imagepng($image, $this->dir . '/' . $name),
            'jpeg' => imagejpeg($image, $this->dir . '/' . $name),
            'gif' => imagegif($image, $this->dir . '/' . $name),
        };
    }

    private function dimensions(string $name): array
    {
        return array_slice(getimagesize($this->dir . '/' . $name), 0, 2);
    }

    public function testResizeKeepsTheRatioInWholePixels(): void
    {
        $this->make('photo.png', 100, 75);
        $image = (new Image())->setOption(['folder' => $this->dir, 'resizeWidth' => '33']);

        $this->assertTrue($image->edit('photo.png'), implode(', ', $image->getErrorMessage()));
        $this->assertSame([33, 25], $this->dimensions('photo.png'));
    }

    public function testResizeAndCropIntoANewFile(): void
    {
        $this->make('photo with space.jpg', 200, 100, 'jpeg');
        $image = (new Image())->setOption([
            'folder' => $this->dir,
            'editType' => PIMG_RESIZE_CROP,
            'resizeWidth' => 100,
            'cropWidth' => 40,
            'cropHeight' => 40,
            'newFileName' => 'thumb',
        ]);

        $this->assertTrue($image->edit('photo with space.jpg'));
        $this->assertSame([40, 40], $this->dimensions('thumb.jpg'));
        $this->assertSame([200, 100], $this->dimensions('photo with space.jpg'));
    }

    public function testPngTransparencyIsKept(): void
    {
        $this->make('logo.png', 20, 20, 'png', true);

        $this->assertTrue((new Image())->setOption(['folder' => $this->dir, 'resizeWidth' => 10])->edit('logo.png'));

        $resized = imagecreatefrompng($this->dir . '/logo.png');
        $this->assertSame(127, imagecolorsforindex($resized, imagecolorat($resized, 5, 5))['alpha']);
    }

    public function testNotAnImageIsAnErrorNotAWarning(): void
    {
        file_put_contents($this->dir . '/fake.png', 'not an image');
        $image = (new Image())->setOption('folder', $this->dir);

        $this->assertFalse($image->edit('fake.png'));
        $this->assertSame(['Unable to get image dimensions.'], $image->getErrorMessage());
    }

    public function testCropNeedsASize(): void
    {
        $this->make('photo.gif', 30, 30, 'gif');
        $image = (new Image())->setOption(['folder' => $this->dir, 'editType' => PIMG_CROP]);

        $this->assertFalse($image->edit('photo.gif'));
        $this->assertStringContainsString('cropWidth', $image->getErrorMessage()[0]);
    }

    public function testErrorsBelongToTheLastEdit(): void
    {
        $image = new Image();
        $this->assertFalse($image->edit('x.png'), 'No folder');

        $image->setOption('folder', $this->dir);
        $this->assertFalse($image->edit('missing.png'));
        $this->assertCount(1, $image->getErrorMessage());
    }
}
