<?php

declare(strict_types=1);

/**
 * Panada Upload API.
 *
 *   $upload = new \Resources\Upload();
 *   $upload->setOption([
 *       'folderLocation' => APP.'storage/uploads',
 *       'permittedFileType' => 'jpg|jpeg|png|pdf',
 *       'maximumSize' => 2_000_000,
 *       'autoRename' => true,
 *   ]);
 *
 *   if (!$upload->now($_FILES['photo'])) {
 *       return e($upload->getError('message'));
 *   }
 *
 *   $info = $upload->getFileInfo();   // extension, name, folder, mime, size
 *
 * Files a web server could run (.php, .phtml, .phar, .htaccess, ...) are
 * always refused, whatever permittedFileType says. Still, keep uploads
 * outside public/ when you can.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

// Edit types for the 'editImage' option, see Resources\Image.
if (!defined('PIMG_RESIZE')) {
    define('PIMG_RESIZE', 'resize');
}

if (!defined('PIMG_CROP')) {
    define('PIMG_CROP', 'crop');
}

if (!defined('PIMG_RESIZE_CROP')) {
    define('PIMG_RESIZE_CROP', 'resize_crop');
}

class Upload
{
    use Configurable;

    /**
     * Names a web server may execute as code (also "x.php.jpg", which some
     * Apache setups run) or that change its configuration.
     */
    private const EXECUTABLE = '/\.(php\d*|phtml|pht|phar|phps)(\.|$)|^\.(htaccess|user\.ini)$/i';

    /** The $_FILES entry being uploaded. */
    public array $file = [];

    /** Last error: object with code and message, or null. */
    public ?\stdClass $error = null;

    public array $errorMessages = [
        1 => 'File upload failed due to unknown error.',
        2 => 'No folder located. Please define the folder location.',
        3 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        4 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        5 => 'The uploaded file was only partially uploaded.',
        6 => 'No file was uploaded.',
        7 => 'Missing a temporary folder.',
        8 => 'Failed to write file to disk.',
        9 => 'File upload stopped by extension.',
        10 => 'Folder you\'ve defined does not exist.',
        11 => 'Can\'t create new folder in your defined folder.',
        12 => 'Uploaded file not permitted.',
        13 => 'The uploaded file exceeds the maximum size.',
        14 => 'File uploaded, but editing image has failed with the following error(s): ',
        15 => 'Folder you specified not writeable.',
    ];

    public string $folderLocation = '';

    /** File name without extension; the original extension is kept. */
    public string $setFileName = '';

    /** Give the file a random name. */
    public bool $autoRename = false;

    /** Replace spaces in the file name with "_". */
    public bool $stripSpaces = true;

    /** Maximum size in bytes, 0 for no limit. */
    public int $maximumSize = 0;

    /** Store in folderLocation/year/month. */
    public bool $autoCreateFolder = false;

    /** Information about the stored file, see getFileInfo(). */
    public array $getFileInfo = [];

    /** Allowed extensions as a regex alternation, e.g. 'jpg|jpeg|png'. */
    public string $permittedFileType = '';

    public ?Image $image = null;

    /** Image edits after upload, each an array of Image options. */
    public array $editImage = [];

    /**
     * Process the upload of one $_FILES entry.
     */
    public function now(array $file): bool
    {
        $this->file = $file + ['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0];
        $this->error = null;
        $this->getFileInfo = [];

        $folder = $this->folder();

        if ($folder === null || !$this->check() || !$this->upload($folder)) {
            return false;
        }

        foreach ($this->editImage as $options) {
            $this->image = new Image();
            $this->image->folder = $folder;
            $this->image->setOption($options);

            if (!$this->image->edit($this->getFileInfo['name'])) {
                return $this->setError(14);
            }
        }

        return true;
    }

    /**
     * The destination folder, created when missing; null on error.
     */
    private function folder(): ?string
    {
        if ($this->folderLocation === '') {
            $this->setError(2);

            return null;
        }

        $folder = rtrim($this->folderLocation, '/\\').($this->autoCreateFolder ? '/'.date('Y/m') : '');

        if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) {
            $this->setError(11);

            return null;
        }

        if (!is_writable($folder)) {
            $this->setError(15);

            return null;
        }

        return $folder;
    }

    private function check(): bool
    {
        if ($this->maximumSize > 0 && $this->file['size'] > $this->maximumSize) {
            return $this->setError(13);
        }

        if ((int) $this->file['error'] !== UPLOAD_ERR_OK) {
            return $this->setError(match ((int) $this->file['error']) {
                UPLOAD_ERR_INI_SIZE => 3,
                UPLOAD_ERR_FORM_SIZE => 4,
                UPLOAD_ERR_PARTIAL => 5,
                UPLOAD_ERR_NO_FILE => 6,
                UPLOAD_ERR_NO_TMP_DIR => 7,
                UPLOAD_ERR_CANT_WRITE => 8,
                UPLOAD_ERR_EXTENSION => 9,
                default => 1,
            });
        }

        if ($this->permittedFileType !== '' && !preg_match('!\.('.$this->permittedFileType.')$!i', $this->file['name'])) {
            return $this->setError(12);
        }

        return true;
    }

    public static function getFileExtension(string $file): string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    private function upload(string $folder): bool
    {
        // basename(): a name must never point outside the folder.
        $original = basename(str_replace('\\', '/', $this->file['name']));
        $extension = self::getFileExtension($original);
        $suffix = $extension === '' ? '' : '.'.$extension;

        $name = match (true) {
            $this->autoRename => bin2hex(random_bytes(8)).$suffix,
            $this->setFileName !== '' => basename(str_replace('\\', '/', $this->setFileName)).$suffix,
            default => $original,
        };

        if ($this->stripSpaces) {
            $name = str_replace(' ', '_', $name);
        }

        // Windows drops trailing dots and spaces: "shell.php." is "shell.php".
        $name = rtrim($name, ' .');

        if ($name === '' || preg_match(self::EXECUTABLE, $name)) {
            return $this->setError(12);
        }

        $this->getFileInfo = [
            'extension' => $extension,
            'name' => $name,
            'folder' => $folder,
            'mime' => self::getMimeTypes($name)['type'] ?? 'application/octet-stream',
            'size' => $this->file['size'],
        ];

        if (!move_uploaded_file($this->file['tmp_name'], $folder.'/'.$name)) {
            return $this->setError(1);
        }

        return true;
    }

    private function setError(int $code): bool
    {
        $imageErrors = $code === 14 && $this->image ? implode(', ', $this->image->errorMessages) : '';
        $this->error = (object) ['code' => $code, 'message' => $this->errorMessages[$code].$imageErrors];

        return false;
    }

    /**
     * Mime type from the file extension. Original from Wordpress 3.0
     * get_allowed_mime_types() function in wp-includes/functions.php.
     *
     * @return array{type: string, ext: string}|false
     */
    public static function getMimeTypes(string $file_name = ''): array|false
    {
        $mimes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tif|tiff' => 'image/tiff',
            'ico' => 'image/x-icon',
            'asf|asx|wax|wmv|wmx' => 'video/asf',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'txt|asc|c|cc|h' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',
            'mp3|m4a|m4b' => 'audio/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'ogv' => 'video/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/wma',
            'mka' => 'audio/x-matroska',
            'mkv' => 'video/x-matroska',
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'doc|docx' => 'application/msword',
            'pot|pps|ppt|pptx|ppam|pptm|sldm|ppsm|potm' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlsx|xlt|xlw|xlam|xlsb|xlsm|xltm' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docm|dotm' => 'application/vnd.ms-word',
            'pptx|sldx|ppsx|potx' => 'application/vnd.openxmlformats-officedocument.presentationml',
            'xlsx|xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml',
            'docx|dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'exe' => 'application/x-msdownload',
            // openoffice formats
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // wordperfect formats
            'wp|wpd' => 'application/wordperfect',
        ];

        foreach ($mimes as $extensions => $type) {
            if (preg_match('!\.('.$extensions.')$!i', $file_name, $match)) {
                return ['type' => $type, 'ext' => $match[1]];
            }
        }

        return false;
    }

    public function getFileInfo(): array
    {
        return $this->getFileInfo;
    }

    /**
     * The error object, or one of its properties: getError('message').
     */
    public function getError(string|false $property = false): mixed
    {
        if ($property === false) {
            return $this->error;
        }

        return $this->error->$property ?? null;
    }

    /**
     * Replace error messages, e.g. to translate them: [12 => 'Jenis file ditolak.'].
     */
    public function setErrorMessage(array $messages = []): static
    {
        $this->errorMessages = array_replace($this->errorMessages, $messages);

        return $this;
    }
}
