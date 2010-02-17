<?php

namespace Symfony\Framework\WebBundle\Listener;

use Symfony\Components\EventDispatcher\Event;
use Symfony\Framework\WebBundle\Controller;

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
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ClosureControllerLoader extends ControllerLoader
{
  public function resolve(Event $event)
  {
    $request = $event->getParameter('request');

    if (!($pattern = $request->getPathParameter('_pattern')))
    {
      if (null !== $this->logger)
      {
        $this->logger->err('Unable to look for the controller as the _pattern is not set');
      }

      return false;
    }

    if (!$controller = $this->container->getKernelService()->getController($pattern))
    {
      throw new \InvalidArgumentException(sprintf('Unable to find controller for "%s".', $pattern));
    }

    $r = new \ReflectionFunction($controller);
    $parameters = $event->getParameter('request')->getPathParameters();
    $parameters['controller'] = new Controller($this->container);
    $arguments = $this->getMethodArguments($r, $parameters, $pattern);

    $event->setReturnValue(array($controller, $arguments));

    return true;
  }
}
