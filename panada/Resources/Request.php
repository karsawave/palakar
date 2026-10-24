<?php

declare(strict_types=1);

/**
 * Panada Request/input Handler.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

class Request
{
    /**
     * Handler for HTTP GET request.
     *
     * @param int $filterType One of PHP's FILTER_* constants, e.g. FILTER_VALIDATE_INT
     */
    public function get(?string $key = null, int $filterType = FILTER_DEFAULT, int|array $flags = 0): mixed
    {
        return $this->read($_GET, INPUT_GET, $key, $filterType, $flags);
    }

    /**
     * Handler for HTTP POST request.
     */
    public function post(?string $key = null, int $filterType = FILTER_DEFAULT, int|array $flags = 0): mixed
    {
        return $this->read($_POST, INPUT_POST, $key, $filterType, $flags);
    }

    /**
     * Handler for cookie values.
     */
    public function cookie(?string $key = null, int $filterType = FILTER_DEFAULT, int|array $flags = 0): mixed
    {
        return $this->read($_COOKIE, INPUT_COOKIE, $key, $filterType, $flags);
    }

    private function read(array $source, int $type, ?string $key, int $filterType, int|array $flags): mixed
    {
        $filterType = $filterType ?: FILTER_DEFAULT;

        if ($key === null || $key === '') {
            return filter_var_array($source, $filterType);
        }

        if (!isset($source[$key])) {
            return false;
        }

        if ($filterType === FILTER_DEFAULT && $flags === 0) {
            return $source[$key];
        }

        return filter_var($source[$key], $filterType, $flags);
    }

    /**
     * HTTP method in upper case: GET, POST, PUT, ...
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Is the visitor on HTTPS?
     *
     * X-Forwarded-Proto is only trusted when 'trustProxyHeaders' is true in
     * app/config/main.php, because any visitor can send that header.
     */
    public static function isSecure(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));

        if ($https === 'on' || $https === '1') {
            return true;
        }

        if ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443') {
            return true;
        }

        if (Config::main()['trustProxyHeaders'] ?? false) {
            return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
        }

        return false;
    }

    /**
     * Strip any html tags and attributes defined by user.
     *
     * Note: for showing user input in a view, escaping with e() is simpler and
     * safer than stripping tags. Use this only when some HTML must survive.
     *
     * @param string|array|null $allowtags       e.g. '<b><i>' or ['b', 'i']
     * @param string|array|null $allowattributes e.g. 'href,title'
     */
    public function stripTagsAttributes(string $str, string|array|null $allowtags = null, string|array|null $allowattributes = null): string
    {
        /*
         * ID:  Jika string < diikuti dengan tanda non-alpha selain tanda ?, maka ubah menjadi &lt; (htmlentities)
         *      Ini berguna jika string yang diinput berupa emotion code seperpti <*_*> atau tanda panah <=
         */
        $str = preg_replace(['/<\*/', '/<=/'], '&lt;\\1', $str);

        /*
         * ID:  Hapus semua tag html dan php yang tidak didefinisikan dari input string.
         */
        $str = strip_tags($str, $allowtags);

        /*
         * ID:  Kembalikan string &lt; menjadi <
         */
        $str = str_replace('&lt;', '<', $str);

        if ($allowattributes === null) {
            return $str;
        }

        $allowattributes = is_array($allowattributes) ? $allowattributes : explode(',', $allowattributes);
        $allowattributes = array_map(fn ($attr) => preg_quote(trim($attr), '/'), $allowattributes);
        $notAllowed = $allowattributes ? '(?<!'.implode(')(?<!', $allowattributes).')' : '';

        // Remove every attribute that is not in the allow list.
        // See original idea at http://php.net/manual/en/function.strip-tags.php#91498
        return preg_replace_callback(
            '/<[^>]*>/i',
            fn (array $matches) => preg_replace('/ [^ =]*'.$notAllowed.'=("[^"]*"|\'[^\']*\')/i', '', $matches[0]),
            $str
        );
    }
}
