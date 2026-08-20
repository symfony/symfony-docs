Comparing Arbitrary Precision Numbers
-------------------------------------

.. versionadded:: 8.2

    Support for :phpclass:`BcMath\\Number` values in comparison constraints was
    introduced in Symfony 8.2.

This constraint can also validate :phpclass:`BcMath\\Number` objects, which are
the arbitrary precision numbers provided by the PHP ``bcmath`` extension. In
this case, you can define the comparison value as an integer, a float or a
numeric string::

    // src/Entity/Order.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Order
    {
        #[Assert\GreaterThanOrEqual(0.01)]
        protected \BcMath\Number $amount;
    }

When the validated value is a ``BcMath\Number``, the validator converts the
comparison value to a ``BcMath\Number`` too, so the comparison doesn't lose any
precision. Without this conversion, PHP truncates the float to an integer when
comparing it with a ``BcMath\Number``::

    // this is true in plain PHP because 10.5 is truncated to 10
    new \BcMath\Number('10.2') > 10.5;
