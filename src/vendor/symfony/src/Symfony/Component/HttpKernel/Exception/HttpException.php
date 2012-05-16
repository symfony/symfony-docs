<?php

namespace Symfony\Component\HttpKernel\Exception;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HttpException.
 *
 * By convention, exception code == response status code.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class HttpException extends \Exception
{
}
