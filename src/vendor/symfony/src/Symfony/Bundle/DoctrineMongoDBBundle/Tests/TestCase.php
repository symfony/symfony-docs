<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\DoctrineMongoDBBundle\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Doctrine\\ODM\\MongoDB\\Version')) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }
    }
}
