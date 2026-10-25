<?php

declare(strict_types=1);

/**
 * Panada Image Modifier (GD): resize, crop, or resize then crop.
 *
 *   $image = (new \Resources\Image())->setOption([
 *       'folder' => APP.'storage/uploads',
 *       'editType' => PIMG_RESIZE_CROP,
 *       'resizeWidth' => 300,
 *       'cropWidth' => 200,
 *       'cropHeight' => 200,
 *       'newFileName' => 'avatar-small',   // keep the original, write a copy
 *   ]);
 *
 *   if (!$image->edit('avatar.jpg')) {
 *       $errors = $image->getErrorMessage();
 *   }
 *
 * Works with JPEG, PNG (transparency kept) and GIF.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

if (!defined('PIMG_RESIZE')) {
    define('PIMG_RESIZE', 'resize');
}

if (!defined('PIMG_CROP')) {
    define('PIMG_CROP', 'crop');
}

if (!defined('PIMG_RESIZE_CROP')) {
    define('PIMG_RESIZE_CROP', 'resize_crop');
}

class Image
{
    use Configurable;

    /** resize | crop | resize_crop */
    public string $editType = PIMG_RESIZE;

    /** Compute the missing width or height from the image ratio. */
    public bool $autoRatio = true;

    public int $resizeWidth = 0;
    public int $resizeHeight = 0;
    public int $cropWidth = 0;
    public int $cropHeight = 0;

    /** Source file name and full path, set by edit(). */
    public string $fileName = '';
    public string $filePath = '';

    /** getimagesize() of the source. */
    public array|false $fileInfo = [];

    /** Write the result to this name (without extension) instead of the source. */
    public string $newFileName = '';

    /** Replace spaces in the new file name with "_". */
    public bool $stripSpaces = true;

    /** Folder of the source image. */
    public string $folder = '';

    /** IMAGETYPE_JPEG, IMAGETYPE_PNG or IMAGETYPE_GIF. */
    public ?int $imageType = null;

    /** JPEG quality, 0 - 100. */
    public int $jpegCompression = 90;

    /** Folder for the result, default: the source folder. */
    public string $saveTo = '';

    /** Errors of the last edit(). */
    public array $errorMessages = [];

    public function __construct()
    {
        if (!function_exists('imagecopyresampled')) {
            throw new RunException('Image resizing function that required by Image Class is not available.');
        }
    }

    public function edit(string $fileName): bool
    {
        $this->fileName = $fileName;
        $this->filePath = $this->folder . '/' . $fileName;
        $this->errorMessages = [];

        if (!$this->preErrorChecker()) {
            return false;
        }

        $this->initFileInfo();

        if (!$this->errorHandler() || !$image = $this->createImageFrom()) {
            return false;
        }

        [$width, $height] = $this->fileInfo;
        $newWidth = $width;
        $newHeight = $height;

        if ($this->resizeWidth > 0 && $this->resizeHeight === 0) {
            $newWidth = $this->resizeWidth;
            $newHeight = $this->autoRatio ? $height * $newWidth / $width : $height;
        } elseif ($this->resizeHeight > 0 && $this->resizeWidth === 0) {
            $newHeight = $this->resizeHeight;
            $newWidth = $this->autoRatio ? $width * $newHeight / $height : $width;
        } elseif ($this->resizeWidth > 0 && $this->resizeHeight > 0 && $this->editType === PIMG_RESIZE) {
            $newWidth = $this->resizeWidth;
            $newHeight = $this->resizeHeight;
        }

        // GD wants whole pixels, a ratio gives fractions.
        $newWidth = max(1, (int) round($newWidth));
        $newHeight = max(1, (int) round($newHeight));

        if ($this->editType === PIMG_RESIZE || $this->editType === PIMG_RESIZE_CROP) {
            $resized = $this->canvas($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        if ($this->editType === PIMG_CROP || $this->editType === PIMG_RESIZE_CROP) {
            if ($this->cropWidth < 1 || $this->cropHeight < 1) {
                $this->errorMessages[] = 'Set cropWidth and cropHeight to crop the image.';

                return false;
            }

            // Cut the middle of the image.
            $cropped = $this->canvas($this->cropWidth, $this->cropHeight);
            imagecopy(
                $cropped,
                $image,
                0,
                0,
                max(0, intdiv(imagesx($image) - $this->cropWidth, 2)),
                max(0, intdiv(imagesy($image) - $this->cropHeight, 2)),
                $this->cropWidth,
                $this->cropHeight,
            );
            $image = $cropped;
        }

        return $this->createImage($image);
    }

    private function preErrorChecker(): bool
    {
        if ($this->folder === '') {
            $this->errorMessages[] = 'No folder located. Please define the folder location.';
        } elseif (!is_dir($this->folder)) {
            $this->errorMessages[] = 'The folder ' . $this->folder . ' doesn\'t exists please create it first.';
        } elseif (!is_writable($this->folder)) {
            $this->errorMessages[] = 'The folder ' . $this->folder . ' is not writable.';
        } elseif (!is_file($this->filePath)) {
            $this->errorMessages[] = 'File ' . $this->filePath . ' doesn\'t exists.';
        }

        return $this->errorMessages === [];
    }

    private function errorHandler(): bool
    {
        if (!$this->fileInfo || !$this->fileInfo[0] || !$this->fileInfo[1]) {
            $this->errorMessages[] = 'Unable to get image dimensions.';

            return false;
        }

        return true;
    }

    public function initFileInfo(): void
    {
        // A file that is no image gives false (and maybe a notice), handled by errorHandler().
        $this->fileInfo = @getimagesize($this->filePath);
        $this->imageType = $this->fileInfo[2] ?? null;
    }

    public function createImageFrom(): \GdImage|false
    {
        $image = match ($this->imageType) {
            IMAGETYPE_GIF => imagecreatefromgif($this->filePath),
            IMAGETYPE_JPEG => imagecreatefromjpeg($this->filePath),
            IMAGETYPE_PNG => imagecreatefrompng($this->filePath),
            default => false,
        };

        if ($image === false) {
            $this->errorMessages[] = 'Unrecognized image format.';
        }

        return $image;
    }

    /**
     * Write the edited image to newFileName (or over the source).
     */
    public function createImage(\GdImage $image): bool
    {
        $folder = $this->saveTo !== '' ? $this->saveTo : $this->folder;
        $extension = Upload::getFileExtension($this->fileName);
        $name = $this->newFileName !== '' ? $this->newFileName . ($extension === '' ? '' : '.' . $extension) : $this->fileName;

        if ($this->stripSpaces) {
            $name = str_replace(' ', '_', $name);
        }

        $path = $folder . '/' . basename($name);

        if (!is_dir($folder) || !is_writable($folder)) {
            $this->errorMessages[] = 'File path invalid.';

            return false;
        }

        $saved = match ($this->imageType) {
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_JPEG => imagejpeg($image, $path, $this->jpegCompression),
            IMAGETYPE_PNG => imagepng($image, $path),
            default => false,
        };

        if (!$saved) {
            $this->errorMessages[] = 'File path invalid.';
        }

        return $saved;
    }

    public function getErrorMessage(): array
    {
        return $this->errorMessages;
    }

    /**
     * A blank image; PNG and GIF keep their transparency.
     */
    private function canvas(int $width, int $height): \GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);

        if ($this->imageType !== IMAGETYPE_JPEG) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        }

        return $canvas;
    }
}
