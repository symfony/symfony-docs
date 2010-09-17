<?php

namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpFoundation\Request;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HttpKernelInterface.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface HttpKernelInterface
{
    const MASTER_REQUEST = 1;
    const SUB_REQUEST = 2;

    /**
     * Handles a request to convert it to a response.
     *
     * @param  Request $request A Request instance
     * @param  integer $type The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param  Boolean $raw Whether to catch exceptions or not
     *
     * @return Response $response A Response instance
     */
    public function handle(Request $request = null, $type = self::MASTER_REQUEST, $raw = false);

    /**
     * Gets the Request instance associated with the master request.
     *
     * @return Request A Request instance
     */
    public function getRequest();
}
