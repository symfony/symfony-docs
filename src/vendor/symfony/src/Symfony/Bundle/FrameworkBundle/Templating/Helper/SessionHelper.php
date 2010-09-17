<?php

namespace Symfony\Bundle\FrameworkBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\HttpFoundation\Request;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * SessionHelper.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class SessionHelper extends Helper
{
    protected $session;

    /**
     * Constructor.
     *
     * @param Request $request A Request instance
     */
    public function __construct(Request $request)
    {
        $this->session = $request->getSession();
    }

    /**
     * Returns an attribute
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    /**
     * Returns the locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->session->getLocale();
    }

    public function getFlash($name, $default = null)
    {
        return $this->session->getFlash($name, $default);
    }

    public function hasFlash($name)
    {
        return $this->session->hasFlash($name);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'session';
    }
}
