<?php

require_once __DIR__.'/../src/autoload.php';

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
        $bundles = array(
            new Symfony\Foundation\Bundle\KernelBundle(),
            new Symfony\Framework\WebBundle\WebBundle(),
            new Symfony\Framework\ZendBundle\ZendBundle(),
            new Symfony\Framework\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Framework\DoctrineBundle\DoctrineBundle(),
            //new Symfony\Framework\PropelBundle\PropelBundle(),
            new Symfony\Framework\TwigBundle\TwigBundle(),
            new Application\HelloBundle\HelloBundle(),
        );

        if ($this->isDebug()) {
        }

        return $bundles;
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
