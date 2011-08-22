Date
====

Validates that a value is a valid date, meaning either a ``DateTime`` object
or a string (or an object that can be cast into a string) that follows a
valid YYYY-MM-DD format.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property<validation-property-target>`                        |
+----------------+--------------------------------------------------------------------+
| Options        | - `message`_                                                       |
+----------------+--------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Date`          |
+----------------+--------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateValidator` |
+----------------+--------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        properties:
            birthday:
                - Date: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Date()
             */
             protected $birthday;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid date``

This message is shown if the underlying data is not a valid date.
