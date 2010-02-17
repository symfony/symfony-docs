<?php

namespace Symfony\Framework\WebBundle\Command;

use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;
use Symfony\Framework\WebBundle\Util\Filesystem;
use Symfony\Foundation\Kernel;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * 
 *
 * @package    symfony
 * @subpackage console
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class PharizeBundleCommand extends Command
{
  /**
   * @see Command
   */
  protected function configure()
  {
    $this
      ->setDefinition(array(
        new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle class name to pharize'),
      ))
      ->setName('bundle:pharize')
    ;
  }

  /**
   * @see Command
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if (!preg_match('/Bundle$/', $class = $input->getArgument('bundle')))
    {
      throw new \InvalidArgumentException('The namespace of a bundle must end with Bundle.');
    }

    $dirs = $this->container->getKernelService()->getBundleDirs();

    $tmp = str_replace('\\', '/', $class);
    $namespace = dirname($tmp);
    $bundle = basename($tmp);

    if (!isset($dirs[$namespace]))
    {
      throw new \InvalidArgumentException('Unable to find the bundle.');
    }

    //$dir = str_replace(str_replace('\\', '/', $namespace), '', $dirs[$namespace]);
    $dir = $dirs[$namespace];
    $pharFile = $dir.'/'.$bundle.'.phar';
    if (file_exists($pharFile))
    {
      unlink($pharFile);
    }

    $phar = new \Phar($pharFile, 0, $bundle);
    $phar->setSignatureAlgorithm(\Phar::SHA1);

    $phar->startBuffering();

    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirs[$namespace].'/'.$bundle), \RecursiveIteratorIterator::LEAVES_ONLY) as $file)
    {
      if (preg_match('/^\./', $file))
      {
        continue;
      }

      //$name = str_replace(realpath($dir).'/', '', realpath($file));
      $name = str_replace(realpath($dir).'/', 'Bundle/', realpath($file));;
      $this->addPhpFile($phar, $name, file_get_contents($file));
    }

    // Stubs
    $phar['_cli_stub.php'] = $this->getCliStub();
    $phar['_web_stub.php'] = $this->getWebStub();
    $phar->setDefaultStub('_cli_stub.php', '_web_stub.php');

    $phar->stopBuffering();

    // does not seem to work if set before adding files
    //$phar->compressFiles(\Phar::GZ);

    unset($phar);
  }

  protected function addPhpFile(\Phar $phar, $name, $content)
  {
    $phar[$name] = Kernel::stripComments($content);
  }

  protected function getCliStub()
  {
    return '<?php ?>';
    return <<<'EOF'
<?php

throw new \LogicException('This PHAR file can only be executed from the CLI.');

__HALT_COMPILER();
EOF;
  }

  protected function getWebStub()
  {
    return '<?php ?>';
    return <<<'EOF'
<?php

throw new \LogicException('This PHAR file can only be executed from the CLI.');

__HALT_COMPILER();
EOF;
  }
}
