All
===

When applied to an array (or Traversable object), this constraint allows
you to apply a collection of constraints to each element of the array.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `constraints`_                                                       |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\All`               |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\AllValidator`      |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

Suppose that you have an array of strings, and you want to validate each
entry in that array:

.. configuration-block::

    .. code-block:: yaml

        # src/UserBundle/Resources/config/validation.yml
        Acme\UserBundle\Entity\User:
            properties:
                favoriteColors:
                    - All:
                        - NotBlank:  ~
                        - Length:
                            min: 5

    .. code-block:: php-annotations

        // src/Acme/UserBundle/Entity/User.php
        namespace Acme\UserBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;
  
        class User
        {
            /**
             * @Assert\All({
             *     @Assert\NotBlank
             *     @Assert\Length(min = "5"),
             * })
             */
             protected $favoriteColors = array();
        }

    .. code-block:: xml

        <!-- src/Acme/UserBundle/Resources/config/validation.xml -->
        <class name="Acme\UserBundle\Entity\User">
            <property name="favoriteColors">
                <constraint name="All">
                    <option name="constraints">
                        <constraint name="NotBlank" />
                        <constraint name="Length">
                            <option name="min">5</option>
                        </constraint>
                    </option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/UserBundle/Entity/User.php
        namespace Acme\UserBundle\Entity;
       
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('favoriteColors', new Assert\All(array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array('min' => 5)),
                    ),
                )));
            }
        }

Now, each entry in the ``favoriteColors`` array will be validated to not
be blank and to be at least 5 characters long.

Options
-------

constraints
~~~~~~~~~~~

**type**: ``array`` [:ref:`default option<validation-default-option>`]

This required option is the array of validation constraints that you want
to apply to each element of the underlying array.
