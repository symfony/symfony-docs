<?php

namespace Symfony\Components\HTTPCache;

class Cache
{
  protected
    $callable = null,
    $options  = array(),
    $traces   = array();

  public function __construct($callable, $options = array())
  {
    $this->callable = $callable;
    $this->options = array_merge(array(
      'cache_key'        => 'Key',
      'verbose'          => true,
      'storage'          => null, // cache instance
      'metastore'        => 'heap:/',
      'entitystore'      => 'heap:/',
      'default_ttl'      => 0,
      'private_headers'  => array('Authorization', 'Cookie'),
      'allow_reload'     => true,
      'allow_revalidate' => true,
    ), $options);
  }

  public function execute(CacheRequest $request = null)
  {
    $this->traces = array();

    if (is_null($request))
    {
      $request = new CacheRequest();
    }

    if (!$request->isMethodSafe())
    {
      $response = $this->invalidate($request);
    }
    else if (isset($_SERVER['HTTP_EXPECT']))
    {
      $response = $this->pass($request);
    }
    else
    {
      $response = $this->lookup($request);
    }

    $response->setHeader('X-Http-Cache', implode(', ', $this->traces));

    return $response;
  }

  public function pass($request)
  {
    $this->record('pass');

    return $this->forward($request);
  }

  public function invalidate($request)
  {
    $this->record('invalidate');

    return $this->pass($request);
  }

  public function lookup($request)
  {
    if (!$request->isCacheable() && $this->options['allow_reload'])
    {
      $this->record('reload');

      return $this->fetch($request);
    }
    else
    {
      $this->record('miss');

      return $this->fetch($request);
    }
  }

  public function fetch($request)
  {
    // send no head requests because we want content
    $_SERVER['REQUEST_METHOD'] = 'GET';

    // avoid that the backend sends no content
    unset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['HTTP_IF_NONE_MATCH']);

    $response = $this->forward();

    if ($response->isCacheable())
    {
      $this->store($request, $response);
    }

    return $response;
  }

  // the callable must return an array with response status code, headers, content
  public function forward()
  {
    $ret = call_user_func($this->callable);

    if (false !== $ret)
    {
      return new CacheResponse($ret[0], $ret[1], $ret[2]);
    }

    // does not work for status code / headers from PHP
    // that's the best we can do
    ob_start();
    call_user_func($this->callable);

    $content = ob_get_clean();
    $headers = headers_list();

    return new CacheResponse(200, $headers, $content);
  }

  protected function store(CacheRequest $request, CacheResponse $response)
  {
    $this->record('store');

//    $this->metastore->store($request, $response);

//    metastore.store(@request, response, entitystore)
//    response.headers['Age'] = response.age.to_s
  }

  protected function record($event)
  {
    $this->traces[] = $event;
  }
}
