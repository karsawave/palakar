<?php

// No strict_types here on purpose: this class forwards calls coming from
// your code (or from URL segments, which are always strings), so it must not
// change the typing mode the caller is used to.

/**
 * This is the heart of the whole Panada system.
 *
 * A request travels through this file from top to bottom:
 *   1. send()              registers the autoloader and the error handler.
 *   2. __construct()       reads the URI, then the CSRF token is checked.
 *   3. controllerHandler() looks for app/Controllers/{Name}.php,
 *      subControllerHandler() for app/Controllers/{Folder}/{Name}.php,
 *      moduleHandler()     for app/Modules/{Module}/Controllers/{Name}.php.
 *   4. run()               calls the controller method and stores the output.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since version 1.0.0
 */
final class Gear
{
    public static ?Resources\Uri $uri = null;

    private Resources\Uri $uriObj;
    private string $firstUriPath;

    private function __construct(private array $config)
    {
        $this->uriObj = new Resources\Uri();
        $this->uriObj->setDefaultController($config['defaultController']);
        self::$uri = $this->uriObj;
        $this->firstUriPath = ucwords($this->uriObj->getClass());
    }

    /**
     * Entry point, called from public/index.php.
     */
    public static function send(int $errorReporting = E_ALL): void
    {
        error_reporting($errorReporting);
        self::registerAutoloader();

        $config = Resources\Config::main();
        Resources\ErrorHandler::register((bool) ($config['debug'] ?? false));

        try {
            $gear = new self($config);
            Resources\Csrf::verifyRequest($gear->uriObj->pathInfo);
            $gear->controllerHandler();
            $response = new Resources\Response();
        } catch (Resources\HttpException $e) {
            $response = Resources\HttpException::outputError($e);
        } catch (Throwable $e) {
            $response = Resources\ErrorHandler::render($e);
        }

        echo $response->send();
    }

    /**
     * Two autoloaders, in this order:
     *  1. Composer (PSR-4), only when `composer install` has been run.
     *  2. Panada's own loader below. It is always registered, so the framework
     *     keeps working without Composer and catches whatever Composer misses.
     */
    public static function registerAutoloader(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        $composer = (defined('VENDOR') ? VENDOR : dirname(GEAR).'/vendor/').'autoload.php';

        if (is_file($composer)) {
            require_once $composer;
        }

        spl_autoload_register([self::class, 'loader']);
        require_once GEAR.'helpers.php';

        $registered = true;
    }

    /**
     * Panada's built-in autoloader: maps the first part of a namespace to a folder.
     *
     *  Controllers\Home        -> app/Controllers/Home.php
     *  Resources\Session       -> panada/Resources/Session.php
     *  Modules\Blog\Models\Post -> app/Modules/Blog/Models/Post.php
     *
     * Following PSR-4, it never throws: when the file is missing it simply
     * returns, so class_exists() can answer false and other loaders can try.
     */
    public static function loader(string $class): void
    {
        $prefix = strstr($class, '\\', true) ?: $class;

        $folder = match ($prefix) {
            'Models', 'Libraries', 'Controllers' => APP,
            'Resources', 'Drivers' => GEAR,
            'Modules' => Resources\Config::main()['module']['path'] ?? APP,
            default => Resources\Config::main()['namespace'][$prefix] ?? null,
        };

        if ($folder === null) {
            return;
        }

        $file = $folder.str_replace('\\', '/', $class).'.php';

        if (is_file($file)) {
            require $file;
        }
    }

    /**
     * Step 1: is there a controller file named after the first URI segment?
     */
    private function controllerHandler(): void
    {
        if (!is_file(APP.'Controllers/'.$this->firstUriPath.'.php')) {
            $this->subControllerHandler();

            return;
        }

        $instance = $this->instantiate('Controllers\\'.$this->firstUriPath);
        $method = $this->uriObj->getMethod();
        $request = $this->uriObj->getRequests() ?: [];

        if (!$this->isRoutable($instance, $method)) {
            $request = array_slice($this->uriObj->path(), 1);
            $method = $this->config['alias']['method'];

            if (!$this->isRoutable($instance, $method)) {
                throw new Resources\HttpException('Method '.$this->uriObj->getMethod().' does not exists in controller '.$this->firstUriPath);
            }
        }

        $this->run($instance, $method, $request);
    }

