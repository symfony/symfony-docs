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
 * ForbiddenHttpException.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ForbiddenHttpException extends HttpException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        if (!$message) {
            $message = 'Forbidden';
        }

        parent::__construct($message, 403, $previous);
    }
}
