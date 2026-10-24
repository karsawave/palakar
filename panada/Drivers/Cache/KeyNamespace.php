<?php

declare(strict_types=1);

namespace Drivers\Cache;

/**
 * Namespaces drop many keys at once:
 *
 *   $cache->setValue('item_15', $item, 0, 'products');
 *   $cache->deleteValue('products');      // every 'products' value is gone
 *
 * The namespace key holds a random version that prefixes its keys. Deleting
 * it gives the namespace a new version, so the old values are never read
 * again and simply expire in the cache server.
 */
trait KeyNamespace
{
    private function keyToNamespace(string $key, string|false $namespace = false): string
    {
        if ($namespace === false) {
            return $key;
        }

        $version = $this->getValue($namespace);

        if ($version === false) {
            // Random, not time(): a namespace dropped and used again within
            // the same second must not get its old values back.
            $version = random_int(1, PHP_INT_MAX);
            $this->setValue($namespace, $version);
        }

        return $version.'_'.$key;
    }
}
