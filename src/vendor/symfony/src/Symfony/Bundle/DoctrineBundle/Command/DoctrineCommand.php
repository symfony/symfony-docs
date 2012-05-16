<?php

namespace Symfony\Bundle\DoctrineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\EntityGenerator;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Base class for Doctrine console commands to extend from.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class DoctrineCommand extends Command
{
    public static function setApplicationEntityManager(Application $application, $emName)
    {
        $container = $application->getKernel()->getContainer();
        $emName = $emName ? $emName : 'default';
        $emServiceName = sprintf('doctrine.orm.%s_entity_manager', $emName);
        if (!$container->has($emServiceName)) {
            throw new \InvalidArgumentException(sprintf('Could not find Doctrine EntityManager named "%s"', $emName));
        }

        $em = $container->get($emServiceName);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');
    }

    public static function setApplicationConnection(Application $application, $connName)
    {
        $container = $application->getKernel()->getContainer();
        $connName = $connName ? $connName : 'default';
        $connServiceName = sprintf('doctrine.dbal.%s_connection', $connName);
        if (!$container->has($connServiceName)) {
            throw new \InvalidArgumentException(sprintf('Could not find Doctrine Connection named "%s"', $connName));
        }

        $connection = $container->get($connServiceName);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($connection), 'db');
    }

    protected function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();

        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        return $entityGenerator;
    }

    protected function getEntityManager($name = null)
    {
        $name = $name ? $name : 'default';
        $serviceName = sprintf('doctrine.orm.%s_entity_manager', $name);
        if (!$this->container->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf('Could not find Doctrine EntityManager named "%s"', $name));
        }

        return $this->container->get($serviceName);
    }

    protected function runCommand($name, array $input = array())
    {
        $application = new Application($this->container->getKernelService());
        $arguments = array();
        $arguments = array_merge(array($name), $input);
        $input = new ArrayInput($arguments);
        $application->setAutoExit(false);
        $application->run($input);
    }

    /**
     * TODO: Better way to do these functions?
     *
     * @return Connection[] An array of Connections
     */
    protected function getDoctrineConnections()
    {
        $connections = array();
        $ids = $this->container->getServiceIds();
        foreach ($ids as $id) {
            preg_match('/doctrine.dbal.(.*)_connection/', $id, $matches);
            if ($matches) {
                $name = $matches[1];
                $connections[$name] = $this->container->get($id);
            }
        }

        return $connections;
    }

    protected function getDoctrineEntityManagers()
    {
        $entityManagers = array();
        $ids = $this->container->getServiceIds();
        foreach ($ids as $id) {
            preg_match('/doctrine.orm.(.*)_entity_manager/', $id, $matches);
            if ($matches) {
                $name = $matches[1];
                $entityManagers[$name] = $this->container->get($id);
            }
        }
        return $entityManagers;
    }

    protected function getBundleMetadatas(Bundle $bundle)
    {
        $tmp = dirname(str_replace('\\', '/', get_class($bundle)));
        $namespace = str_replace('/', '\\', dirname($tmp));
        $class = basename($tmp);

        $bundleMetadatas = array();
        $entityManagers = $this->getDoctrineEntityManagers();
        foreach ($entityManagers as $key => $em) {
            $cmf = new SymfonyDisconnectedClassMetadataFactory($em);
            $metadatas = $cmf->getAllMetadata();
            foreach ($metadatas as $metadata) {
                if (strpos($metadata->name, $namespace) !== false) {
                    $bundleMetadatas[] = $metadata;
                }
            }
        }
        return $bundleMetadatas;
    }
}

class SymfonyDisconnectedClassMetadataFactory extends DisconnectedClassMetadataFactory
{
    /**
     * @override
     */
    protected function _newClassMetadataInstance($className)
    {
        if (class_exists($className)) {
            return new ClassMetadata($className);
        } else {
            return new ClassMetadataInfo($className);
        }
    }
}