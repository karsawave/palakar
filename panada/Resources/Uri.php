<?php

declare(strict_types=1);

/**
 * Handler for request and mapped into, controller, metohd and requests.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 *
 * @since	version 0.1
 */

namespace Resources;

final class Uri
{
    private array $pathUri = [];
    public string $baseUri = '';
    public string $basePath = '';
    public string $pathInfo = '';
    public string $defaultController = 'Home';
    public string $requestScheme = 'http://';
    public string $frontController = '';
    public static string $staticDefaultController = 'Home';

    /**
     * Class constructor.
     *
     * Difine the SAPI mode, cli or web/http
     */
    public function __construct()
    {
        $this->defaultController = self::$staticDefaultController;

        if (PHP_SAPI === 'cli') {
            $this->pathUri = array_slice($_SERVER['argv'] ?? [], 1);

            return;
        }

        $this->requestScheme = $this->isHttps() ? 'https://' : 'http://';

        $scriptPath = explode('/', $_SERVER['SCRIPT_FILENAME']);
        $this->frontController = end($scriptPath);

        $docRoot = str_replace('/'.$this->frontController, '', $_SERVER['DOCUMENT_ROOT']);
        $scriptFile = str_replace('/'.$this->frontController, '', $_SERVER['SCRIPT_FILENAME']);
        $requestURI = str_replace('/'.$this->frontController, '', $_SERVER['REQUEST_URI'] ?? '/');
        $this->basePath = str_replace($docRoot, '', $scriptFile);

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->baseUri = $this->requestScheme.$host.$this->basePath.'/';

        if ($this->basePath !== '' && str_starts_with($requestURI, $this->basePath)) {
            $requestURI = substr($requestURI, strlen($this->basePath));
        }

        $this->pathInfo = trim((string) strtok($requestURI, '?'), '/');
        $this->pathUri = explode('/', $this->pathInfo);
    }

    /**
     * Does this site use https?
     */
    public function isHttps(): bool
    {
        return Request::isSecure();
    }

    /**
     * Clean the 'standard' model query.
     */
    public function removeQuery(string $path): string
    {
        return explode('?', $path)[0];
    }

    /**
     * Get one URI segment (zero based), or all of them when $segment is false.
     */
    public function path(int|false $segment = false): array|string|false
    {
        if ($segment !== false) {
            return (isset($this->pathUri[$segment]) && $this->pathUri[$segment] !== $this->frontController) ? $this->pathUri[$segment] : false;
        }

        return $this->pathUri;
    }

    /**
     * Get class name from the url.
     */
    public function getClass(): string
    {
        if ($uriString = $this->path(0)) {
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $uriString)) {
                return $uriString;
            }

            throw new HttpException('Invalid controller name: '.$uriString);
        }

        return $this->defaultController;
    }

    /**
     * Get method name from the url.
     */
    public function getMethod(?string $default = 'index'): ?string
    {
        $uriString = $this->path(1);

        if (is_string($uriString) && $uriString !== '') {
            return $this->stripUriString($uriString) ? $uriString : '';
        }

        return $default;
    }

    /**
     * Get "GET" request from the url.
     */
    public function getRequests(int $segment = 2): array|false
    {
        if ($this->path($segment) !== false) {
            return array_slice($this->path(), $segment);
        }

        return false;
    }

    /**
     * Cleaner for class and method name.
     */
    public function stripUriString(string $uri): bool
    {
        return !preg_match('/[^a-zA-Z0-9_.-]/', $uri);
    }

    /**
     * Setter for default controller.
     */
    public function setDefaultController(string $defaultController): void
    {
        $this->defaultController = self::$staticDefaultController = $defaultController;
    }

    /**
     * Getter for default controller.
     */
    public function getDefaultController(): string
    {
        return $this->defaultController;
    }

    /**
     * Getter for baseUri.
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }
}
