<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AssertTypeValidator extends ConstraintValidator
{
    public function isValid($value, Constraint $constraint)
    {
        if ($value === null) {
            return true;
        }

        $type = $constraint->type == 'boolean' ? 'bool' : $constraint->type;
        $function = 'is_' . $type;

        if (function_exists($function) && call_user_func($function, $value)) {
            return true;
        } else if ($value instanceof $constraint->type) {
            return true;
        }

        $this->setMessage($constraint->message, array(
            'value' => $value,
            'type' => $constraint->type,
        ));

        return false;
    }
}