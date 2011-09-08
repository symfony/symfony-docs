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
                        - MinLength: 5

    .. code-block:: php-annotations

       // src/Acme/UserBundle/Entity/User.php
       namespace Acme\UserBundle\Entity;
       
       use Symfony\Component\Validator\Constraints as Assert;

       class User
       {
           /**
            * @Assert\All({
            *     @Assert\NotBlank
            *     @Assert\MinLength(5),
            * })
            */
            protected $favoriteColors = array();
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
