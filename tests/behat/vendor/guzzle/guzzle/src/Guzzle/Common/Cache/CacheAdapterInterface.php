<?php

namespace Guzzle\Common\Cache;

/**
 * Interface for cache adapters.
 *
 * Cache adapters allow Guzzle to utilze various frameworks for caching HTTP
 * responses.
 *
 * @link http://www.doctrine-project.org/ Inspired by Doctrine 2
 */
interface CacheAdapterInterface
{
    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id      cache id The cache id of the entry to check for.
     * @param array  $options Array of cache adapter options
     *
     * @return bool Returns TRUE if a cache entry exists for the given cache
     *              id, FALSE otherwise.
     */
    function contains($id, array $options = null);

    /**
     * Deletes a cache entry.
     *
     * @param string $id      cache id
     * @param array  $options Array of cache adapter options
     *
     * @return bool TRUE on success, FALSE on failure
     */
    function delete($id, array $options = null);

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id      cache id The id of the cache entry to fetch.
     * @param array  $options Array of cache adapter options
     *
     * @return string The cached data or FALSE, if no cache entry exists
     *                for the given id.
     */
    function fetch($id, array $options = null);

    /**
     * Puts data into the cache.
     *
     * @param string $id       The cache id.
     * @param string $data     The cache entry/data.
     * @param int    $lifeTime The lifetime. If != false, sets a specific
     *                         lifetime for this cache entry. Set to null
     *                         to give an infinite lifetime.
     * @param array  $options  Array of cache adapter options
     *
     * @return bool TRUE if the entry was successfully stored in the cache,
     *              FALSE otherwise.
     */
    function save($id, $data, $lifeTime = false, array $options = null);
}
