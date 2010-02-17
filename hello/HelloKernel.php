<?php

require_once __DIR__.'/../src/autoload.php';
require_once __DIR__.'/../src/vendor/symfony/src/Symfony/Foundation/bootstrap.php';

use Symfony\Foundation\Kernel;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader;
use Symfony\Components\Routing\Loader\YamlFileLoader as RoutingLoader;

class HelloKernel extends Kernel
{
  public function registerRootDir()
  {
    return __DIR__;
  }

  public function registerBundles()
  {
    return array(
      new Symfony\Foundation\Bundle\KernelBundle(),
      new Symfony\Framework\WebBundle\Bundle(),
      new Symfony\Framework\ZendBundle\Bundle(),
      new Symfony\Framework\SwiftmailerBundle\Bundle(),
      new Symfony\Framework\DoctrineBundle\Bundle(),
      new Application\HelloBundle\Bundle(),
    );
  }

  public function registerBundleDirs()
  {
    return array(
      'Application'        => __DIR__.'/../src/Application',
      'Bundle'             => __DIR__.'/../src/Bundle',
      'Symfony\\Framework' => __DIR__.'/../src/vendor/symfony/src/Symfony/Framework',
    );
  }

  public function registerContainerConfiguration()
  {
    $loader = new ContainerLoader($this->getBundleDirs());

    return $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
  }

  public function registerRoutes()
  {
    $loader = new RoutingLoader($this->getBundleDirs());

    return $loader->load(__DIR__.'/config/routing.yml');
  }
}
