Null
====

Validates that a value is exactly equal to ``null``. To force that a property
is simply blank (blank string or ``null``), see the  :doc:`/reference/constraints/Blank`
constraint. To ensure that a property is not null, see :doc:`/reference/constraints/NotNull`.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Null`             |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NullValidator`    |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If, for some reason, you wanted to ensure that the ``firstName`` property
of an ``Author`` class exactly equal to ``null``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                firstName:
                    - Null: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Null()
             */
            protected $firstName;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be null``

This is the message that will be shown if the value is not ``null``.
