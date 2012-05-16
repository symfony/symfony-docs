<?php

namespace Symfony\Component\Form;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A field for entering a birthday date
 *
 * This field is a preconfigured DateField with allowed years between the
 * current year and 120 years in the past.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class BirthdayField extends DateField
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $currentYear = date('Y');

        $this->addOption('years', range($currentYear-120, $currentYear));

        parent::configure();
    }
}