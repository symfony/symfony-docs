<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\SessionStorage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Request represents an HTTP request.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Request
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $attributes;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $request;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $query;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $server;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $files;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $cookies;

    /**
     * @var \Symfony\Component\HttpFoundation\HeaderBag
     */
    public $headers;

    protected $languages;
    protected $charsets;
    protected $acceptableContentTypes;
    protected $pathInfo;
    protected $requestUri;
    protected $baseUrl;
    protected $basePath;
    protected $method;
    protected $format;
    protected $session;

    static protected $formats;

    /**
     * Constructor.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     */
    public function __construct(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server);
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     */
    public function initialize(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        $this->request = new ParameterBag(null !== $request ? $request : $_POST);
        $this->query = new ParameterBag(null !== $query ? $query : $_GET);
        $this->attributes = new ParameterBag(null !== $attributes ? $attributes : array());
        $this->cookies = new ParameterBag(null !== $cookies ? $cookies : $_COOKIE);
        $this->files = new ParameterBag($this->convertFileInformation(null !== $files ? $files : $_FILES));
        $this->server = new ParameterBag(null !== $server ? $server : $_SERVER);
        $this->headers = new HeaderBag($this->initializeHeaders(), 'request');

        $this->languages = null;
        $this->charsets = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * @param string $uri        The URI
     * @param string $method     The HTTP method
     * @param array  $parameters The request (GET) or query (POST) parameters
     * @param array  $cookies    The request cookies ($_COOKIE)
     * @param array  $files      The request files ($_FILES)
     * @param array  $server     The server parameters ($_SERVER)
     *
     * @return Request A Request instance
     */
    static public function create($uri, $method = 'get', $parameters = array(), $cookies = array(), $files = array(), $server = array())
    {
        $defaults = array(
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Symfony/2.X',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
        );

        if (in_array(strtolower($method), array('post', 'put', 'delete'))) {
            $request = $parameters;
            $query = array();
            $defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        } else {
            $request = array();
            $query = $parameters;
            if (false !== $pos = strpos($uri, '?')) {
                $qs = substr($uri, $pos + 1);
                parse_str($qs, $params);

                $query = array_merge($params, $query);
            }
        }

        $queryString = false !== ($pos = strpos($uri, '?')) ? html_entity_decode(substr($uri, $pos + 1)) : '';
        parse_str($queryString, $qs);
        if (is_array($qs)) {
            $query = array_replace($qs, $query);
        }

        $server = array_replace($defaults, $server, array(
            'REQUEST_METHOD'       => strtoupper($method),
            'PATH_INFO'            => '',
            'REQUEST_URI'          => $uri,
            'QUERY_STRING'         => $queryString,
        ));

        return new self($query, $request, array(), $cookies, $files, $server);
    }

    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        $dup = clone $this;
        $dup->initialize(
            null !== $query ? $query : $this->query->all(),
            null !== $request ? $request : $this->request->all(),
            null !== $attributes ? $attributes : $this->attributes->all(),
            null !== $cookies ? $cookies : $this->cookies->all(),
            null !== $files ? $files : $this->files->all(),
            null !== $server ? $server : $this->server->all()
        );

        return $dup;
    }

    public function __clone()
    {
        $this->query      = clone $this->query;
        $this->request    = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies    = clone $this->cookies;
        $this->files      = clone $this->files;
        $this->server     = clone $this->server;
        $this->headers    = clone $this->headers;
    }

    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIES, and $_FILES.
     */
    public function overrideGlobals()
    {
        $_GET = $this->query->all();
        $_POST = $this->request->all();
        $_SERVER = $this->server->all();
        $_COOKIES = $this->cookies->all();
        // FIXME: populate $_FILES

        foreach ($this->headers->all() as $key => $value) {
            $_SERVER['HTTP_'.strtoupper(str_replace('-', '_', $key))] = implode(', ', $value);
        }

        // FIXME: should read variables_order and request_order
        // to know which globals to merge and in which order
        $_REQUEST = array_merge($_GET, $_POST);
    }

    // Order of precedence: GET, PATH, POST, COOKIE
    // Avoid using this method in controllers:
    //  * slow
    //  * prefer to get from a "named" source
    // This method is mainly useful for libraries that want to provide some flexibility
    public function get($key, $default = null)
    {
        return $this->query->get($key, $this->attributes->get($key, $this->request->get($key, $default)));
    }

    public function getSession()
    {
        if (null === $this->session) {
            $this->session = new Session(new NativeSessionStorage());
        }
        $this->session->start();

        return $this->session;
    }

    public function hasSession()
    {
        return $this->cookies->has(session_name());
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Returns the client IP address.
     *
     * @param  Boolean $proxy Whether the current request has been made behind a proxy or not
     *
     * @return string The client IP address
     */
    public function getClientIp($proxy = false)
    {
        if ($proxy) {
            if ($this->server->has('HTTP_CLIENT_IP')) {
                return $this->server->get('HTTP_CLIENT_IP');
            } elseif ($this->server->has('HTTP_X_FORWARDED_FOR')) {
                return $this->server->get('HTTP_X_FORWARDED_FOR');
            }
        }

        return $this->server->get('REMOTE_ADDR');
    }

    /**
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    public function getScheme()
    {
        return ($this->server->get('HTTPS') == 'on') ? 'https' : 'http';
    }

    public function getPort()
    {
        return $this->server->get('SERVER_PORT');
    }

    public function getHttpHost()
    {
        $host = $this->headers->get('HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->server->get('SERVER_NAME');
        $port   = $this->server->get('SERVER_PORT');

        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return $name;
        } else {
            return $name.':'.$port;
        }
    }

    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Generates a normalized URI for the Request.
     *
     * @return string A normalized URI for the Request
     *
     * @see getQueryString()
     */
    public function getUri()
    {
        $qs = $this->getQueryString();
        if (null !== $qs) {
            $qs = '?'.$qs;
        }

        return $this->getScheme().'://'.$this->getHost().':'.$this->getPort().$this->getScriptName().$this->getPathInfo().$qs;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string A normalized query string for the Request
     */
    public function getQueryString()
    {
        if (!$qs = $this->server->get('QUERY_STRING')) {
            return null;
        }

        $parts = array();
        $order = array();

        foreach (explode('&', $qs) as $segment) {
            if (false === strpos($segment, '=')) {
                $parts[] = $segment;
                $order[] = $segment;
            } else {
                $tmp = explode('=', urldecode($segment), 2);
                $parts[] = urlencode($tmp[0]).'='.urlencode($tmp[1]);
                $order[] = $tmp[0];
            }
        }
        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    public function isSecure()
    {
        return (
            (strtolower($this->server->get('HTTPS')) == 'on' || $this->server->get('HTTPS') == 1)
            ||
            (strtolower($this->headers->get('SSL_HTTPS')) == 'on' || $this->headers->get('SSL_HTTPS') == 1)
            ||
            (strtolower($this->headers->get('X_FORWARDED_PROTO')) == 'https')
        );
    }

    /**
     * Returns the host name.
     *
     * @return string
     */
    public function getHost()
    {
        if ($host = $this->headers->get('X_FORWARDED_HOST')) {
            $elements = explode(',', $host);

            return trim($elements[count($elements) - 1]);
        } else {
            return $this->headers->get('HOST', $this->server->get('SERVER_NAME', $this->server->get('SERVER_ADDR', '')));
        }
    }

    public function setMethod($method)
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', 'GET');
    }

    /**
     * Gets the request method.
     *
     * @return string The request method
     */
    public function getMethod()
    {
        if (null === $this->method) {
            switch ($this->server->get('REQUEST_METHOD', 'GET')) {
                case 'POST':
                    $this->method = strtoupper($this->request->get('_method', 'POST'));
                    break;

                case 'PUT':
                    $this->method = 'PUT';
                    break;

                case 'DELETE':
                    $this->method = 'DELETE';
                    break;

                case 'HEAD':
                    $this->method = 'HEAD';
                    break;

                default:
                    $this->method = 'GET';
            }
        }

        return $this->method;
    }

    /**
     * Gets the mime type associated with the format.
     *
     * @param  string $format  The format
     *
     * @return string The associated mime type (null if not found)
     */
    public function getMimeType($format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    /**
     * Gets the format associated with the mime type.
     *
     * @param  string $mimeType  The associated mime type
     *
     * @return string The format (null if not found)
     */
    public function getFormat($mimeType)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Associates a format with mime types.
     *
     * @param string       $format     The format
     * @param string|array $mimeTypes  The associated mime types (the preferred one must be the first as it will be used as the content type)
     */
    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : array($mimeTypes);
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request parameter
     *  * null
     *
     * @return string The request format
     */
    public function getRequestFormat()
    {
        if (null === $this->format) {
            $this->format = $this->get('_format', 'html');
        }

        return $this->format;
    }

    public function setRequestFormat($format)
    {
        $this->format = $format;
    }

    public function isMethodSafe()
    {
        return in_array(strtolower($this->getMethod()), array('get', 'head'));
    }

    public function getETags()
    {
        return preg_split('/\s*,\s*/', $this->headers->get('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
    }

    public function isNoCache()
    {
        return $this->headers->getCacheControl()->isNoCache() || 'no-cache' == $this->headers->get('Pragma');
    }

    /**
     * Returns the preferred language.
     *
     * @param  array  $locales  An array of ordered available locales
     *
     * @return string The preferred locale
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();

        if (null === $locales) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }

        if (!$preferredLanguages) {
            return $locales[0];
        }

        $preferredLanguages = array_values(array_intersect($preferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }

        $languages = $this->splitHttpAcceptHeader($this->headers->get('Accept-Language'));
        foreach ($languages as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_'.strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     */
    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }

        return $this->charsets = $this->splitHttpAcceptHeader($this->headers->get('Accept-Charset'));
    }

    /**
     * Gets a list of content types acceptable by the client browser
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getAcceptableContentTypes()
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }

        return $this->acceptableContentTypes = $this->splitHttpAcceptHeader($this->headers->get('Accept'));
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library set an X-Requested-With HTTP header.
     * It is known to work with Prototype, Mootools, jQuery.
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }

    /**
     * Splits an Accept-* HTTP header.
     *
     * @param string $header  Header to split
     */
    public function splitHttpAcceptHeader($header)
    {
        if (!$header) {
            return array();
        }

        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q     = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = trim(substr($value, 0, $pos));
            } else {
                $q = 1;
            }

            if (0 < $q) {
                $values[trim($value)] = $q;
            }
        }

        arsort($values);

        return array_keys($values);
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->headers->has('X_REWRITE_URL')) {
            // check this first so IIS will catch
            $requestUri = $this->headers->get('X_REWRITE_URL');
        } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $this->getScheme().'://'.$this->getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ($this->server->get('QUERY_STRING')) {
                $requestUri .= '?'.$this->server->get('QUERY_STRING');
            }
        }

        return $requestUri;
    }

    protected function prepareBaseUrl()
    {
        $baseUrl = '';

        $filename = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = $this->server->get('PHP_SELF', '');
            $file    = $this->server->get('SCRIPT_FILENAME', '');
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    protected function prepareBasePath()
    {
        $basePath = '';
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return '';
        }

        $pathInfo = '';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param  array $files A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of UploadedFile instances
     */
    protected function convertFileInformation(array $files)
    {
        $fixedFiles = array();

        foreach ($files as $key => $data) {
            $fixedFiles[$key] = $this->fixPhpFilesArray($data);
        }

        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        foreach ($fixedFiles as $key => $data) {
            if (is_array($data)) {
                $keys = array_keys($data);
                sort($keys);

                if ($keys == $fileKeys) {
                    $fixedFiles[$key] = new UploadedFile($data['tmp_name'], $data['name'], $data['type'], $data['size'], $data['error']);
                } else {
                    $fixedFiles[$key] = $this->convertFileInformation($data);
                }
            }
        }

        return $fixedFiles;
    }

    /**
     * Fixes a malformed PHP $_FILES array.
     *
     * PHP has a bug that the format of the $_FILES array differs, depending on
     * whether the uploaded file fields had normal field names or array-like
     * field names ("normal" vs. "parent[child]").
     *
     * This method fixes the array to look like the "normal" $_FILES array.
     *
     * It's safe to pass an already converted array, in which case this method
     * just returns the original array unmodified.
     *
     * @param  array $data
     * @return array
     */
    protected function fixPhpFilesArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }    
        
        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        $keys = array_keys($data);
        sort($keys);

        if ($fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach ($fileKeys as $k) {
            unset($files[$k]);
        }
        foreach (array_keys($data['name']) as $key) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key],
                'type'     => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key],
            ));
        }

        return $files;
    }

    protected function initializeHeaders()
    {
        $headers = array();
        foreach ($this->server->all() as $key => $value) {
            if ('http_' === strtolower(substr($key, 0, 5))) {
                $headers[substr($key, 5)] = $value;
            }
        }

        return $headers;
    }

    static protected function initializeFormats()
    {
        static::$formats = array(
            'txt'  => 'text/plain',
            'js'   => array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'css'  => 'text/css',
            'json' => array('application/json', 'application/x-json'),
            'xml'  => array('text/xml', 'application/xml', 'application/x-xml'),
            'rdf'  => 'application/rdf+xml',
            'atom' => 'application/atom+xml',
        );
    }
}
