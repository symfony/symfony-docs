<?php

namespace Symfony\Framework\WebBundle\Command;

use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;
use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\BuilderConfiguration;
use Symfony\Components\DependencyInjection\Dumper\GraphvizDumper;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Dumps the application container as a graphviz compatible file.
 *
 * @package    symfony
 * @subpackage console
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ContainerGraphvizCommand extends Command
{
  /**
   * @see Command
   */
  protected function configure()
  {
    $this
      ->setName('container:graphviz')
      ->setHelp('Usage: container:graphviz | dot -Tpng -o graph.png')
    ;
  }

  /**
   * @see Command
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $kernel = $this->container->getKernelService();

    $container = new Builder($kernel->getDefaultParameters());
    $configuration = new BuilderConfiguration();
    foreach ($kernel->getBundles() as $bundle)
    {
      $configuration->merge($bundle->buildContainer($container));
    }
    $configuration->merge($kernel->registerContainerConfiguration());
    $container->merge($configuration);
    $kernel->optimizeContainer($container);
    $container->setService('kernel', $kernel);

    $dumper = new GraphvizDumper($container);

    $output->write($dumper->dump(), Output::OUTPUT_RAW);
  }
}
