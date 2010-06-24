<?php

require_once __DIR__.'/../src/autoload.php';

use Symfony\Foundation\Kernel;
use Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader;
use Symfony\Components\Routing\Loader\YamlFileLoader as RoutingLoader;

use Symfony\Foundation\Bundle\KernelBundle;
use Symfony\Framework\FoundationBundle\FoundationBundle;
use Symfony\Framework\ZendBundle\ZendBundle;
use Symfony\Framework\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Framework\DoctrineBundle\DoctrineBundle;
use Symfony\Framework\DoctrineMigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Framework\DoctrineMongoDBBundle\DoctrineMongoDBBundle;
use Symfony\Framework\PropelBundle\PropelBundle;
use Symfony\Framework\TwigBundle\TwigBundle;
use Application\HelloBundle\HelloBundle;

class HelloKernel extends Kernel
{
    public function registerRootDir()
    {
        return __DIR__;
    }

    public function registerBundles()
    {
        $bundles = array(
            new KernelBundle(),
            new FoundationBundle(),
            new ZendBundle(),
            new SwiftmailerBundle(),
            new DoctrineBundle(),
            //new DoctrineMigrationsBundle(),
            //new DoctrineMongoDBBundle(),
            //new PropelBundle(),
            //new TwigBundle(),
            new HelloBundle(),
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