    /**
     * Step 2: is the first URI segment a folder inside app/Controllers?
     */
    private function subControllerHandler(): void
    {
        $folder = APP.'Controllers/'.$this->firstUriPath.'/';

        if (!is_dir($folder)) {
            $this->moduleHandler();

            return;
        }

        $controllerClass = $this->classSegment(1) ?? ucwords($this->config['defaultController']);

        if (!is_file($folder.$controllerClass.'.php')) {
            throw new Resources\HttpException('Controller '.$controllerClass.' does not exists in sub-controller '.$this->firstUriPath.'.');
        }

        $instance = $this->instantiate('Controllers\\'.$this->firstUriPath.'\\'.$controllerClass);
        $method = $this->uriObj->path(2) ?: 'index';
        $request = array_slice($this->uriObj->path(), 3);

        if (!$this->isRoutable($instance, $method)) {
            $request = array_slice($this->uriObj->path(), 2);
            $method = $this->config['alias']['method'];

            if (!$this->isRoutable($instance, $method)) {
                throw new Resources\HttpException('Method '.$this->uriObj->path(2).' does not exists in controller /'.$this->firstUriPath.'/'.$controllerClass.'.');
            }
        }

        $this->run($instance, $method, $request);
    }

    /**
     * Step 3: is the first URI segment a module? If not, try the alias controller.
     */
    private function moduleHandler(): void
    {
        $moduleFolder = $this->config['module']['path'].'Modules/'.$this->firstUriPath.'/';
        $alias = $this->config['alias']['controller'] ?? null;

        if (!is_dir($moduleFolder)) {
            if ($alias === null || !is_file(APP.'Controllers/'.$alias['class'].'.php')) {
                throw new Resources\HttpException('Controller, sub-controller or module '.$this->firstUriPath.' does not exists');
            }

            $instance = $this->instantiate('Controllers\\'.$alias['class']);
            $this->run($instance, $alias['method'], $this->uriObj->path());

            return;
        }

        $controllerClass = $this->classSegment(1) ?? ucwords($this->config['defaultController']);
        $method = $this->uriObj->path(2) ?: 'index';
        $request = array_slice($this->uriObj->path(), 3);

        if (!is_file($moduleFolder.'Controllers/'.$controllerClass.'.php')) {
            if ($alias === null || !is_file($moduleFolder.'Controllers/'.$alias['class'].'.php')) {
                throw new Resources\HttpException('Controller '.$controllerClass.' does not exists in module '.$this->firstUriPath);
            }

            $controllerClass = $alias['class'];
            $method = $alias['method'];
            $request = array_slice($this->uriObj->path(), 1);
        }

        $instance = $this->instantiate('Modules\\'.$this->firstUriPath.'\\Controllers\\'.$controllerClass);

        if (!$this->isRoutable($instance, $method)) {
            $request = array_slice($this->uriObj->path(), 2);
            $method = $this->config['alias']['method'];

            if (!$this->isRoutable($instance, $method)) {
                throw new Resources\HttpException('Method '.$method.' does not exists in controller '.$moduleFolder.$controllerClass);
            }
        }

        $this->run($instance, $method, $request);
    }

    /**
     * A URI segment used as a class name must look like a class name.
     */
    private function classSegment(int $index): ?string
    {
        $segment = $this->uriObj->path($index);

        if ($segment === false || $segment === '') {
            return null;
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $segment)) {
            throw new Resources\HttpException('Invalid URL segment');
        }

        return ucwords($segment);
    }

    private function instantiate(string $class): object
    {
        if (!class_exists($class)) {
            throw new Resources\RunException('Class '.$class.' not found. Check the namespace and file name.');
        }

        return new $class();
    }

    /**
     * Only public methods written in YOUR controller can be reached from a URL.
     * Helpers inherited from Resources\Controller (output, redirect, ...) and
     * magic methods (__construct, __get, ...) are never routable.
     */
    private function isRoutable(object $instance, string $method): bool
    {
        if ($method === '' || str_starts_with($method, '__') || !method_exists($instance, $method)) {
            return false;
        }

        $reflection = new ReflectionMethod($instance, $method);

        return $reflection->isPublic()
            && !$reflection->isStatic()
            && $reflection->getDeclaringClass()->getName() !== Resources\Controller::class;
    }

    /**
     * Call the controller's method and keep its output in Response::$body.
     *
     * This file is not in strict mode, so a method declared as show(int $id)
     * receives "15" from /post/show/15 as the integer 15.
     */
    private function run(object $instance, string $method, array $request): void
    {
        $request = array_values($request);

        if (count($request) < (new ReflectionMethod($instance, $method))->getNumberOfRequiredParameters()) {
            throw new Resources\HttpException('Missing URL segment for '.$method.'()');
        }

        // Controllers may `echo` instead of `return`; capture both.
        ob_start();

        try {
            $result = call_user_func_array([$instance, $method], $request);
        } finally {
            $echoed = (string) ob_get_clean();
        }

        Resources\Response::setContent($echoed, $result);
    }
}
