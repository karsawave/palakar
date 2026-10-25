<?php

declare(strict_types=1);

/**
 * Handler for controller process.
 *
 * @author  Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link    http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since   version 1.0.0
 */

namespace Resources;

class Controller
{
    use Loadable;

    public ?Uri $uri;
    public array $config = [];
    protected array $configMain;

    /** @var array{namespaceArray: string[], namespaceString: string} */
    private array $childClass;

    /** Data shared with nested views, e.g. $this->output('header') inside a view. */
    private array $viewCache = [];

    public function __construct()
    {
        $this->childClass = [
            'namespaceArray' => explode('\\', static::class),
            'namespaceString' => static::class,
        ];

        $this->configMain = Config::main();
        $this->uri = \Gear::$uri ?? null;
    }

    /**
     * Render an error view from app/views (used by the error handlers).
     */
    public static function outputError(string $file, array $data = []): string
    {
        return (new self())->output($file, $data);
    }

    /**
     * Render a view file and return it as a string.
     *
     *   return $this->output('home', ['title' => 'Hello']);
     *
     * In the view, print user data with e(): <?= e($title) ?>
     */
    public function output(string $panadaViewFile, array $data = []): string
    {
        $panadaFilePath = APP . 'views/' . $panadaViewFile . '.php';

        if ($this->childClass['namespaceArray'][0] === 'Modules') {
            $panadaFilePath = $this->configMain['module']['path'] . 'Modules/' . $this->childClass['namespaceArray'][1] . '/views/' . $panadaViewFile . '.php';
        }

        if (!is_file($panadaFilePath)) {
            throw new RunException('View file ' . $panadaFilePath . ' does not exist');
        }

        if ($data !== []) {
            $this->viewCache = $data;
        }

        return (function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();

            try {
                include $__file;
            } catch (\Throwable $e) {
                ob_end_clean();

                throw $e;
            }

            return (string) ob_get_clean();
        })($panadaFilePath, $this->viewCache);
    }

    public function outputJSON(mixed $data): string
    {
        return $this->outputTransporter($data, 'json');
    }

    public function outputXML(mixed $data): string
    {
        return $this->outputTransporter($data, 'xml');
    }

    private function outputTransporter(mixed $data, string $type): string
    {
        return (string) (new Rest())->wrapResponseOutput($data, $type);
    }

    public function location(string $location = ''): string
    {
        return $this->uri?->getBaseUri() . ($this->configMain['indexFile'] ?? '') . $location;
    }

    public function redirect(string $location = '', int $status = 302): string
    {
        if (!str_starts_with($location, 'http')) {
            $location = $this->location() . $location;
        }

        Response::setHeader('Location', $location, $status);

        return '<html><head><meta http-equiv="refresh" content="0; url=' . e($location) . '" /></head></html>';
    }
}
