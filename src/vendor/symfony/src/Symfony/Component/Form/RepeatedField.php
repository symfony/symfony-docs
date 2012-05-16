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
 * A field for repeated input of values
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class RepeatedField extends FieldGroup
{
    /**
     * The prototype for the inner fields
     * @var FieldInterface
     */
    protected $prototype;

    /**
     * Repeats the given field twice to verify the user's input
     *
     * @param FieldInterface $innerField
     */
    public function __construct(FieldInterface $innerField, array $options = array())
    {
        $this->prototype = $innerField;

        parent::__construct($innerField->getKey(), $options);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $field = clone $this->prototype;
        $field->setKey('first');
        $field->setPropertyPath('first');
        $this->add($field);

        $field = clone $this->prototype;
        $field->setKey('second');
        $field->setPropertyPath('second');
        $this->add($field);
    }

    /**
     * Returns whether both entered values are equal
     *
     * @return bool
     */
    public function isFirstEqualToSecond()
    {
        return $this->get('first')->getData() === $this->get('second')->getData();
    }

    /**
     * Sets the values of both fields to this value
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        parent::setData(array('first' => $data, 'second' => $data));
    }

    /**
     * Return only value of first password field.
     *
     * @return string The password.
     */
    public function getData()
    {
        if ($this->isBound() && $this->isFirstEqualToSecond()) {
            return $this->get('first')->getData();
        }

        return null;
    }
}
