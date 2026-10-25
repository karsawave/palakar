<?php

declare(strict_types=1);

namespace Tests\Http;

use Resources\Upload;

/**
 * Real multipart uploads to the fixture app (Api::upload), because
 * move_uploaded_file() only accepts files PHP received over HTTP.
 */
final class UploadTest extends HttpTestCase
{
    private static string $dir;

    public static function setUpBeforeClass(): void
    {
        self::$dir = str_replace('\\', '/', sys_get_temp_dir()).'/panada-upload-test';
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::remove(self::$dir);
        mkdir(self::$dir.'/files', 0777, true);
        mkdir(self::$dir.'/src');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::remove(self::$dir);
    }

    private static function remove(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }

    private function upload(string $name, string $content = 'halo', array $options = []): array
    {
        $source = self::$dir.'/src/'.md5($name.$content);
        file_put_contents($source, $content);
        $data = ['file' => new \CURLFile($source, 'application/octet-stream', $name)];

        // Nested options as multipart fields: options[editImage][0][resizeWidth] ...
        foreach (explode('&', http_build_query(['options' => $options])) as $pair) {
            if ($pair !== '') {
                [$field, $value] = explode('=', $pair);
                $data[urldecode($field)] = urldecode($value);
            }
        }

        $url = self::server(dirname(__DIR__).'/Fixtures/public', ['PANADA_UPLOAD_DIR' => self::$dir.'/files']);
        $response = $this->request($url.'/api/upload', 'POST', $data);
        $this->assertSame(200, $response['status'], $response['body']);

        return json_decode($response['body'], true);
    }

    private function storedFiles(): array
    {
        return array_values(array_diff(scandir(self::$dir.'/files'), ['.', '..']));
    }

    public function testUploadStoresTheFileWithItsInfo(): void
    {
        $result = $this->upload('hello world.txt');

        $this->assertTrue($result['ok']);
        $this->assertNull($result['error']);
        $this->assertSame(
            ['extension' => 'txt', 'name' => 'hello_world.txt', 'folder' => self::$dir.'/files', 'mime' => 'text/plain', 'size' => 4],
            $result['info']
        );
        $this->assertSame('halo', file_get_contents(self::$dir.'/files/hello_world.txt'));
    }

    public function testFilesTheServerCouldRunAreAlwaysRefused(): void
    {
        $permitted = ['permittedFileType' => 'php|phtml|php5|phar|jpg|htaccess|ini'];

        foreach (['shell.php', 'SHELL.PHTML', 'x.php5', 'tools.phar', 'image.php.jpg', '.htaccess', '.user.ini'] as $name) {
            $result = $this->upload($name, '<?php echo 1;', $permitted);

            $this->assertFalse($result['ok'], $name);
            $this->assertSame(12, $result['error']['code'], $name);
        }

        $this->assertSame([], $this->storedFiles());
    }

    public function testPermittedTypesAndMaximumSize(): void
    {
        $this->assertSame(12, $this->upload('notes.txt', 'x', ['permittedFileType' => 'jpg|png'])['error']['code']);
        $this->assertSame(13, $this->upload('notes.txt', 'too big', ['maximumSize' => 3])['error']['code']);
        $this->assertTrue($this->upload('photo.PNG', 'x', ['permittedFileType' => 'jpg|png'])['ok']);
    }

    public function testUnknownOrMissingExtension(): void
    {
        $this->assertSame('application/octet-stream', $this->upload('data.xyz', "\x00\x01\x02\xff")['info']['mime']);
        $this->assertSame(['extension' => '', 'name' => 'README'], array_slice($this->upload('README')['info'], 0, 2));
    }

    public function testMimeComesFromTheContentNotTheName(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        $this->assertSame('image/png', $this->upload('photo.txt', $png)['info']['mime']);
        $this->assertSame('text/plain', $this->upload('fake.png', 'halo')['info']['mime']);
    }

    public function testNamesCannotLeaveTheFolder(): void
    {
        $result = $this->upload('a.txt', 'x', ['setFileName' => '../../evil']);

        $this->assertSame('evil.txt', $result['info']['name']);
        $this->assertSame(['evil.txt'], $this->storedFiles());
    }

    public function testAutoRenameAndAutoCreateFolder(): void
    {
        $first = $this->upload('a.txt', 'x', ['autoRename' => 1, 'autoCreateFolder' => 1]);
        $second = $this->upload('a.txt', 'y', ['autoRename' => 1, 'autoCreateFolder' => 1]);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{16}\.txt$/', $first['info']['name']);
        $this->assertNotSame($first['info']['name'], $second['info']['name']);
        $this->assertSame(self::$dir.'/files/'.date('Y/m'), $first['info']['folder']);
        $this->assertFileExists($first['info']['folder'].'/'.$first['info']['name']);
    }

    public function testImageIsEditedAfterUpload(): void
    {
        ob_start();
        imagepng(imagecreatetruecolor(100, 50));
        $png = (string) ob_get_clean();

        $result = $this->upload('photo.png', $png, ['editImage' => [
            ['resizeWidth' => 50],
            ['editType' => 'crop', 'cropWidth' => 10, 'cropHeight' => 10, 'newFileName' => 'thumb'],
        ]]);

        $this->assertTrue($result['ok'], (string) ($result['error']['message'] ?? ''));
        $this->assertSame([50, 25], array_slice(getimagesize(self::$dir.'/files/photo.png'), 0, 2));
        $this->assertSame([10, 10], array_slice(getimagesize(self::$dir.'/files/thumb.png'), 0, 2));
    }

    public function testErrors(): void
    {
        $upload = new Upload();
        $this->assertNull($upload->getError());
        $this->assertNull($upload->getError('message'));

        $this->assertFalse($upload->now(['name' => 'a.txt']));
        $this->assertSame(2, $upload->getError('code'), 'No folder');

        $upload->setOption('folderLocation', self::$dir.'/files');
        $this->assertFalse($upload->now(['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0]));
        $this->assertSame('No file was uploaded.', $upload->getError('message'));
    }
}
