IdenticalTo
===========

.. versionadded:: 2.3
    The ``IdenticalTo`` constraint was introduced in Symfony 2.3.

Validates that a value is identical to another value, defined in the options.
To force that a value is *not* identical, see
:doc:`/reference/constraints/NotIdenticalTo`.

.. caution::
    
    This constraint compares using ``===``, so ``3`` and ``"3"`` are *not*
    considered equal. Use :doc:`/reference/constraints/EqualTo` to compare
    with ``==``.

+----------------+--------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                    |
+----------------+--------------------------------------------------------------------------+
| Options        | - `value`_                                                               |
|                | - `message`_                                                             |
+----------------+--------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalTo`         |
+----------------+--------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IdenticalToValidator`|
+----------------+--------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is equal to
``20`` and an integer, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - IdenticalTo:
                        value: 20

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\IdenticalTo(
             *     value = 20
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/SocialBundle/Resources/config/validation.xml -->
        <class name="Acme\SocialBundle\Entity\Person">
            <property name="age">
                <constraint name="IdenticalTo">
                    <option name="value">20</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\IdenticalTo(array(
                    'value' => 20,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be identical to {{ compared_value_type }} {{ compared_value }}.``

This is the message that will be shown if the value is not identical.
