NotNull
=======

Validates that a value is not strictly equal to ``null``. To ensure that
a value is simply not blank (not a blank string), see the  :doc:`/reference/constraints/NotBlank`
constraint.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotNull`          |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotNullValidator` |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``firstName`` property of an ``Author`` class
were not strictly equal to ``null``, you would:

.. configuration-block::

    .. code-block:: yaml

        properties:
            firstName:
                - NotNull: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotNull()
             */
            protected $firstName;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be null``

This is the message that will be shown if the value is ``null``.
