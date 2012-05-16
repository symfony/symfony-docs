<?php

namespace Symfony\Component\HttpFoundation;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Response represents an HTTP response.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Response
{
    /**
     * @var \Symfony\Component\HttpFoundation\HeaderBag
     */
    public $headers;

    protected $content;
    protected $version;
    protected $statusCode;
    protected $statusText;

    static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * Constructor.
     *
     * @param string  $content The response content
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
        $this->headers = new HeaderBag($headers, 'response');
    }

    /**
     * Returns the response content as it will be sent (with the headers).
     *
     * @return string The response content
     */
    public function __toString()
    {
        $content = '';

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html');
        }

        // status
        $content .= sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\n";

        // headers
        foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $content .= "$name: $value\n";
            }
        }

        $content .= "\n".$this->getContent();

        return $content;
    }

    /**
     * Clones the current Response instance.
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    /**
     * Sends HTTP headers.
     */
    public function sendHeaders()
    {
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/html');
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));

        // headers
        foreach ($this->headers->all() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value);
            }
        }
    }

    /**
     * Sends content for the current web response.
     */
    public function sendContent()
    {
        echo $this->content;
    }

    /**
     * Sends HTTP headers and content.
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * Sets the response content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Gets the current response content
     *
     * @return string Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     *
     * @param string $version The HTTP protocol version
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Gets the HTTP protocol version.
     *
     * @return string The HTTP protocol version
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Sets response status code.
     *
     * @param integer $code HTTP status code
     * @param string  $text HTTP status text
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function setStatusCode($code, $text = null)
    {
        $this->statusCode = (int) $code;
        if ($this->statusCode < 100 || $this->statusCode > 599) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        $this->statusText = false === $text ? '' : (null === $text ? self::$statusTexts[$this->statusCode] : $text);
    }

    /**
     * Retrieves status code for the current web response.
     *
     * @return string Status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns true if the response is worth caching under any circumstance.
     *
     * Responses marked "private" with an explicit Cache-Control directive are
     * considered uncacheable.
     *
     * Responses with neither a freshness lifetime (Expires, max-age) nor cache
     * validator (Last-Modified, ETag) are considered uncacheable.
     *
     * @return Boolean true if the response is worth caching, false otherwise
     */
    public function isCacheable()
    {
        if (!in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false;
        }

        if ($this->headers->getCacheControl()->isNoStore() || $this->headers->getCacheControl()->isPrivate()) {
            return false;
        }

        return $this->isValidateable() || $this->isFresh();
    }

    /**
     * Returns true if the response is "fresh".
     *
     * Fresh responses may be served from cache without any interaction with the
     * origin. A response is considered fresh when it includes a Cache-Control/max-age
     * indicator or Expiration header and the calculated age is less than the freshness lifetime.
     *
     * @return Boolean true if the response is fresh, false otherwise
     */
    public function isFresh()
    {
        return $this->getTtl() > 0;
    }

    /**
     * Returns true if the response includes headers that can be used to validate
     * the response with the origin server using a conditional GET request.
     *
     * @return Boolean true if the response is validateable, false otherwise
     */
    public function isValidateable()
    {
        return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
    }

    /**
     * Marks the response "private".
     *
     * It makes the response ineligible for serving other clients.
     *
     * @param Boolean $value Whether to set the response to be private or public.
     */
    public function setPrivate($value)
    {
        $value = (Boolean) $value;
        $this->headers->getCacheControl()->setPublic(!$value);
        $this->headers->getCacheControl()->setPrivate($value);
    }

    /**
     * Returns true if the response must be revalidated by caches.
     *
     * This method indicates that the response must not be served stale by a
     * cache in any circumstance without first revalidating with the origin.
     * When present, the TTL of the response should not be overriden to be
     * greater than the value provided by the origin.
     *
     * @return Boolean true if the response must be revalidated by a cache, false otherwise
     */
    public function mustRevalidate()
    {
        return $this->headers->getCacheControl()->mustRevalidate() || $this->headers->getCacheControl()->mustProxyRevalidate();
    }

    /**
     * Returns the Date header as a DateTime instance.
     *
     * When no Date header is present, the current time is returned.
     *
     * @return \DateTime A \DateTime instance
     *
     * @throws \RuntimeException when the header is not parseable
     */
    public function getDate()
    {
        if (null === $date = $this->headers->getDate('Date')) {
            $date = new \DateTime();
            $this->headers->set('Date', $date->format(DATE_RFC2822));
        }

        return $date;
    }

    /**
     * Returns the age of the response.
     *
     * @return integer The age of the response in seconds
     */
    public function getAge()
    {
        if ($age = $this->headers->get('Age')) {
            return $age;
        }

        return max(time() - $this->getDate()->format('U'), 0);
    }

    /**
     * Marks the response stale by setting the Age header to be equal to the maximum age of the response.
     */
    public function expire()
    {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge());
        }
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @return \DateTime A DateTime instance
     */
    public function getExpires()
    {
        return $this->headers->getDate('Expires');
    }

    /**
     * Sets the Expires HTTP header with a \DateTime instance.
     *
     * If passed a null value, it deletes the header.
     *
     * @param \DateTime $date A \DateTime instance
     */
    public function setExpires(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->delete('Expires');
        } else {
            $this->headers->set('Expires', $date->format(DATE_RFC2822));
        }
    }

    /**
     * Sets the number of seconds after the time specified in the response's Date
     * header when the the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @return integer|null Number of seconds
     */
    public function getMaxAge()
    {
        if ($age = $this->headers->getCacheControl()->getSharedMaxAge()) {
            return $age;
        }

        if ($age = $this->headers->getCacheControl()->getMaxAge()) {
            return $age;
        }

        if (null !== $this->getExpires()) {
            return $this->getExpires()->format('U') - $this->getDate()->format('U');
        }

        return null;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param integer $value A number of seconds
     */
    public function setMaxAge($value)
    {
        $this->headers->getCacheControl()->setMaxAge($value);
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param integer $value A number of seconds
     */
    public function setSharedMaxAge($value)
    {
        $this->headers->getCacheControl()->setSharedMaxAge($value);
    }

    /**
     * Returns the response's time-to-live in seconds.
     *
     * It returns null when no freshness information is present in the response.
     *
     * When the responses TTL is <= 0, the response may not be served from cache without first
     * revalidating with the origin.
     *
     * @return integer The TTL in seconds
     */
    public function getTtl()
    {
        if ($maxAge = $this->getMaxAge()) {
            return $maxAge - $this->getAge();
        }

        return null;
    }

    /**
     * Sets the response's time-to-live for shared caches.
     *
     * This method adjusts the Cache-Control/s-maxage directive.
     *
     * @param integer $seconds The number of seconds
     */
    public function setTtl($seconds)
    {
        $this->setSharedMaxAge($this->getAge() + $seconds);
    }

    /**
     * Sets the response's time-to-live for private/client caches.
     *
     * This method adjusts the Cache-Control/max-age directive.
     *
     * @param integer $seconds The number of seconds
     */
    public function setClientTtl($seconds)
    {
        $this->setMaxAge($this->getAge() + $seconds);
    }

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @return \DateTime A DateTime instance
     */
    public function getLastModified()
    {
        return $this->headers->getDate('LastModified');
    }

    /**
     * Sets the Last-Modified HTTP header with a \DateTime instance.
     *
     * If passed a null value, it deletes the header.
     *
     * @param \DateTime $date A \DateTime instance
     */
    public function setLastModified(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->delete('Last-Modified');
        } else {
            $this->headers->set('Last-Modified', $date->format(DATE_RFC2822));
        }
    }

    /**
     * Returns the literal value of ETag HTTP header.
     *
     * @return string The ETag HTTP header
     */
    public function getEtag()
    {
        return $this->headers->get('ETag');
    }

    public function setEtag($etag = null, $weak = false)
    {
        if (null === $etag) {
            $this->headers->delete('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"'.$etag.'"';
            }

            $this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag);
        }
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     */
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->delete($header);
        }
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a redirect status code.
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     */
    public function setRedirect($url, $status = 302)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->setStatusCode($status);
        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }

        $this->headers->set('Location', $url);
        $this->setContent(sprintf('<html><head><meta http-equiv="refresh" content="1;url=%s"/></head></html>', htmlspecialchars($url, ENT_QUOTES)));
    }

    /**
     * Returns true if the response includes a Vary header.
     *
     * @return true if the response includes a Vary header, false otherwise
     */
    public function hasVary()
    {
        return (Boolean) $this->headers->get('Vary');
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @return array An array of Vary names
     */
    public function getVary()
    {
        if (!$vary = $this->headers->get('Vary')) {
            return array();
        }

        return preg_split('/[\s,]+/', $vary);
    }

    /**
     * Determines if the Response validators (ETag, Last-Modified) matches
     * a conditional value specified in the Request.
     *
     * If the Response is not modified, it sets the status code to 304 and
     * remove the actual content by calling the setNotModified() method.
     *
     * @param Request $request A Request instance
     *
     * @return Boolean true if the Response validators matches the Request, false otherwise
     */
    public function isNotModified(Request $request)
    {
        $lastModified = $request->headers->get('If-Modified-Since');
        $notModified = false;
        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->get('Last-Modified') == $lastModified);
        } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->get('Last-Modified');
        }

        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    public function isRedirect()
    {
        return in_array($this->statusCode, array(301, 302, 303, 307));
    }

    public function isEmpty()
    {
        return in_array($this->statusCode, array(201, 204, 304));
    }

    public function isRedirected($location)
    {
        return $this->isRedirect() && $location == $this->headers->get('Location');
    }
}
