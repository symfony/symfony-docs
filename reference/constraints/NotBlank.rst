NotBlank
========

Validates that a value is not blank, defined as not equal to a blank string
and also not equal to ``null``. To force that a value is simply not equal to
``null``, see the :doc:`/reference/constraints/NotNull` constraint.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotBlank`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotBlankValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``firstName`` property of an ``Author`` class
were not blank, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        properties:
            firstName:
                - NotBlank: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank()
             */
            protected $firstName;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be blank``

This is the message that will be shown if the value is blank.
