<?php

declare(strict_types=1);

/**
 * Panada Tools Class.
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

class Tools
{
    /**
     * Set the HTTP status code of the response.
     *
     * @param string $text ignored: PHP sends the standard reason phrase (and
     *                     HTTP/2 has none). Kept for old calls.
     */
    public static function setStatusHeader(int $code = 200, string $text = ''): void
    {
        Response::$statusCode = $code;
    }

    /**
     * Random string from a cryptographically secure source.
     */
    public static function getRandomString(int $length = 12, bool $specialChars = true): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if ($specialChars) {
            $chars .= '!@#$%^&*()';
        }

        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $str;
    }

    /**
     * Array (or object) to XML. Thanks to djdykes {@link: http://snipplr.com/view.php?codeview&id=3491}.
     *
     *   Tools::xmlEncode(['user' => ['name' => 'Budi']]);
     *   // <data><user><name>Budi</name></user></data>
     *
     * Numeric keys become <unknownNode_0>, other invalid characters in a
     * name become "_". Values are escaped.
     */
    public static function xmlEncode(array|object $data, string $rootNodeName = 'data', ?\SimpleXMLElement $xml = null): string|false
    {
        $xml ??= new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.self::xmlName($rootNodeName).'/>');

        foreach ($data as $key => $value) {
            $key = self::xmlName(is_numeric($key) ? 'unknownNode_'.$key : (string) $key);

            if (is_array($value) || is_object($value)) {
                self::xmlEncode($value, $rootNodeName, $xml->addChild($key));
            } else {
                // addChild() does not escape "&" by itself.
                $xml->addChild($key, htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
            }
        }

        return $xml->asXML();
    }

    /**
     * A valid XML element name.
     */
    private static function xmlName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9_.-]/', '_', $name);

        return preg_match('/^[A-Za-z_]/', $name) ? $name : '_'.$name;
    }

    /**
     * XML string, file or URL into an object (or an array with $type 'array').
     * Returns false when it is not valid XML.
     */
    public static function xmlDecode(string $xml, string $type = 'object'): \SimpleXMLElement|array|false
    {
        $previous = libxml_use_internal_errors(true);
        $result = str_starts_with(ltrim($xml), '<') ? simplexml_load_string($xml) : simplexml_load_file($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($result === false) {
            return false;
        }

        return $type === 'array' ? (array) self::objectToArray($result) : $result;
    }

    /**
     * Convert an object into array.
     */
    public static function objectToArray(mixed $object): mixed
    {
        return json_decode((string) json_encode($object), true);
    }

    /**
     * Convert an array into object.
     */
    public static function arrayToObject(mixed $var): mixed
    {
        return json_decode((string) json_encode($var));
    }
}
