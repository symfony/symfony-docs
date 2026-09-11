Comparing Arbitrary Precision Numbers
-------------------------------------

.. versionadded:: 8.2

    Support for :phpclass:`BcMath\\Number` values in comparison constraints was
    introduced in Symfony 8.2.

This constraint can also be applied to :phpclass:`BcMath\\Number` objects,
the arbitrary precision numbers provided by the PHP ``bcmath`` extension. The
comparison value can be given as an integer, a float or a numeric string::

    // src/Entity/Order.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Order
    {
        #[Assert\GreaterThanOrEqual(0.01)]
        protected \BcMath\Number $amount;
    }

When the validated value is a ``BcMath\Number``, the comparison value is
converted to a ``BcMath\Number`` too, so the comparison keeps its arbitrary
precision. This matters because PHP truncates the other operand to an integer
when comparing it with a ``BcMath\Number``, which makes ``10.2`` greater than
``10.5`` in plain PHP::

    new \BcMath\Number('10.2') > 10.5; // true in plain PHP
    // with the constraint, 10.2 is correctly considered lower than 10.5

Floats written in exponent notation (e.g. ``1.0E-7``) are supported as well.
The ``INF`` and ``NAN`` values have no ``BcMath\Number`` representation, so
they keep the native PHP comparison.

.. note::

    This applies to the :doc:`EqualTo </reference/constraints/EqualTo>`,
    :doc:`NotEqualTo </reference/constraints/NotEqualTo>`,
    :doc:`GreaterThan </reference/constraints/GreaterThan>`,
    :doc:`GreaterThanOrEqual </reference/constraints/GreaterThanOrEqual>`,
    :doc:`LessThan </reference/constraints/LessThan>`,
    :doc:`LessThanOrEqual </reference/constraints/LessThanOrEqual>` and
    :doc:`Range </reference/constraints/Range>` constraints.
    :doc:`IdenticalTo </reference/constraints/IdenticalTo>` and
    :doc:`NotIdenticalTo </reference/constraints/NotIdenticalTo>` compare
    object identity, so a ``BcMath\Number`` is never identical to a scalar, and
    :doc:`DivisibleBy </reference/constraints/DivisibleBy>` doesn't accept
    ``BcMath\Number`` values.
