<?php

namespace Symfony\Components\HTTPCache;

class Response
{
  protected $status, $headers, $content;

  static protected $CACHEABLE_RESPONSE_CODES = array(
    200, # OK
    203, # Non-Authoritative Information
    300, # Multiple Choices
    301, # Moved Permanently
    302, # Found
    404, # Not Found
    410, # Gone
  );

  public function __construct($status, $headers, $content)
  {
    $this->status = $status;
    $this->content = $content;
    $this->setHeaders($headers);
  }

  public function isCacheable()
  {
    if (!in_array($this->status, self::$CACHEABLE_RESPONSE_CODES))
    {
      return false;
    }

//    return false if cache_control.no_store? || cache_control.private?

    return $this->isValidateable() || $this->isFresh();
  }

  public function getMaxAge()
  {
    cache_control.shared_max_age ||
      cache_control.max_age ||
      (expires && (expires - date))
  }

  public function getTtl()
  {
    if (!$maxAge = $this->getMaxAge())
    {
      return;
    }

    return $maxAge - $this->getAge();
  }

  public function isFresh()
  {
    $ttl = $this->getTtl();

    return $ttl && $ttl > 0
  }

  public function isValidateable()
  {
    return $this->hasHeader('Last-Modified') || $this->hasHeader('ETag');
  }

  public function setHeaders($headers)
  {
    foreach ($headers as $name => $value)
    {
      $this->setHeader($name, $value);
    }
  }

  public function hasHeader($name)
  {
    return array_key_exists($this->normalizeHeaderName($name), $this->headers);
  }

  public function setHeader($name, $value, $replace = true)
  {
    $name = $this->normalizeHeaderName($name);

    if (is_null($value))
    {
      unset($this->headers[$name]);

      return;
    }

    if (!$replace)
    {
      $current = isset($this->headers[$name]) ? $this->headers[$name] : '';
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->headers[$name] = $value;
  }

  public function send()
  {
// FIXME
$protocol = '1.0';

    // status
    header($protocol.' '.$this->status);

    // headers
print_r($this->headers);
    foreach ($this->headers as $name => $value)
    {
      header($name.': '.$value);
    }

    // content
    echo $this->content;
  }

  protected function normalizeHeaderName($name)
  {
    return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
  }
}
