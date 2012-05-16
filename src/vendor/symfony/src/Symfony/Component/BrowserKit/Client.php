<?php

namespace Symfony\Component\BrowserKit;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Client;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Client simulates a browser.
 *
 * To make the actual request, you need to implement the doRequest() method.
 *
 * If you want to be able to run requests in their own process (insulated flag),
 * you need to also implement the getScript() method.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class Client
{
    protected $history;
    protected $cookieJar;
    protected $server;
    protected $request;
    protected $response;
    protected $crawler;
    protected $insulated;
    protected $redirect;
    protected $followRedirects;

    /**
     * Constructor.
     *
     * @param array     $server    The server parameters (equivalent of $_SERVER)
     * @param History   $history   A History instance to store the browser history
     * @param CookieJar $cookieJar A CookieJar instance to store the cookies
     */
    public function __construct(array $server = array(), History $history = null, CookieJar $cookieJar = null)
    {
        $this->setServerParameters($server);
        $this->history = null === $history ? new History() : $history;
        $this->cookieJar = null === $cookieJar ? new CookieJar() : $cookieJar;
        $this->insulated = false;
        $this->followRedirects = true;
    }

    /**
     * Sets whether to automatically follow redirects or not.
     *
     * @param Boolean $followRedirect Whether to follow redirects
     */
    public function followRedirects($followRedirect = true)
    {
        $this->followRedirects = (Boolean) $followRedirect;
    }

    /**
     * Sets the insulated flag.
     *
     * @param Boolean $insulated Whether to insulate the requests or not
     *
     * @throws \RuntimeException When Symfony Process Component is not installed
     */
    public function insulate($insulated = true)
    {
        if (!class_exists('Symfony\\Component\\Process\\Process')) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Unable to isolate requests as the Symfony Process Component is not installed.');
            // @codeCoverageIgnoreEnd
        }

        $this->insulated = (Boolean) $insulated;
    }

    /**
     * Sets server parameters.
     *
     * @param array $server An array of server parameters
     */
    public function setServerParameters(array $server)
    {
        $this->server = array_merge(array(
            'HTTP_HOST'       => 'localhost',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3',
        ), $server);
    }

    /**
     * Returns the History instance.
     *
     * @return History A History instance
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Returns the CookieJar instance.
     *
     * @return CookieJar A CookieJar instance
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    /**
     * Returns the current Crawler instance.
     *
     * @return Crawler A Crawler instance
     */
    public function getCrawler()
    {
        return $this->crawler;
    }

    /**
     * Returns the current Response instance.
     *
     * @return Response A Response instance
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the current Request instance.
     *
     * @return Request A Request instance
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Clicks on a given link.
     *
     * @param Link $link A Link instance
     */
    public function click(Link $link)
    {
        return $this->request($link->getMethod(), $link->getUri());
    }

    /**
     * Submits a form.
     *
     * @param Form  $form   A Form instance
     * @param array $values An array of form field values
     */
    public function submit(Form $form, array $values = array())
    {
        $form->setValues($values);

        return $this->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), array(), $form->getPhpFiles());
    }

    /**
     * Calls a URI.
     *
     * @param string  $method        The request method
     * @param string  $uri           The URI to fetch
     * @param array   $parameters    The Request parameters
     * @param array   $files         The files
     * @param array   $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param Boolean $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     */
    public function request($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $changeHistory = true)
    {
        $uri = $this->getAbsoluteUri($uri);

        $server = array_merge($this->server, $server);
        if (!$this->history->isEmpty()) {
            $server['HTTP_REFERER'] = $this->history->current()->getUri();
        }
        $server['HTTP_HOST'] = parse_url($uri, PHP_URL_HOST);
        $server['HTTPS'] = 'https' == parse_url($uri, PHP_URL_SCHEME);

        $request = new Request($uri, $method, $parameters, $files, $this->cookieJar->getValues($uri), $server);

        $this->request = $this->filterRequest($request);

        if (true === $changeHistory) {
            $this->history->add($request);
        }

        if ($this->insulated) {
            $this->response = $this->doRequestInProcess($this->request);
        } else {
            $this->response = $this->doRequest($this->request);
        }

        $response = $this->filterResponse($this->response);

        $this->cookieJar->updateFromResponse($response, $uri);

        $this->redirect = $response->getHeader('Location');

        if ($this->followRedirects && $this->redirect) {
            return $this->crawler = $this->followRedirect();
        }

        return $this->crawler = $this->createCrawlerFromContent($request->getUri(), $response->getContent(), $response->getHeader('Content-Type'));
    }

    /**
     * Makes a request in another process.
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     *
     * @throws \RuntimeException When processing returns exit code
     */
    protected function doRequestInProcess($request)
    {
        $process = new PhpProcess($this->getScript($request));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return unserialize($process->getOutput());
    }

    /**
     * Makes a request.
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    abstract protected function doRequest($request);

    /**
     * Returns the script to execute when the request must be insulated.
     *
     * @param Request $request A Request instance
     *
     * @throws \LogicException When this abstract class is not implemented
     */
    protected function getScript($request)
    {
        // @codeCoverageIgnoreStart
        throw new \LogicException('To insulate requests, you need to override the getScript() method.');
        // @codeCoverageIgnoreEnd
    }

    protected function filterRequest(Request $request)
    {
        return $request;
    }

    protected function filterResponse($response)
    {
        return $response;
    }

    protected function createCrawlerFromContent($uri, $content, $type)
    {
        $crawler = new Crawler(null, $uri);
        $crawler->addContent($content, $type);

        return $crawler;
    }

    /**
     * Goes back in the browser history.
     */
    public function back()
    {
        return $this->requestFromRequest($this->history->back(), false);
    }

    /**
     * Goes forward in the browser history.
     */
    public function forward()
    {
        return $this->requestFromRequest($this->history->forward(), false);
    }

    /**
     * Reloads the current browser.
     */
    public function reload()
    {
        return $this->requestFromRequest($this->history->current(), false);
    }

    /**
     * Follow redirects?
     *
     * @return Client
     *
     * @throws \LogicException If request was not a redirect
     */
    public function followRedirect()
    {
        if (empty($this->redirect)) {
            throw new \LogicException('The request was not redirected.');
        }

        return $this->request('get', $this->redirect);
    }

    /**
     * Restarts the client.
     *
     * It flushes all cookies.
     */
    public function restart()
    {
        $this->cookieJar->clear();
        $this->history->clear();
    }

    protected function getAbsoluteUri($uri)
    {
        // already absolute?
        if ('http' === substr($uri, 0, 4)) {
            return $uri;
        }

        if (!$this->history->isEmpty()) {
            $currentUri = $this->history->current()->getUri();
        } else {
            $currentUri = sprintf('http%s://%s/',
                isset($this->server['HTTPS']) ? 's' : '',
                isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : 'localhost'
            );
        }

        // anchor?
        if (!$uri || '#' == $uri[0]) {
            return preg_replace('/#.*?$/', '', $currentUri).$uri;
        }

        if ('/' !== $uri[0]) {
            $path = parse_url($currentUri, PHP_URL_PATH);

            if ('/' !== substr($path, -1)) {
                $path = substr($path, 0, strrpos($path, '/') + 1);
            }

            $uri = $path.$uri;
        }

        return preg_replace('#^(.*?//[^/]+)\/.*$#', '$1', $currentUri).$uri;
    }

    /**
     * Makes a request from a Request object directly.
     *
     * @param Request $request       A Request instance
     * @param Boolean $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     */
    protected function requestFromRequest(Request $request, $changeHistory = true)
    {
        return $this->request($request->getMethod(), $request->getUri(), $request->getParameters(), array(), $request->getFiles(), $request->getServer(), $changeHistory);
    }
}
