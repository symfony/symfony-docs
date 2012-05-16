<?php

namespace Symfony\Component\Validator\Mapping;

use Symfony\Component\Validator\Constraint;

abstract class ElementMetadata
{
    public $constraints = array();
    public $constraintsByGroup = array();

    /**
     * Returns the names of the properties that should be serialized
     *
     * @return array
     */
    public function __sleep()
    {
        return array(
            'constraints',
            'constraintsByGroup',
        );
    }

    /**
     * Clones this object
     */
    public function __clone()
    {
        $constraints = $this->constraints;

        $this->constraints = array();
        $this->constraintsByGroup = array();

        foreach ($constraints as $constraint) {
            $this->addConstraint(clone $constraint);
        }
    }

    /**
     * Adds a constraint to this element
     *
     * @param Constraint $constraint
     */
    public function addConstraint(Constraint $constraint)
    {
        $this->constraints[] = $constraint;

        foreach ($constraint->groups as $group) {
            if (!isset($this->constraintsByGroup[$group])) {
                $this->constraintsByGroup[$group] = array();
            }

            $this->constraintsByGroup[$group][] = $constraint;
        }

        return $this;
    }

    /**
     * Returns all constraints of this element
     *
     * @return array  An array of Constraint instances
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Returns whether this element has any constraints
     *
     * @return boolean
     */
    public function hasConstraints()
    {
        return count($this->constraints) > 0;
    }

    /**
     * Returns the constraints of the given group
     *
     * @param  string $group  The group name
     * @return array  An array with all Constraint instances belonging to
     *                the group
     */
    public function findConstraints($group)
    {
        return isset($this->constraintsByGroup[$group])
                ? $this->constraintsByGroup[$group]
                : array();
    }
}