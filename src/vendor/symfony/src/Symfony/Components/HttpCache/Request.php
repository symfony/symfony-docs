<?php

namespace Symfony\Components\HTTPCache;

class Request
{
  protected $cacheControl;

  public function __construct()
  {
    $this->cacheControl = new CacheRequestControl($_REQUEST['HTTP_CACHE_CONTROL']);
  }

  public function isMethodSafe()
  {
    return in_array(strtolower($_SERVER['REQUEST_METHOD']), array('get', 'head'));
  }

  public function isCacheable()
  {
    return true;
//    return 'no-cache' == $_REQUEST['HTTP_PRAGMA'] || $this->cacheControl->hasNoCache();
  }
}
