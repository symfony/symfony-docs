NotIdenticalTo
==============

.. versionadded:: 2.3
    The ``NotIdenticalTo`` constraint was introduced in Symfony 2.3.

Validates that a value is **not** identical to another value, defined in the
options. To force that a value is identical, see
:doc:`/reference/constraints/IdenticalTo`.

.. caution::
    
    This constraint compares using ``!==``, so ``3`` and ``"3"`` are
    considered not equal. Use :doc:`/reference/constraints/NotEqualTo` to compare
    with ``!=``.

+----------------+-----------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                       |
+----------------+-----------------------------------------------------------------------------+
| Options        | - `value`_                                                                  |
|                | - `message`_                                                                |
+----------------+-----------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotIdenticalTo`         |
+----------------+-----------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotIdenticalToValidator`|
+----------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is *not* equal to
``15`` and *not* an integer, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - NotIdenticalTo:
                        value: 15

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\NotIdenticalTo(
             *     value = 15
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/SocialBundle/Resources/config/validation.xml -->
        <class name="Acme\SocialBundle\Entity\Person">
            <property name="age">
                <constraint name="NotIdenticalTo">
                    <option name="value">15</option>
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
                $metadata->addPropertyConstraint('age', new Assert\NotIdenticalTo(array(
                    'value' => 15,
                )));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be identical to {{ compared_value_type }} {{ compared_value }}.``

This is the message that will be shown if the value is not equal.
