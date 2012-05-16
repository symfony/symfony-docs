<?php

namespace Symfony\Component\HttpFoundation\File\Exception;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Thrown when the access on a file was denied.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class AccessDeniedException extends FileException
{
    /**
     * Constructor.
     *
     * @param string $path  The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}