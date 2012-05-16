<?php

namespace Symfony\Component\HttpKernel\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderBag;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This code is partially based on the Rack-Cache library by Ryan Tomayko,
 * which is released under the MIT license.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Store implements all the logic for storing cache metadata (Request and Response headers).
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Store
{
    protected $root;
    protected $keyCache;
    protected $locks;

    /**
     * Constructor.
     *
     * @param string $root The path to the cache directory
     */
    public function __construct($root)
    {
        $this->root = $root;
        if (!is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }
        $this->keyCache = new \SplObjectStorage();
        $this->locks = array();
    }

    public function __destruct()
    {
        // unlock everything
        foreach ($this->locks as $lock) {
            @unlink($lock);
        }

        $error = error_get_last();
        if (1 === $error['type'] && false === headers_sent()) {
            // send a 503
            header('HTTP/1.0 503 Service Unavailable');
            header('Retry-After: 10');
            echo '503 Service Unavailable';
        }
    }

    /**
     * Locks the cache for a given Request.
     *
     * @param Request $request A Request instance
     *
     * @return Boolean|string true if the lock is acquired, the path to the current lock otherwise
     */
    public function lock(Request $request)
    {
        if (false !== $lock = @fopen($path = $this->getPath($this->getCacheKey($request).'.lck'), 'x')) {
            fclose($lock);

            $this->locks[] = $path;

            return true;
        } else {
            return $path;
        }
    }

    /**
     * Releases the lock for the given Request.
     *
     * @param Request $request A Request instance
     */
    public function unlock(Request $request)
    {
        return @unlink($this->getPath($this->getCacheKey($request).'.lck'));
    }

    /**
     * Locates a cached Response for the Request provided.
     *
     * @param Request $request A Request instance
     *
     * @return Response|null A Response instance, or null if no cache entry was found
     */
    public function lookup(Request $request)
    {
        $key = $this->getCacheKey($request);

        if (!$entries = $this->getMetadata($key)) {
            return null;
        }

        // find a cached entry that matches the request.
        $match = null;
        foreach ($entries as $entry) {
            if ($this->requestsMatch(isset($entry[1]['vary']) ? $entry[1]['vary'][0] : '', $request->headers->all(), $entry[0]))
            {
                $match = $entry;

                break;
            }
        }

        if (null === $match) {
            return null;
        }

        list($req, $headers) = $match;
        if (file_exists($body = $this->getPath($headers['x-content-digest'][0]))) {
            return $this->restoreResponse($headers, $body);
        } else {
            // TODO the metaStore referenced an entity that doesn't exist in
            // the entityStore. We definitely want to return nil but we should
            // also purge the entry from the meta-store when this is detected.
            return null;
        }
    }

    /**
     * Writes a cache entry to the store for the given Request and Response.
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     *
     * @return string The key under which the response is stored
     */
    public function write(Request $request, Response $response)
    {
        $key = $this->getCacheKey($request);
        $storedEnv = $this->persistRequest($request);

        // write the response body to the entity store if this is the original response
        if (!$response->headers->has('X-Content-Digest')) {
            $digest = 'en'.sha1($response->getContent());

            if (false === $this->save($digest, $response->getContent())) {
                throw new \RuntimeException(sprintf('Unable to store the entity (%s).', $e->getMessage()));
            }

            $response->headers->set('X-Content-Digest', $digest);

            if (!$response->headers->has('Transfer-Encoding')) {
                $response->headers->set('Content-Length', strlen($response->getContent()));
            }
        }

        // read existing cache entries, remove non-varying, and add this one to the list
        $entries = array();
        $vary = $response->headers->get('vary');
        foreach ($this->getMetadata($key) as $entry) {
            if (!isset($entry[1]['vary'])) {
                $entry[1]['vary'] = array('');
            }

            if ($vary != $entry[1]['vary'][0] || !$this->requestsMatch($vary, $entry[0], $storedEnv)) {
                $entries[] = $entry;
            }
        }

        $headers = $this->persistResponse($response);
        unset($headers['age']);

        array_unshift($entries, array($storedEnv, $headers));

        if (false === $this->save($key, serialize($entries))) {
            throw new \RuntimeException(sprintf('Unable to store the metadata (%s).', $e->getMessage()));
        }

        return $key;
    }

    /**
     * Invalidates all cache entries that match the request.
     *
     * @param Request $request A Request instance
     */
    public function invalidate(Request $request)
    {
        $modified = false;
        $key = $this->getCacheKey($request);

        $entries = array();
        foreach ($this->getMetadata($key) as $entry) {
            $response = $this->restoreResponse($entry[1]);
            if ($response->isFresh()) {
                $response->expire();
                $modified = true;
                $entries[] = array($entry[0], $this->persistResponse($response));
            } else {
                $entries[] = $entry;
            }
        }

        if ($modified) {
            if (false === $this->save($key, serialize($entries))) {
                throw new \RuntimeException('Unable to store the metadata.');
            }
        }

        // As per the RFC, invalidate Location and Content-Location URLs if present
        foreach (array('Location', 'Content-Location') as $header) {
            if ($uri = $request->headers->get($header)) {
                $subRequest = Request::create($uri, 'get', array(), array(), array(), $request->server->all());

                $this->invalidate($subRequest);
            }
        }
    }

    /**
     * Determines whether two Request HTTP header sets are non-varying based on
     * the vary response header value provided.
     *
     * @param string $vary A Response vary header
     * @param array  $env1 A Request HTTP header array
     * @param array  $env2 A Request HTTP header array
     *
     * @return Boolean true if the the two environments match, false otherwise
     */
    public function requestsMatch($vary, $env1, $env2)
    {
        if (empty($vary)) {
            return true;
        }

        foreach (preg_split('/[\s,]+/', $vary) as $header) {
            $key = HeaderBag::normalizeHeaderName($header);
            $v1 = isset($env1[$key]) ? $env1[$key] : null;
            $v2 = isset($env2[$key]) ? $env2[$key] : null;
            if ($v1 !== $v2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets all data associated with the given key.
     *
     * Use this method only if you know what you are doing.
     *
     * @param string $key The store key
     *
     * @return array An array of data associated with the key
     */
    public function getMetadata($key)
    {
        if (false === $entries = $this->load($key)) {
            return array();
        }

        return unserialize($entries);
    }

    /**
     * Purges data for the given URL.
     *
     * @param string $url A URL
     */
    public function purge($url)
    {
        if (file_exists($path = $this->getPath($this->getCacheKey(Request::create($url))))) {
            unlink($path);
        }
    }

    /**
     * Loads data for the given key.
     *
     * Don't use this method directly, use lookup() instead
     *
     * @param string $key  The store key
     *
     * @return string The data associated with the key
     */
    public function load($key)
    {
        $path = $this->getPath($key);

        return file_exists($path) ? file_get_contents($path) : false;
    }

    /**
     * Save data for the given key.
     *
     * Don't use this method directly, use write() instead
     *
     * @param string $key  The store key
     * @param string $data The data to store
     */
    public function save($key, $data)
    {
        $path = $this->getPath($key);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $tmpFile = tempnam(dirname($path), basename($path));
        if (false === $fp = @fopen($tmpFile, 'wb')) {
            return false;
        }
        @fwrite($fp, $data);
        @fclose($fp);

        if ($data != file_get_contents($tmpFile)) {
            return false;
        }

        if (false === @rename($tmpFile, $path)) {
            return false;
        }

        chmod($path, 0644);
    }

    public function getPath($key)
    {
        return $this->root.DIRECTORY_SEPARATOR.substr($key, 0, 2).DIRECTORY_SEPARATOR.substr($key, 2, 4).DIRECTORY_SEPARATOR.substr($key, 4);
    }

    /**
     * Returns a cache key for the given Request.
     *
     * @param Request $request A Request instance
     *
     * @return string A key for the given Request
     */
    public function getCacheKey(Request $request)
    {
        if (isset($this->keyCache[$request])) {
            return $this->keyCache[$request];
        }

        return $this->keyCache[$request] = 'md'.sha1($request->getUri());
    }

    /**
     * Persists the Request HTTP headers.
     *
     * @param Request $request A Request instance
     *
     * @return array An array of HTTP headers
     */
    protected function persistRequest(Request $request)
    {
        return $request->headers->all();
    }

    /**
     * Persists the Response HTTP headers.
     *
     * @param Response $response A Response instance
     *
     * @return array An array of HTTP headers
     */
    protected function persistResponse(Response $response)
    {
        $headers = $response->headers->all();
        $headers['X-Status'] = array($response->getStatusCode());

        return $headers;
    }

    /**
     * Restores a Response from the HTTP headers and body.
     *
     * @param array  $headers An array of HTTP headers for the Response
     * @param string $body    The Response body
     */
    protected function restoreResponse($headers, $body = null)
    {
        $status = $headers['X-Status'][0];
        unset($headers['X-Status']);

        if (null !== $body) {
            $headers['X-Body-File'] = array($body);
        }

        return new Response($body, $status, $headers);
    }
}
